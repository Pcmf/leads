<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);


//Se for gestor só seleciona os que tiverem status inferior a 10
if($dt->tipo=="Gestor"){
    $result= mysqli_query($con, sprintf("UPDATE arq_leads SET user=%s WHERE user=%s "
            . " AND status <10 AND status NOT IN(3,4,5)",$dt->userdestino,$dt->userorigem));
    if($result){
        mysqli_query($con, sprintf("UPDATE cad_agenda SET user=%s WHERE user=%s AND status=1",$dt->userdestino,$dt->userorigem));
        $resp = '{"erro":""}';
        echo $resp;        
    }else {
        $resp = '{"erro":"Não é possivél realizar esta operação!"}';
        echo $resp;        
    }
        
}

//Se for Analista só seleciona os que tiverem status superior a 11 e
// diferente de Financiado,Desistiu e Recusado apos financiamento
if($dt->tipo=="Analista"){
    $result= mysqli_query($con, sprintf("UPDATE arq_leads SET analista=%s WHERE analista=%s"
            . " AND status >11 AND status NOT IN(17,18,19)",$dt->userdestino,$dt->userorigem));
    if($result){
        $resp = '{"erro":""}';
        echo $resp;        
    }else {
        $resp = '{"erro":"Não é possivél realizar esta operação!"}';
        echo $resp;        
    }
        
}

