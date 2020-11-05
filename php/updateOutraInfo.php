<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);


//Alterar o status da lead
$query = sprintf("UPDATE arq_processo SET outrainfo='%s' WHERE lead=%s",$dt->outrainfo, $dt->lead);
$result = mysqli_query($con,$query);

echo $query;



