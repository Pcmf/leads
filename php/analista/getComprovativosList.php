<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$lead = file_get_contents("php://input");

$resp = array();

$query = sprintf("SELECT * FROM cad_comprovativos WHERE lead= %s", $lead);

$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
}