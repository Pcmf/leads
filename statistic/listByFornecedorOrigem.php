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
    $sel2 = ' DATE(datastatus)=DATE(NOW()) ';
}

if($tm=='semana'){
    $sel2 = ' YEAR(datastatus)=YEAR(NOW()) AND WEEK(datastatus)=WEEK(NOW()) ';
}    
if($tm=='mes'){
    $sel2 =' YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW()) ';
}
if($tm=='mespassado'){
    $sel2 =' YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW())-1 ';
}


$resp = array();
if(!isset($dt->tipo)){
$query = sprintf("SELECT S.nome,S.descricao,S.id , count(*) AS qty FROM `arq_leads` L "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . "  WHERE ".$sel2." AND L.fornecedor =%s AND L.nomelead='%s'"
        . " AND L.status IN(3,4,5,8,10,11,12,13,1415,16,17,18)"
        . " GROUP BY S.nome",$dt->fornecedor, $dt->origem);
} else {
$query = sprintf("SELECT S.nome,S.descricao,S.id , count(*) AS qty FROM `arq_leads` L "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . "  WHERE ".$sel2." AND L.fornecedor =%s AND L.nomelead='%s' "
        . " AND L.status IN(3,4,5,8,10,11,12,13,1415,16,17,18) AND L.tipo='%s'"
        . " GROUP BY S.nome",$dt->fornecedor, $dt->origem,$dt->tipo);    
}

$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}