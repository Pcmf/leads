<?php

/*
 * Obter toda a informação sobre uma lead
 * 
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();
//LEAD info para Gestor
$query = sprintf("SELECT L.id, L.idleadorig,L.nomelead, L.tipo,L.dataentrada,L.status,L.datastatus,L.situacao,L.info,L.user, L.nif, L.telefone, L.email, "
        . " F.nome AS fornecedornome, S.nome AS nomestatus, U1.nome AS gestor, U.nome AS analista "
        . " FROM arq_leads L "
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " INNER JOIN cad_utilizadores U1 ON U1.id=L.user "
        . " LEFT JOIN cad_utilizadores U ON U.id=L.analista "
        . " WHERE L.id=%s LIMIT 1", $dt->lead);

$result = mysqli_query($con, $query);
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $result0 = mysqli_query($con, sprintf("SELECT *  FROM cad_docpedida WHERE lead=%s", $dt->lead));
    $row['docpedida'] = mysqli_num_rows($result0);
    $resp['dlead'] = $row;

    //Obter dados de Cartão de credito
    $resultCC = mysqli_query($con, sprintf("SELECT * FROM cad_cartaocredito WHERE lead = %s", $dt->lead));
    if ($resultCC) {
        $rowCC = mysqli_fetch_array($resultCC, MYSQLI_ASSOC);
        $resp['cc'] = $rowCC;
    }
    if ($row['id'])
        $resp['historic'] = checkClientOpenLeads($dt->lead, $row['nif'], $row['email'], $row['telefone'], $con);
}

//Informações do Cliente - Processo
$query = sprintf("SELECT P.*, SP.nome AS nomecontrato,SP2.nome AS nomecontrato2,"
        . " SF.nome AS sitfamiliar, H.nome AS nomehabitacao, H2.nome AS nomehabitacao2 "
        . " FROM arq_processo P "
        . " LEFT JOIN cnf_sitprofissional SP ON SP.id=P.tipocontrato "
        . " LEFT JOIN cnf_sitprofissional SP2 ON SP2.id=P.tipocontrato2 "
        . " LEFT JOIN cnf_sitfamiliar SF ON SF.id=P.estadocivil "  //INNER
        . " LEFT JOIN cnf_tipohabitacao H ON H.id=P.tipohabitacao "  //INNER
        . " LEFT JOIN cnf_tipohabitacao H2 ON H2.id=P.tipohabitacao2 "
        . " WHERE P.lead=%s", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    $row00 = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if ($row00) {


        $resp['infoCliente'] = $row00;
    //    $resp['processo']= $row00;

        //Obter dados de outros rendimentos
        $result0 = mysqli_query($con, sprintf("SELECT * FROM cad_outrosrendimentos WHERE lead=%s", $dt->lead));
        if ($result0) {
            $temp = array();
            while ($row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
                array_push($temp, $row0);
            }
            $resp['rendimentos'] = $temp;
        }
        //Obter dados de outros Créditos
        $result0 = mysqli_query($con, sprintf("SELECT * FROM cad_outroscreditos WHERE lead=%s", $dt->lead));
        if ($result0) {
            $temp = array();
            while ($row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
                array_push($temp, $row0);
            }
            $resp['creditos'] = $temp;
        }

        $resp['historic'] = checkClientOpenLeads($row00['lead'], $row00['nif'], $row00['email'], $row00['telefone'], $con);
    } else {
        $query = sprintf("SELECT nome, email, idade, telefone, nif FROM arq_leads WHERE id=%s", $dt->lead);
        $result = mysqli_query($con, $query);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['infoCliente'] = $row;
            $resp['rendimentos'] = null;
            $resp['creditos'] = null;
        }
    }
}
//Historico dos contactos
$query = sprintf("SELECT H.lead, H.data, S.descricao, U.nome, U.tipo "
        . " FROM `arq_histprocess` H "
        . " INNER JOIN cnf_statuslead S ON S.id=H.status"
        . " LEFT JOIN cad_utilizadores U ON (U.id= H.analista OR U.id=H.user) "
        . " WHERE `lead` = %s GROUP BY H.data", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    // $resp['contactos'] = $temp; 
}
$query = sprintf("SELECT R.lead, R.dtcontacto aS data, M.descricao, U.nome, U.tipo "
        . " FROM cad_registocontacto R "
        . " INNER JOIN cnf_motivocontacto M ON M.id=R.motivocontacto "
        . " INNER JOIN cad_utilizadores U ON U.id=R.user "
        . " WHERE R.lead= %s", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    //    $temp=array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['contactos'] = $temp;
}


//Documentação Pedida e Recebida
$query = sprintf("SELECT D.*,N.*,F.tipo,F.nomefx"
        . " FROM cad_docpedida D "
        . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc "
        . " LEFT JOIN arq_documentacao F ON F.lead= D.lead AND F.linha=D.linha AND D.recebido=1 "
        . " WHERE D.lead=%s", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['docs'] = $temp;
}
//Financiamentos
$query = sprintf("SELECT F.*,DATE(F.datastatus) AS dtstatus,P.nome,S.status AS statusnome FROM cad_financiamentos F "
        . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
        . " INNER JOIN cnf_stsfinanciamentos S ON S.id=F.status "
        . " WHERE F.lead=%s", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['financiamentos'] = $temp;
}
//Rejeições
$query = sprintf("SELECT * FROM cad_rejeicoes WHERE lead=%s", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['rejeicoes'] = $temp;
}

//Contratos de Financiamentos
$query = sprintf("SELECT * FROM arq_contratos WHERE lead=%s", $dt->lead);
$result = mysqli_query($con, $query);
if ($result) {
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['contratos'] = $temp;
}


//Simulação - se não existir cria com os dados do processo
$result = mysqli_query($con, sprintf("SELECT * FROM cad_simula WHERE lead=%s ORDER BY linha DESC LIMIT 1", $dt->lead));
if ($result && $result->num_rows >= 1) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $resp['simula'] = $row;
} else {
    //Insere no cad_simula
    !isset($resp['infoCliente']['segundoproponente']) ? $resp['infoCliente']['segundoproponente'] = 0 : null;
    !isset($resp['infoCliente']['tipocredito']) ? $resp['infoCliente']['tipocredito'] = 0 : null;
    !isset($resp['infoCliente']['valorpretendido']) ? $resp['infoCliente']['valorpretendido'] = 0 : null;
    !isset($resp['infoCliente']['prestacaopretendida']) ? $resp['infoCliente']['prestacaopretendida'] = 0 : null;
    !isset($resp['infoCliente']['prazopretendido']) ? $resp['infoCliente']['prazopretendido'] = 0 : null;


    $result = mysqli_query($con, sprintf("INSERT INTO cad_simula(lead, valorpretendido, prestacaopretendida, prazopretendido, segundoproponente, tipocredito) "
                    . " VALUES(%s, %s, %s, %s, %s, '%s')", $dt->lead, $resp['infoCliente']['valorpretendido'], $resp['infoCliente']['prestacaopretendida'],
                    $resp['infoCliente']['prazopretendido'], $resp['infoCliente']['segundoproponente'], $resp['infoCliente']['tipocredito']));
    if ($result) {
        $result = mysqli_query($con, sprintf("SELECT * FROM cad_simula WHERE lead=%s", $dt->lead));
        if ($result->num_rows == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['simula'] = $row;
        }
    }
}

// Simulações para enviar por email
$result = mysqli_query($con, sprintf("SELECT * FROM cad_simulag WHERE lead = %s", $dt->lead));
if($result) {
    $temp =  array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
        $resp['simulaemail'] = $temp;
}

//FORMULARIO do PROCESSO - se a lead não existir na tabela arq_process_form vai inserir com os dados do arq_processo
$result = mysqli_query($con, sprintf("SELECT * FROM arq_process_form WHERE lead=%s", $dt->lead));
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if ($row['lead']>0) {
    $resp['processo'] = $row;
    
} else {
    !isset($resp['infoCliente']['nome']) ? $resp['infoCliente']['nome'] = '' : null;
    !isset($resp['infoCliente']['estadocivil']) ? $resp['infoCliente']['estadocivil'] = 0 : null;
    !isset($resp['infoCliente']['filhos']) ? $resp['infoCliente']['filhos'] = 0 : null;
    !isset($resp['infoCliente']['nif']) || !$resp['infoCliente']['nif'] ? $resp['infoCliente']['nif'] = 0 : null;
    !isset($resp['infoCliente']['vencimento']) || !$resp['infoCliente']['vencimento'] ? $resp['infoCliente']['vencimento'] = 0 : null;
    !isset($resp['infoCliente']['venc_cetelem']) || !$resp['infoCliente']['venc_cetelem'] ? $resp['infoCliente']['venc_cetelem'] = 0 : null;
    !isset($resp['infoCliente']['segundoproponente']) ? $resp['infoCliente']['segundoproponente'] = 0 : null;
    !isset($resp['infoCliente']['nome2']) ? $resp['infoCliente']['nome2'] = '' : null;
    !isset($resp['infoCliente']['nif2']) || !$resp['infoCliente']['nif2'] ? $resp['infoCliente']['nif2'] = 0 : null;
    !isset($resp['infoCliente']['vencimento2']) || !$resp['infoCliente']['vencimento2'] ? $resp['infoCliente']['vencimento2'] = 0 : null;
    !isset($resp['infoCliente']['venc_cetelem2']) || !$resp['infoCliente']['venc_cetelem2'] ? $resp['infoCliente']['venc_cetelem2'] = 0 : null;
    !isset($resp['infoCliente']['relacaofamiliar']) ? $resp['infoCliente']['relacaofamiliar'] = 0 : null;
    !isset($resp['infoCliente']['tipohabitacao']) ? $resp['infoCliente']['tipohabitacao'] = 0 : null;
       !isset($resp['infoCliente']['anoiniciohabitacao']) ? $resp['infoCliente']['anoiniciohabitacao'] = 0 : null;
 !isset($resp['infoCliente']['valorhabitacao']) ? $resp['infoCliente']['valorhabitacao'] = 0 : null;
    !isset($resp['infoCliente']['tipohabitacao2']) ? $resp['infoCliente']['tipohabitacao2'] = 0 : null;
     !isset($resp['infoCliente']['anoiniciohabitacao2']) ? $resp['infoCliente']['anoiniciohabitacao2'] = 0 : null;
   !isset($resp['infoCliente']['valorhabitacao2']) ? $resp['infoCliente']['valorhabitacao2'] = 0 : null;
    !isset($resp['infoCliente']['telefone']) ? $resp['infoCliente']['telefone'] = '' : null;
    !isset($resp['infoCliente']['email']) ? $resp['infoCliente']['email'] = '' : null;
    !isset($resp['infoCliente']['telefone2']) ? $resp['infoCliente']['telefone2'] = '' : null;
    !isset($resp['infoCliente']['tipocontrato']) ? $resp['infoCliente']['tipocontrato'] = 0 : null;
    !isset($resp['infoCliente']['profissao']) ? $resp['infoCliente']['profissao'] = '' : null;
    !isset($resp['infoCliente']['tipocontrato2']) ? $resp['infoCliente']['tipocontrato2'] = 0 : null;
    !isset($resp['infoCliente']['profissao2']) ? $resp['infoCliente']['profissao2'] = '' : null;
    !isset($resp['infoCliente']['anoinicio']) ? $resp['infoCliente']['anoinicio'] = 0 : null;
    !isset($resp['infoCliente']['mesinicio']) ? $resp['infoCliente']['mesinicio'] = 0 : null;
    !isset($resp['infoCliente']['anoinicio2']) ? $resp['infoCliente']['anoinicio2'] = 0 : null;
    !isset($resp['infoCliente']['mesinicio2']) ? $resp['infoCliente']['mesinicio2'] = 0 : null;
    !isset($resp['infoCliente']['diaprestacao']) ? $resp['infoCliente']['diaprestacao'] = 1 : null;

    // Inserir alguns valores a partir do arq_processo
    $query = sprintf("INSERT INTO arq_process_form( lead, nome, estadocivil, filhos, nif, vencimento, venc_cetelem, "
            . " segundoproponente, nome2, nif2, vencimento2, venc_cetelem2, relacaofamiliar, "
            . " tipohabitacao, anoiniciohabitacao, valorhabitacao, tipohabitacao2, anoiniciohabitacao2, valorhabitacao2, telefone, email, telefone2, tipocontrato, tipocontrato2, "
            . " desde, desdemes, desde2, desdemes2, diaprestacao, sector, sector2) "
            . " VALUES(%s, '%s', %s, %s, %s, %s, %s, %s, '%s', %s, %s, %s, %s, '%s', '%s', %s, %s, %s, %s, '%s', '%s', '%s', '%s', '%s', %s, '%s', '%s', %s, %s, '%s', '%s')",
            $dt->lead, $resp['infoCliente']['nome'], $resp['infoCliente']['estadocivil'], $resp['infoCliente']['filhos'], $resp['infoCliente']['nif'],
            $resp['infoCliente']['vencimento'], $resp['infoCliente']['venc_cetelem'], 
            $resp['infoCliente']['segundoproponente'], $resp['infoCliente']['nome2'], $resp['infoCliente']['nif2'],
            $resp['infoCliente']['vencimento2'], $resp['infoCliente']['venc_cetelem2'], $resp['infoCliente']['relacaofamiliar'],
            $resp['infoCliente']['tipohabitacao'], $resp['infoCliente']['anoiniciohabitacao'],  $resp['infoCliente']['valorhabitacao'], 
            $resp['infoCliente']['tipohabitacao2'], $resp['infoCliente']['anoiniciohabitacao2'],  $resp['infoCliente']['valorhabitacao2'], 
            $resp['infoCliente']['telefone'], $resp['infoCliente']['email'], $resp['infoCliente']['telefone2'],
            $resp['infoCliente']['tipocontrato'], $resp['infoCliente']['tipocontrato2'],
            $resp['infoCliente']['anoinicio'], $resp['infoCliente']['mesinicio'], $resp['infoCliente']['anoinicio2'], $resp['infoCliente']['mesinicio2'], $resp['infoCliente']['diaprestacao'],
            $resp['infoCliente']['profissao'], $resp['infoCliente']['profissao2']);
   // echo $query;
    $result = mysqli_query($con, $query);
 //   echo $result;
    if ($result) {
        $result = mysqli_query($con, sprintf("SELECT * FROM arq_process_form WHERE lead=%s", $dt->lead));
        if ($result->num_rows == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['processo'] = $row;
        }
    }
}
}

// Calculos
//Calcular o valor de outros creditos
$calculos = array();
$calculos['outrosC'] = 0;
$calculos['outrosCTotal'] = 0;
if ($resp['creditos']) {
    $totalPrestacao = 0;
    $totalOCreditos = 0;
    forEach ($resp['creditos'] as $ln) {
        if ($ln['adminchoice'] != null) {
            $liquidar = $ln['adminchoice'];
        } else {
            $liquidar = $ln['liquidar'];
        }
        $liquidar == 0 ? $totalPrestacao += $ln['prestacao'] : null;
        $liquidar == 0 ? $totalOCreditos += $ln['valorcredito'] : null;
    }
    $calculos['outrosC'] = $totalPrestacao;
    $calculos['outrosCTotal'] = $totalOCreditos;
}
//Calcular o valor de Receitas
$calculos['outrosR'] = 0;
if ($resp['rendimentos']) {
    $totalRendimentos = 0.0;
    forEach ($resp['rendimentos'] as $ln) {
        $ln['periocidade'] == 'Ano' ? $dv = 12 : $dv = 1;
        $ln['usar'] == 1 ? $totalRendimentos += round($ln['valorrendimento'] / $dv) : null;
    }
    $calculos['outrosR'] = $totalRendimentos;
}
$resp['calculos'] = $calculos;

// Obter os valor hipotecarios guardados
$resp['hipotecario'] = NULL;
$result = mysqli_query($con, sprintf("SELECT * FROM cad_hipotecario WHERE lead=%s", $dt->lead));
if ($result->num_rows == 1) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $resp['hipotecario'] = $row;
}


echo json_encode($resp);

//Função para verificar se existem lead em aberto para o cliente
function checkClientOpenLeads($lead, $nif, $email, $telefone, $con) {
    $list = array();
    $query = sprintf("SELECT L.id,L.nome,L.dataentrada,L.nif,L.email,L.telefone,S.nome AS status,L.datastatus,U.nome AS usernome,U1.nome AS analista,L.montante "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON S.id=L.status"
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U1 ON U1.id=L.analista "
            . " WHERE L.id<>%s AND ((L.nif=%s AND L.nif>0) OR (L.email='%s' AND L.email<>'') OR (L.telefone='%s' AND L.telefone<>'')) "
            , $lead, $nif, $email, $telefone);

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
