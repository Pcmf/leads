<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../php/openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);

if($tm =='hoje'){
    $sel2s = ' DATE(datastatus)=DATE(NOW()) ';
}

if($tm=='semana'){
    $sel2s = ' YEAR(datastatus)=YEAR(NOW()) AND WEEK(datastatus)=WEEK(NOW()) ';
}    
if($tm=='mes'){
    $sel2s =' YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW()) ';
}
if($tm=='mespassado'){
    $sel2s =' YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW())-1 ';
}


$resp = array();

//$query = sprintf("SELECT * "
//        . " FROM arq_leads "
//        . " WHERE ".$sel."(datastatus)=".$sel2." AND fornecedor=%s AND nomelead='%s' AND status=%s",
//        $dt->id,$dt->nome,$dt->sts);


$query = sprintf("SELECT L.id, L.nome,L.telefone,L.email,L.montante AS valorpretendido,S.nome AS status,L.datastatus,"
        . " U.nome AS gestor, U2.nome AS analista "
        . " FROM arq_leads L LEFT JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
        . " WHERE ".$sel2s." AND L.fornecedor=%s AND nomelead='%s' AND status=%s",
        $dt->id,$dt->nome,$dt->sts);
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}