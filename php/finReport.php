<?php

/* 
 * Historico
 *Obter a quantidade de financiamentos e o valor por mes
 */

require_once 'openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();
$rt= array();
$query = sprintf("SELECT count(*) AS qty, MONTH(F.datafinanciado) AS mes, YEAR(F.datafinanciado) AS ano "
        . " FROM arq_leads L"
        . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
        . " WHERE F.status=7 "
        . " AND (L.user=%s OR L.analista=%s) GROUP BY ano, mes", $dt->user, $dt->user);

$result = mysqli_query($con, $query);
if($result){
    $r = array();
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $temp = array();
        $temp['label']= $row['mes'];
        $temp['labelAno']= $row['ano'];
        $temp['data']=$row['qty'];
        array_push($r, $temp);
    }
    array_push($rt, $r);
}

array_push($resp, $rt);


$rt= array();
//Obter  o valor  dos financiados por mes
$query0 =sprintf("SELECT YEAR(F.datafinanciado) AS ano, MONTH(F.datafinanciado) AS mes, SUM(F.montante)/1000 AS valor  FROM  cad_financiamentos F"
        . " INNER JOIN arq_leads L ON L.id=F.lead "
        . " WHERE F.status IN(7,23,24)  AND (L.user=%s OR L.analista=%s) GROUP BY ano, mes",$dt->user, $dt->user);
$result0 = mysqli_query($con, $query0);
if($result0){
    $r = array();
    while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
        $temp = array();
        //$temp['label']= $row0['mes'];
        $temp['data']=$row0['valor'];
        array_push($r, $temp);
    }
    array_push($rt, $r);
} 
array_push($resp, $rt);

echo json_encode($resp);

//AND YEAR(F.datafinanciado)=YEAR(NOW())