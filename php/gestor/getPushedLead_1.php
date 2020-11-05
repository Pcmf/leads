<?php

/*
 * Este PHP recebe como parametro o user Id e retorna uma LEAD
 * 
 * A seleção da LEAD vai depender da Agenda. Se houver alguma lead agendada para 
 * o periodo em que se encontra vai escolher uma das agendadas senão escolhe uma nova,
 *  que tenha a data mais antiga
 */
require_once '../openCon.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);
$dt->user = json_decode($dt->user);


$resp = array();
$regCont = array();

$res = mysqli_query($con, sprintf("SELECT id FROM arq_leads WHERE status=2 AND user=%s", $dt->user->id));
if ($res) {
    $rowA = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $dt->lead = $rowA['id'];
}

//Se tem lead selecionada no url
if (isset($dt->lead)) {
    $queryA = sprintf("SELECT L.*, F.nome AS nomeFornecedor,S.nome AS statusnome "
            . " FROM arq_leads L LEFT JOIN cad_agenda A ON A.lead=L.id "
            . " INNER JOIN cad_fornecedorleads F ON L.fornecedor= F.id "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " WHERE L.id =%s LIMIT 1", $dt->lead);
    $resultA = mysqli_query($con, $queryA);
    if ($resultA) {
        $rowA = mysqli_fetch_array($resultA, MYSQLI_BOTH);
        if ($rowA[0] > 0) { //Existe Lead agendada, 
            $resp['lead'] = $rowA;
            $query1 = sprintf("SELECT * FROM cad_registocontacto R INNER JOIN cnf_motivocontacto M ON M.id=R.motivocontacto "
                    . " WHERE R.lead=%s ORDER BY dtcontacto DESC", $rowA['id']);
            $result1 = mysqli_query($con, $query1);
            if ($result1) {
                while ($row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
                    array_push($regCont, $row1);
                }
                $resp['regCont'] = $regCont;
            }
//Atualiza o status da lead
            mysqli_query($con, sprintf("UPDATE arq_leads SET status=2,user=%s WHERE id=%s", $rowA['id'], $dt->user->id));
//Verificar se existe no cad_agendatemp
            $query = sprintf("SELECT processo FROM cad_agendatemp WHERE lead=%s ORDER BY id DESC LIMIT 1", $dt->lead);
            $result = mysqli_query($con, $query);
            if ($result) {
                $row = mysqli_fetch_array($result, MYSQLI_BOTH);
                if ($row) {
                    $resp['processo'] = json_decode($row['processo']);
                } else {
                    $resp['processo'] = $rowA;
                }
            } else {
                $resp['processo'] = $rowA;
            }
//Verificar se existem outras LEADS para este cliente que estejam em aberto
            $resp['openLeads'] = checkClientOpenLeads($rowA['id'], $rowA['nif'], $rowA['email'], $rowA['telefone'], $con);

// Verificar se a lead já teve algum status >=8
            $resp['goToDet'] = checkHistoricStatus($con, $rowA['id']);
//resposta
            echo json_encode($resp);
            return;
        }
    }
}

//Selecionar leads perioritarias
// Com documentação completa
$query = sprintf("SELECT F.lead FROM cad_fila F "
        . " INNER JOIN arq_leads L ON L.id=F.lead"
        . " WHERE F.status=1 AND  L.status IN(36,39) AND L.user=%s ORDER BY L.id LIMIT 1", $dt->user->id);
$result = mysqli_query($con, $query);
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    
    if ($row) {
        getLeadInfo($con, $dt, $row['lead'], $resp);
        return;
    }
    
}
// Com speedup
$query = sprintf("SELECT F.lead FROM `cad_fila` F "
        . " INNER JOIN arq_leads L ON L.id=F.lead "
        . " INNER JOIN cad_speedup S ON S.lead=F.lead "
        . " WHERE F.status=1 AND L.status IN(37,38) AND S.visto=0 AND L.user=%s "
        . " ORDER BY F.lead LIMIT 1", $dt->user->id);
