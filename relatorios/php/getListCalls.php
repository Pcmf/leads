<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';

$telefone = file_get_contents("php://input");


$resp = array();

$result = mysqli_query($con, sprintf("SELECT * FROM cad_registochamadas WHERE telefone LIKE '%s' ORDER BY data DESC", $telefone));
if($result){
    while ($row = mysqli_fetch_array($result)) {
        array_push($resp,$row);
    }
    echo json_encode($resp);
}