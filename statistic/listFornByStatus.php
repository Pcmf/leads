<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//require_once '../php/openCon.php';
require_once '../class/configs.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

if($tm =='hoje'){
    $sel2 = ' DATE(dataentrada)=DATE(NOW()) ';
    $sel2s = ' DATE(datastatus)=DATE(NOW()) ';
}

if($tm=='semana'){
    $sel2 = ' YEAR(dataentrada)=YEAR(NOW()) AND WEEK(dataentrada)=WEEK(NOW()) ';
    $sel2s = ' YEAR(datastatus)=YEAR(NOW()) AND WEEK(datastatus)=WEEK(NOW()) ';
}    
if($tm=='mes'){
    $sel2 =' YEAR(dataentrada)=YEAR(NOW()) AND MONTH(dataentrada)=MONTH(NOW()) ';
    $sel2s =' YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW()) ';
}
if($tm=='mespassado'){
    $sel2 =' YEAR(dataentrada)=YEAR(NOW()) AND MONTH(dataentrada)=MONTH(NOW())-1 ';
    $sel2s =' YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW())-1 ';
}


$resp = array();

$queryRcb = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
        . "  WHERE ".$sel2." AND L.fornecedor =%s ",$dt->fornecedor->id);
$result = mysqli_query($con, $queryRcb);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['LEADRCB']=$row[0];
}

$queryNATRB = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
        . "  WHERE ".$sel2s." AND L.fornecedor =%s "
        . " AND L.status IN(".NATRB.")",$dt->fornecedor->id);
$result = mysqli_query($con, $queryNATRB);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['NATRB']=$row[0];
}

$queryANUL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
        . "  WHERE ".$sel2s." AND L.fornecedor =%s "
        . " AND L.status IN(".ANULGST.",".NATND.")",$dt->fornecedor->id);
$result = mysqli_query($con, $queryANUL);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['ANUL']=$row[0];
}

$queryRECANL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
        . "  WHERE ".$sel2s." AND L.fornecedor =%s "
        . " AND L.status IN(".RECANL.")",$dt->fornecedor->id);
$result = mysqli_query($con, $queryRECANL);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['RECANL']=$row[0];
}


echo json_encode($resp);