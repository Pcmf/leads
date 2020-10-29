<?php

/* 
 * Obter os processos recusadoe e desistencias  para o analista e para o mes atual
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$month = date('n');

$resp = array();

$query = sprintf("SELECT P.lead,P.nome AS cliente,P.valorpretendido AS montante,"
        . " DATE(L.datastatus) AS datastatus, R.motivo, S.nome AS status "
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " LEFT JOIN cad_rejeicoes R ON R.lead=L.id "
        . " WHERE L.analista=%s AND L.status IN(14,15,18,19) AND MONTH(L.datastatus)>=%s AND YEAR(L.datastatus)=YEAR(NOW()) ",
        $dt->user,$month-1);

$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}