$result = mysqli_query($con, $query);
if ($result) {  
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if ($row['lead']) {
        getLeadInfo($con, $dt, $row['lead'], $resp);
        return;
    }
}
//Seleção sem ter uma LEAD definida
//Verificar se está definido turn N e se há novas. se não houver muda para turn A
if ($dt->turn == 'N') {
    $resultN = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads WHERE status=1 OR (status=2 AND user=%s) ", $dt->user->id));
    if ($resultN) {
        $rowN = mysqli_fetch_array($resultN, MYSQLI_NUM);
        if ($rowN[0] == 0) {
            $dt->turn = 'A';
        } else {
            puxaLeadNova($dt, $con, $resp);
        } 
    } else {
        $dt->turn = 'A';
    }
}

// Agendadas
if (!isset($dt->lead) && $dt->turn == 'A') {
//Verificar se há LEADS agendadas para a data/hora atual se sim puxa a que estever como ativa e com data/hora agendada mais antiga
    $queryA = sprintf("SELECT L.*, F.nome AS nomeFornecedor,S.nome AS statusnome, D.ativa "
            . " FROM cad_agenda A "
            . " INNER JOIN arq_leads L ON A.lead=L.id "
            . " INNER JOIN cad_fornecedorleads F ON L.fornecedor= F.id "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id"
            . " LEFT JOIN cad_agendadoc D ON D.lead=A.lead "
            . " WHERE L.status IN (6,7,8, 36, 37, 38)  AND L.user=%s "
            . " AND ((  A.tipoagenda<>3 AND A.status=1 AND (( A.agendadata=CURRENT_DATE"
            . " AND A.agendahora<CURTIME() ) OR A.agendadata< CURRENT_DATE )) "
            . " OR D.ativa=1) "
            . " ORDER BY A.agendadata ASC, A.agendahora ASC LIMIT 1", $dt->user->id);

    $resultA = mysqli_query($con, $queryA);
    if ($resultA) {
        $rowA = mysqli_fetch_array($resultA, MYSQLI_BOTH);
//Existe Lead agendada, 
        if ($rowA[0] > 0) {
            $resp['lead'] = $rowA;
            isset($rowA['ativa']) ? $resp['call'] = true : $resp['call'] = false;
//Registo de contactos
            $query1 = sprintf("SELECT * FROM cad_registocontacto R INNER JOIN cnf_motivocontacto M ON M.id=R.motivocontacto "
                    . " WHERE R.lead=%s ORDER BY dtcontacto DESC", $rowA['id']);
            $result1 = mysqli_query($con, $query1);
            if ($result1) {
                while ($row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
                    array_push($regCont, $row1);
                }
                $resp['regCont'] = $regCont;
            }
            if (!$resp['call']) {
//Atualiza o status da lead
                mysqli_query($con, sprintf("UPDATE arq_leads SET status=2 WHERE id=%s", $rowA['id']));
//Limpa a agenda para a lead puxada
                mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $rowA['id']));
            }
            $resp['processo'] = $rowA;
//            }
//Verificar se existem outras LEADS para este cliente que estejam em aberto
            $resp['openLeads'] = checkClientOpenLeads($rowA['id'], $rowA['nif'], $rowA['email'], $rowA['telefone'], $con);
// Check historico da lead           
            $resp['goToDet'] = checkHistoricStatus($con, $rowA['id']);
//resposta
            echo json_encode($resp);
// não existe lead agendada
        } else {
            puxaLeadNova($dt, $con, $resp);
        }
    } else {
        echo $queryA;
    }
} elseif ($dt->user->presenca == 1) {
    puxaLeadNova($dt, $con, $resp);
} else {
    echo null;
}

