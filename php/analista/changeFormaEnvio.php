<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$query = sprintf("UPDATE cad_financiamentos SET formaenvio='%s' WHERE lead=%s AND processo='%s' ", $dt->formaEnv, $dt->p->lead,$dt->p->processo);

$result = mysqli_query($con, $query);
