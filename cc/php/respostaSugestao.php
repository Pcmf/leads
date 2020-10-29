<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


if($dt->resp==1){
    $status = 2;
} else {
    $status = 3;
}
mysqli_query($con, sprintf("UPDATE cad_cartaocredito SET dataresposta= NOW(), respostacliente=%s, status=%s WHERE lead=%s ", $dt->resp, $status, $dt->lead));

return;
