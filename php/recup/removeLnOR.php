<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);

//Remover linha de outros rendimentos
$query = sprintf("DELETE FROM cad_outrosrendimentos WHERE lead=%s AND linha=%s", 
        $dt->or->lead, $dt->or->linha);
$result =mysqli_query($con,$query);


