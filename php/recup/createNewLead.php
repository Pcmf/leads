<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);
$user = $dt->user;
$leadInfo = $dt->lead;
$user->tipo == 'GRec' ? $status = 102 : $status = 2;
//Criar a LEAD 
$query = sprintf("INSERT INTO arq_leads(nomelead, fornecedor, dataentrada, status, datastatus, user) " 
        . " VALUES('%s', %s, NOW(), %s, NOW(), %s) ", 
        $leadInfo->nomelead, $leadInfo->fornecedor->id, $status, $user->id);
$result =mysqli_query($con,$query);

if($result){
    echo mysqli_insert_id($con);
} else {
    echo 0;
}
