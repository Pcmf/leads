<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);


//Verificar que a lead está num status 10 ou 11
$result0 = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s  AND status IN(10,11)",$dt->lead));
if($result0){
    if(mysqli_affected_rows($con)){
        //Executa a operação
        $result = mysqli_query($con, sprintf("UPDATE arq_leads SET status=12,analista=%s WHERE id=%s",$dt->user,$dt->lead));
        $resp = '{"erro":""}';
        echo $resp;
    }else{
        $resp = '{"erro":"Não é possivél realizar esta operação!"}';
        echo $resp;
    }   
}else{
    $resp = '{"erro":"Não é possivél realizar esta operação!"}';
    echo $resp;
}
