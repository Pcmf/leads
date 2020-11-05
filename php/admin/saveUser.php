<?php
session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../passwordHash.php';
require_once '../logClass.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);

!isset($dt->rgpdkey) ? $dt->rgpdkey='' : null;
$dt->password = passwordHash::hash($dt->password); 
!isset($dt->fornecedor) ? $dt->fornecedor=0 : null;
if(isset($dt->id) && $dt->id >0){  //Edit
    $query = sprintf("UPDATE cad_utilizadores SET nome='%s', email='%s',telefone='%s', "
            . " tipo='%s', username='%s',password='%s', outrainfo='%s', data=NOW(), rgpdkey='%s', fornecedor=%s "
            . " WHERE id=%s",
            $dt->nome,$dt->email,$dt->telefone,$dt->tipo,$dt->username,
            $dt->password,$dt->outrainfo, $dt->rgpdkey,$dt->fornecedor, $dt->id);
    $tipo = 'U';
} else {
    $query = sprintf("INSERT INTO cad_utilizadores(nome,email,telefone,tipo,fornecedor,username,password,outrainfo)"
            . " VALUES('%s','%s','%s','%s', %s,'%s','%s','%s')",
            $dt->nome,$dt->email,$dt->telefone,$dt->tipo,$dt->fornecedor, $dt->username,
            $dt->password, $dt->outrainfo);
    $tipo = 'I';
}
$result = mysqli_query($con,$query);
logClass::log($con,$_SESSION['user'], str_replace('\'','',$query), $tipo);
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