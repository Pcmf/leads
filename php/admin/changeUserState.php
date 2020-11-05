<?php
session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$user = json_decode($json);

if($user->ativo){
$result = mysqli_query($con, sprintf("SELECT COUNT(*) FROM arq_leads WHERE user=%s OR analista=%s", $user->id,$user->id));
if($result){
    $row= mysqli_fetch_array($result,MYSQLI_NUM);
    if( $row[0]>0){
        //coloca o utilizador como inativo
        mysqli_query($con, sprintf("UPDATE cad_utilizadores SET ativo=0 WHERE id=%s", $user->id));
        return;
    } 
}
$query = sprintf("DELETE FROM cad_utilizadores WHERE id=%s",$user->id);
$result = mysqli_query($con,$query);

return;
} else {
    //Ativa o utilizador
        mysqli_query($con, sprintf("UPDATE cad_utilizadores SET ativo=1 WHERE id=%s", $user->id));
        return;
}