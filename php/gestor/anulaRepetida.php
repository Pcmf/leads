<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);


//Alterar o status da lead
$query = sprintf("UPDATE arq_leads SET status=4, datastatus=NOW(), user=%s WHERE id=%s", $dt->user, $dt->lead);

$result = mysqli_query($con,$query);
if($result){
    mysqli_query($con, sprintf("INSERT INTO cad_rejeicoes( lead, motivo) VALUES(%s, 'Repetida') ", $dt->lead));
    echo 'Anulada';
    return;
}
echo 'Erro';

