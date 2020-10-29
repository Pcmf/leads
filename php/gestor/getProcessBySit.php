<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();
//Chegaram
if($dt->sts == 3){
    $query  = sprintf("SELECT P.lead,P.nome,P.telefone,P.nif,P.email,DATE(P.datainicio) AS dtinicial,D.assunto,D.dataentrada, F.nome AS fornecedornome "
            . " FROM arq_processo P  "
            . " INNER JOIN arq_leads L ON L.id=P.lead"
            . " INNER JOIN arq_emaildocs D ON P.email=D.email "
            . " INNER JOIN cad_agenda A ON P.lead = A.lead "
            . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
            . " WHERE L.status=9  AND A.tipoagenda=3 AND A.status=1 AND P.user = %s GROUP BY P.lead ORDER BY P.lead",$dt->userId);
//            . " WHERE L.status=9 AND D.situacao=1 AND A.tipoagenda=3 AND A.status=1 AND P.user = %s GROUP BY P.lead ORDER BY P.lead",$dt->userId);
}
//Aguarda
if($dt->sts == 1 || $dt->sts==21){
    $query  = sprintf("SELECT P.lead,P.nome,P.telefone,P.nif,P.email,DATE(P.datainicio) AS dtinicial,A.agendadata, F.nome AS fornecedornome "
            . " FROM arq_processo P  INNER JOIN arq_leads L ON L.id=P.lead"
            . " INNER JOIN cad_agenda A ON P.lead = A.lead "
            . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
            . " WHERE L.status =8 AND A.status=1 AND A.tipoagenda=3 AND DATE(A.agendadata)>=DATE(NOW()) AND L.user = %s GROUP BY P.lead ORDER BY P.lead",$dt->userId);
}
//Aguarda
if($dt->sts==21){
    $query  = sprintf("SELECT P.lead,P.nome,P.telefone,P.nif,P.email,DATE(P.datainicio) AS dtinicial, F.nome AS fornecedornome"
            . " FROM arq_processo P "
            . " INNER JOIN arq_leads L ON L.id=P.lead "
            . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
            . " WHERE L.status =21 AND L.user = %s ORDER BY P.lead",$dt->userId);
}
//atrasados
if($dt->sts == 10){
    $query = sprintf("SELECT P.lead,P.nome,P.telefone,P.nif,P.email,DATE(P.datainicio) AS dtinicial,A.agendadata, F.nome AS fornecedornome "
        . " FROM arq_processo P "
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " INNER JOIN cad_agenda A ON A.lead = P.lead "
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "    
        . " WHERE L.status=8 AND A.status=1 AND A.tipoagenda=3 AND DATE(A.agendadata)<DATE(NOW()) AND L.user=%s  ORDER BY P.lead",$dt->userId);
}
//atrasados
if($dt->sts == 101){
    $query = sprintf("SELECT DISTINCT(P.lead),P.nome,P.telefone,P.nif,P.email,DATE(P.datainicio) AS dtinicial,A.agendadata, F.nome AS fornecedornome "
        . " FROM arq_processo P "
        . " INNER JOIN arq_documentacao D ON D.lead=P.lead "
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " INNER JOIN cad_agenda A ON A.lead = P.lead "
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "    
        . " WHERE L.status=8 AND A.status=1 AND A.tipoagenda=3 AND DATE(A.agendadata)<DATE(NOW()) AND L.user=%s  ORDER BY P.lead",$dt->userId);
}
//Inseridos pelo Cliente
if($dt->sts == 36){
    $query = sprintf("SELECT P.lead, P.nome, P.telefone, P.nif, P.email, DATE(P.datainicio) AS dtinicial, L.status,"
        . " S.nome AS statusnome, S.descricao, L.dataentrada, F.nome AS fornecedornome "
        . " FROM arq_processo P "
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
        . " WHERE L.status IN(36,37,38) AND L.user=%s  ORDER BY P.lead",$dt->userId);
}
//Com documentação recebida BPS-Docs
if($dt->sts == 39){
    $query = sprintf("SELECT L.id AS lead, L.nome, L.telefone, L.nif, L.email, DATE(L.datastatus) AS dtinicial, L.status,"
        . " S.nome AS statusnome, S.descricao, L.dataentrada, F.nome AS fornecedornome "
        . " FROM arq_leads L "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
        . " WHERE L.status IN(39) AND L.user=%s  ORDER BY L.id",$dt->userId);
}
    $result =mysqli_query($con,$query);
    if($result){
        while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            array_push($resp, $row);
        }
        echo json_encode($resp);
    }else{
        echo $query;
    }
       
