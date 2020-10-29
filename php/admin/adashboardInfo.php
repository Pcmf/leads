<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
//require_once '../../class/configs.php';
$json = file_get_contents("php://input");
$dt = $json;

  


$resp= array();
$rt= array();
$ft= array();
$vt= array();


//Qty total de leads entraram por mes
$query = "SELECT YEAR(dataentrada) AS ano, MONTH(dataentrada) AS mes, count(*) AS qty FROM arq_leads GROUP BY ano, mes";
$result = mysqli_query($con, $query);
if($result){
    $r = array();
    $f = array();
    $v = array();
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $temp = array();
        $temp['label']= $row['mes'];
        $temp['labelAno']= $row['ano'];
        $temp['data']=$row['qty'];
        array_push($f ,getFinanciados($con, $row['ano'], $row['mes']));
        array_push($v ,getValor($con, $row['ano'], $row['mes']));
        array_push($r, $temp);
    }
    array_push($rt, $r);
    array_push($ft, $f);
    array_push($vt, $v);
}

array_push($resp, $rt);
array_push($resp, $f);
array_push($resp, $v);



$rt= array();

//Obter a quantidade das anuladas/não atendidas
$query = "SELECT YEAR(datastatus) AS ano, MONTH(datastatus) AS mes, count(*) AS qty FROM arq_leads WHERE status IN(3,4,5) GROUP BY ano, mes";
$result = mysqli_query($con, $query);
if($result){
    $r = array();
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $temp = array();
        $temp['data']=$row['qty'];
        array_push($r, $temp);
    }
    array_push($rt, $r);
}
array_push($resp, $rt);
$rt= array();

//Obter a quantidade das Recusadas/Não aprovazdas e desistencias
$query = "SELECT YEAR(datastatus) AS ano, MONTH(datastatus) AS mes, count(*) AS qty FROM arq_leads WHERE status IN(14,15,18,19) GROUP BY ano, mes";
$result = mysqli_query($con, $query);
if($result){
    $r = array();
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $temp = array();
        $temp['data']=$row['qty'];
        array_push($r, $temp);
    }
    array_push($rt, $r);
}
array_push($resp, $rt);
$rt= array();



echo json_encode($resp);




function getFinanciados($con,$ano, $mes){
    $rt= array();

//Obter o numero  dos financiados
$query0 = sprintf("SELECT count(*) AS qty  FROM  cad_financiamentos  "
                . " WHERE status=7 AND YEAR(datafinanciado)=%s AND MONTH(datafinanciado)=%s", $ano, $mes);
$result0 = mysqli_query($con, $query0);
if($result0){
    $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);
    $rt['data'] = $row0['qty'];
} else {
    $rt['data'] = 0;
}
    return $rt;
}

function getValor($con, $ano, $mes){
    $rt= array();
    //Obter  o valor  dos financiados por mes
    $query0 = sprintf("SELECT  SUM(F.montante)/1000 AS valor  FROM  cad_financiamentos F"
            . " WHERE F.status IN(7,23,24,12) AND YEAR(F.datafinanciado)=%s AND MONTH(F.datafinanciado)=%s", $ano, $mes);
    $result0 = mysqli_query($con, $query0);
    if($result0){
        $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);
        $rt['data'] = $row0['valor'];
    } else {
        $rt['data'] = 0;
    }      
    return $rt;
}