<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';

$lead = file_get_contents("php://input");


//Alterar o status da lead
$query = sprintf("UPDATE arq_leads SET status=16,datastatus=NOW() WHERE id=%s",$lead);
$result = mysqli_query($con,$query);
echo $query;