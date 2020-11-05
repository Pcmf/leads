<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';
$data = file_get_contents("php://input");
$fx = $data;


$resp = array();
if($fx=='cnf_docnecessaria'){
    $query = "SELECT * FROM ".$fx." ORDER BY ordem";
} else {
    $query = "SELECT * FROM ".$fx;
}
$result = mysqli_query($con,$query);

if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    mysqli_close($con);
    echo json_encode($resp);
} else{
    mysqli_close($con);    
    echo $query;
}
