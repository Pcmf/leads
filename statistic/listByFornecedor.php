<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../php/openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

if($tm =='hoje'){
    $sel2s = ' DATE(dataentrada)=DATE(NOW()) ';
}

if($tm=='semana'){
    $sel2s = ' YEAR(dataentrada)=YEAR(NOW()) AND WEEK(dataentrada)=WEEK(NOW()) ';
}    
if($tm=='mes'){
    $sel2s =' YEAR(dataentrada)=YEAR(NOW()) AND MONTH(dataentrada)=MONTH(NOW()) ';
}
if($tm=='mespassado'){
    $sel2s =' YEAR(dataentrada)=YEAR(NOW()) AND MONTH(dataentrada)=MONTH(NOW())-1 ';
}


$resp = array();

$query = sprintf("SELECT nomelead,tipo, count(*) AS qty,sum(montante) AS valor FROM `arq_leads`"
        . "  WHERE ".$sel2s." AND fornecedor =%s GROUP BY nomelead,tipo",$dt->fornecedor->id);


$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}


