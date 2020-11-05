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
        . " L.datastatus, PA.nome AS parceiro "
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
        . " INNER JOIN cad_parceiros PA ON PA.id=F.parceiro "
        . " WHERE L.analista=%s AND L.status=41 AND F.status=6 ",
        $dt->user);


$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}