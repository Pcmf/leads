<?php
require_once '../openCon.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$json = file_get_contents("php://input");
$dt = json_decode($json);

$result = mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=%s WHERE lead=%s",$dt->ativa, $dt->lead ));

return $result;