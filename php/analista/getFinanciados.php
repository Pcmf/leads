<?php

/* 
 * Obter os processos financiados para o analista e para o mes atual
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$month = date('n');

$resp = array();
$query = sprintf("SELECT P.lead,P.nome AS cliente,F.processo,F.montante,"
        . " DATE(F.datafinanciado) AS dtfinanciado,PA.nome AS parceiro,L.status, S.nome AS nomestatus, DATE(F.datastatus) AS datastatus "
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
        . " INNER JOIN cad_parceiros PA ON PA.id=F.parceiro "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status"
        . " WHERE L.analista=%s AND (L.status IN (17,24) AND F.status=7 AND YEAR(F.datafinanciado)=YEAR(NOW()) AND MONTH(F.datafinanciado)=MONTH(NOW())) "
        . " OR (L.status=25 AND F.status=12 AND YEAR(F.datastatus)=YEAR(NOW()) AND MONTH(F.datastatus)=MONTH(NOW())) ",$dt->user);


$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}