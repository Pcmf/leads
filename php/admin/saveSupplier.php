<?php
session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../passwordHash.php';
//require_once '../logClass.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);

$dt->password = passwordHash::hash($dt->password); 
if(isset($dt->id) && $dt->id >0){  //Edit
    $query = sprintf("UPDATE cad_fornecedorleads SET nome='%s',empresa='%s', email='%s',telefone='%s', "
            . " outrainfo='%s',api=%s,password='%s',ativo=%s WHERE id=%s",
            $dt->nome,$dt->empresa,$dt->email,$dt->telefone,$dt->outrainfo,$dt->api,
            $dt->password,$dt->ativo,$dt->id);
    $tipo = 'U';
} else {
    $query = sprintf("INSERT INTO cad_fornecedorleads(nome,empresa,email,telefone,outrainfo,api,password,ativo)"
            . " VALUES('%s','%s','%s','%s','%s',%s,'%s',1)",
            $dt->nome,$dt->empresa,$dt->email,$dt->telefone,$dt->outrainfo,$dt->api,$dt->password);
    $tipo = 'I';
}
$result = mysqli_query($con,$query);
//logClass::log($_SESSION['user'], str_replace('\'','',$query), $tipo);
if($result){
    $resp = array();
    $query2 = "SELECT * FROM cad_fornecedorleads";
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