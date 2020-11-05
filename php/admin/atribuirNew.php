<?php

/* 
 * Admin - atribuir uma lead a um determinado Gestor
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);

//Verificar que ainda não foi atribuida
$result0 = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE (status=2  || status=102) AND id=%s",$dt->lead));
if(mysqli_affected_rows($con)==0){
    $status = 2;
    if($dt->user->tipo=='GRec') { $status = 102;}
    $query = sprintf("UPDATE arq_leads SET user=%s, status=%s, datastatus=NOW() WHERE id=%s",$dt->user->id, $status, $dt->lead);
    $result = mysqli_query($con, $query);
    if($result){
        echo 'OK';
    } else {
        echo $query;
    }
} else {
    echo 'Erro, já foi puxada';
}