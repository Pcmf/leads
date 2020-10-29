<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$nome=$dt->file->filename;
isset($dt->novonome) && $dt->novonome !='' ? $nome= $dt->novonome : null;

//verificar se já existe algum contrato
$result = mysqli_query($con, sprintf("SELECT max(linha) FROM arq_contratos where lead=%s",$dt->lead));
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    
        $query = sprintf("INSERT INTO arq_contratos(lead,linha,nome,tipo,fx64) "
                . "VALUES(%s,%s,'%s','%s','%s')", $dt->lead,$row[0]+1,$nome,
                substr($dt->file->filetype,strpos($dt->file->filetype,"/")+1),$dt->file->base64);
        $result =mysqli_query($con, $query);
        if(!$result){
            echo $query;
        }

    
} else {
   echo 'upps' ;
}
