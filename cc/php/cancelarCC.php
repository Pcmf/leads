<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");

$dt = json_decode($json);

mysqli_query($con, sprintf("UPDATE cad_cartaocredito SET status=9 WHERE lead=%s ", $dt->lead));

mysqli_query($con, sprintf("INSERT INTO cad_rejeicoes(lead, motivo) VALUES(%s, '%s') ", $dt->lead, $dt->motivo));

return;