function puxaLeadNova($dt, $con, $resp) {
//não encontrou agendadas puxa uma nova do cad_fila
    $result = mysqli_query($con, sprintf("SELECT F.lead FROM cad_fila F "
            . " INNER JOIN arq_leads L ON L.id=F.lead "
            . " WHERE F.status=1 AND (L.status= 1 OR (L.status IN(1, 37, 38, 40) AND L.user=%s))  ORDER BY F.lead LIMIT 1", $dt->user->id));
    if ($result) {
        $row0 = mysqli_fetch_array($result, MYSQLI_ASSOC);
        if ($row0['lead']) {
            getLeadInfo($con, $dt, $row0['lead'], $resp);
        }
    }
    echo null;
}


/**
 * Obter os dados necessários
 */
function getLeadInfo($con, $dt,  $lead, $resp) {
        $regCont = array();
        $query = sprintf("SELECT L.*, F.nome AS nomeFornecedor,S.nome AS statusnome "
                . " FROM arq_leads L INNER JOIN cad_fornecedorleads F ON L.fornecedor=F.id "
                . " INNER JOIN cnf_statuslead S ON L.status=S.id "
                . " WHERE L.id=%s", $lead);
        $result = mysqli_query($con, $query);
        if ($result) {
//Obter os dados da lead
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['lead'] = $row;
//Regista no registo de contactos como  puxada pela 1ª vez
            mysqli_query($con, sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,motivocontacto) VALUES(%s,%s,1,0) ", $row['id'], $dt->user->id));
//Obter o historico de tentativas de contacto
            $query1 = sprintf("SELECT * FROM cad_registocontacto R INNER JOIN cnf_motivocontacto M ON M.id=R.motivocontacto "
                    . " WHERE R.lead=%s ORDER BY dtcontacto DESC", $row['id']);
            $result1 = mysqli_query($con, $query1);
            if ($result1) {
                while ($row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
                    array_push($regCont, $row1);
                }
                $resp['regCont'] = $regCont;
            }
//Atualiza o status da lead e atribuia ao gestor
            mysqli_query($con, sprintf("UPDATE arq_leads SET status=2,user=%s WHERE id=%s", $dt->user->id, $row['id']));
// Atualiza o status no cad_lead
            mysqli_query($con, sprintf("UPDATE cad_fila SET status = 0 WHERE lead =%s", $row['id']));
//Limpar a agenda
            mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $row['id']));
//Verificar se existem outras LEADS para este cliente que estejam em aberto
            $resp['openLeads'] = checkClientOpenLeads($row['id'], $row['nif'], $row['email'], $row['telefone'], $con);
            $resp['processo'] = $row;

// Check historico da lead           
            $resp['goToDet'] = checkHistoricStatus($con, $row['id']);
//Resposta
            echo json_encode($resp);
        }
    
}

//Função para verificar se existem lead em aberto para o cliente
function checkClientOpenLeads($lead, $nif, $email, $telefone, $con) {
    $list = array();

    $query = sprintf("SELECT L.id,L.nome,L.dataentrada,L.nif,L.email,L.telefone,S.nome AS status,L.datastatus,U.nome AS usernome,U1.nome AS analista,L.montante "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON S.id=L.status"
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U1 ON U1.id=L.analista "
            . " WHERE L.id<>%s AND ((L.nif=%s AND L.nif>0) OR (L.email='%s' AND L.email<>'') OR (L.telefone='%s' AND L.telefone<>'')) ", $lead, $nif, $email, $telefone);
//            . " AND L.status IN(1,2,6,7,8,9,10,11,12,13,16,17,20) ",$lead,$nif,$email,$telefone);
    $result = mysqli_query($con, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($list, $row);
        }
        return $list;
    } else {
        return NULL;
    }
}

// Função para verificar o historico da lead
function checkHistoricStatus($con, $lead) {
    $result = mysqli_query($con, sprintf("SELECT COUNT(*) AS qty FROM arq_histprocess where lead=%s AND status>=8", $lead));
    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        if ($row['qty'] > 0) {
            return true;
        }
    }
    return false;
}
