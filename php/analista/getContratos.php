<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$lead = file_get_contents("php://input");

$resp = array();
    //Obter qual documentação que foi pedida
    $result0=mysqli_query($con,sprintf("SELECT * "
            . " FROM arq_contratos "
            . " WHERE lead=%s ",$lead));
    if($result0){
        
        while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
            array_push($resp, $row0);
        }
        echo json_encode($resp);
    }