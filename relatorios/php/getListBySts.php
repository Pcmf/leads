<?php

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

 $dt->data2=='null' ? $dt->data2=$dt->data1 : null;

$resp = array();

if($dt->sts == 4 || $dt->sts==8 || ($dt->sts >=10 && $dt->sts <=13) ){
$query = sprintf("SELECT L.*,U1.nome AS nomeGestor, U2.nome AS nomeAnalista, FL.nome AS nomeFornecedor, P.outrainfo AS informacoes "
        . " FROM arq_leads L"
        . " LEFT JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_utilizadores U1 ON U1.id=L.user "
        . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
        . " INNER JOIN cad_fornecedorleads FL ON FL.id=L.fornecedor "
        . " WHERE L.fornecedor=%s AND DATE(L.datastatus)>= '%s' AND DATE(L.datastatus)<='%s' AND L.status=%s GROUP BY L.id",$dt->forn, $dt->data1,$dt->data2, $dt->sts);
} else{
 
$query = sprintf("SELECT L.*,U1.nome AS nomeGestor, U2.nome AS nomeAnalista, FL.nome AS nomeFornecedor, F.montante AS valor, P.outrainfo AS informacoes "
        . " FROM arq_leads L INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_utilizadores U1 ON U1.id=L.user "
        . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
        . " INNER JOIN cad_fornecedorleads FL ON FL.id=L.fornecedor "
        . " LEFT JOIN cad_financiamentos F ON F.lead=L.id  "
        . " WHERE L.fornecedor=%s AND DATE(L.datastatus)>= '%s' AND DATE(L.datastatus)<='%s' AND L.status=%s GROUP BY L.id",$dt->forn, $dt->data1,$dt->data2, $dt->sts);
}
$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
   // echo $query;
    echo json_encode($resp);
}

