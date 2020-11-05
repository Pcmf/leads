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

$result = mysqli_query($con, "SELECT L.* , F.nome AS nomeFornecedor, S.nome AS statusnome "
            . " FROM arq_leads L "
            . " INNER JOIN cad_fornecedorleads F ON L.fornecedor= F.id "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id"
            . " WHERE L.status=28 LIMIT 1");
if($result->num_rows){
 //Obter os dados da lead
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['lead'] = $row;
        //Regista no registo de contactos como  puxada pela 1ª vez
        mysqli_query($con, sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,motivocontacto) VALUES(%s,%s,"
                . " (SELECT MAX(B.contactonum) FROM cad_registocontacto B WHERE B.lead=%s) +1, 18) ", $row['id'], $dt->user->id, $row['id']));
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
        //Limpar a agenda
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $row['id']));
        //Verificar se existem outras LEADS para este cliente que estejam em aberto
        $resp['openLeads'] = checkClientOpenLeads($row['id'], $row['nif'], $row['email'], $row['telefone'], $con);
        $resp['processo'] = $row;
        //Resposta
        echo json_encode($resp);
        return;
}

if ( $dt->turn == 'A') {
    //Verificar se há LEADS agendadas para a data/hora atual se sim puxa a que estever como ativa e com data/hora agendada mais antiga
    $queryA = sprintf("SELECT L.*, F.nome AS nomeFornecedor,S.nome AS statusnome, D.ativa "
            . " FROM cad_agenda A "
            . " INNER JOIN arq_leads L ON A.lead=L.id "
            . " INNER JOIN cad_fornecedorleads F ON L.fornecedor= F.id "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id"
            . " LEFT JOIN cad_agendadoc D ON D.lead=A.lead "
            . " WHERE L.status IN(32,33)  "
            ." AND ((  A.tipoagenda IN(5,6) AND A.status=1 AND (( A.agendadata=CURRENT_DATE"
            . " AND A.agendahora<CURTIME() ) OR A.agendadata< CURRENT_DATE )) "
             ." OR D.ativa=1) "
            . " ORDER BY A.agendadata ASC, A.agendahora ASC LIMIT 1");


    
    $resultA = mysqli_query($con, $queryA);
    if ($resultA) {
        $rowA = mysqli_fetch_array($resultA, MYSQLI_BOTH);
        if ($rowA[0] > 0) { //Existe Lead agendada, 
            $resp['lead'] = $rowA;
            isset($rowA['ativa']) ? $resp['call']=true : $resp['call']=false;
            $query1 = sprintf("SELECT * FROM cad_registocontacto R INNER JOIN cnf_motivocontacto M ON M.id=R.motivocontacto "
                    . " WHERE R.lead=%s ORDER BY dtcontacto DESC", $rowA['id']);
            $result1 = mysqli_query($con, $query1);
            if ($result1) {
                while ($row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
                    array_push($regCont, $row1);
                }
                $resp['regCont'] = $regCont;
            }
            if(!$resp['call']){
                //Atualiza o status da lead
                mysqli_query($con, sprintf("UPDATE arq_leads SET status=2 WHERE id=%s", $rowA['id']));
                mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $rowA['id']));
            } 
            //Verificar se existe no arq_processo
            $query = sprintf("SELECT processo FROM cad_agendatemp WHERE lead=%s ORDER BY id DESC LIMIT 1", $rowA['id']);
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
            //resposta
            echo json_encode($resp);
        } else {
            puxaLeadNova($dt, $con, $resp);
        }
    } else {
        //echo $queryA;
        puxaLeadNova($dt, $con, $resp);
    }
} else{
    puxaLeadNova($dt, $con, $resp);
}

function puxaLeadNova($dt, $con, $resp) {
    //não encontrou agendadas puxa uma nova
    $regCont = array();
    $query = sprintf("SELECT L.*, F.nome AS nomeFornecedor,S.nome AS statusnome "
            . " FROM arq_leads L INNER JOIN cad_fornecedorleads F ON L.fornecedor=F.id "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " WHERE (L.status=9 OR L.status=5) AND DATEDIFF(NOW(), DATE(L.dataentrada)) < 60  ORDER BY L.dataentrada ASC LIMIT 1", $dt->user->id);
    $result = mysqli_query($con, $query);

    if ($result) {
        
        //Obter os dados da lead
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['lead'] = $row;
        // Inserir no arq_histrecuperacao
        mysqli_query($con, sprintf("INSERT INTO arq_histrecuperacao(lead, user, status) VALUES(%s, %s, 1)", $row['id'], $dt->user->id ));
        
        //Regista no registo de contactos como  puxada pela 1ª vez
        mysqli_query($con, sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,motivocontacto) VALUES(%s,%s,"
                . " (SELECT MAX(B.contactonum) FROM cad_registocontacto B WHERE B.lead=%s) +1, 18) ", $row['id'], $dt->user->id, $row['id']));
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
        mysqli_query($con, sprintf("UPDATE arq_leads SET status=28, user=%s WHERE id=%s", $dt->user->id, $row['id']));
        //Limpar a agenda
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $row['id']));
        //Verificar se existem outras LEADS para este cliente que estejam em aberto
        $resp['openLeads'] = checkClientOpenLeads($row['id'], $row['nif'], $row['email'], $row['telefone'], $con);
        $resp['processo'] = $row;
        //Resposta
        echo json_encode($resp);
    } else {
        echo $query;
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
