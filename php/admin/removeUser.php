<?php
session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../logClass.php';

$id = file_get_contents("php://input");
$query = sprintf("DELETE FROM cad_utilizadores WHERE id=%s",$id);
$result = mysqli_query($con,$query);
logClass::log($_SESSION['user'], $query, 'D');
if($result){
    $resp = array();
    $query2 = "SELECT * FROM cad_utilizadores";
    $result2 = mysqli_query($con,$query2);

    if($result2){
        while ($row = mysqli_fetch_array($result2,MYSQLI_ASSOC)) {
            array_push($resp, $row);
        }
        echo json_encode($resp);
    } else {
       echo $query2;
    }
} else {
  echo $query; 
}