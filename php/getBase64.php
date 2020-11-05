<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$query = sprintf("SELECT fxbase64 FROM arq_emaildocs WHERE dataentrada='%s' AND email='%s' AND linha=%s ",
        $dt->dataentrada,$dt->email,$dt->linha);

$result =mysqli_query($con,$query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    echo $row[0];
} else {
    echo $query;
}
        