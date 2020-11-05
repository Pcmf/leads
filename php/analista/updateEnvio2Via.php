<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

!isset($dt->outraInfo)?$dt->outraInfo="":null;

$query = sprintf("UPDATE cad_financiamentos SET dt2via=NOW(), outrainfo=CONCAT(outrainfo,'%s') "
        . " WHERE lead=%s AND processo='%s' ",$dt->outraInfo, $dt->process->lead,$dt->process->processo);
$result = mysqli_query($con,$query);
if($result){
    echo 'OK';
} else {
   echo $query; 
}


