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
$query = sprintf("UPDATE cad_speedup SET visto=1, datavisto=NOW() WHERE lead=%s",$dt);
$result = mysqli_query($con,$query);

echo $query;



