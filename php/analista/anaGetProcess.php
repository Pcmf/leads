<?php

/* 
 * This will get the process/LEAD information for the olderst LEAD with 
 * status equal to 10 or 11
 * At same time make viablity calculations
 * 
 * 
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();
$query = sprintf("SELECT L.id AS lead  FROM arq_leads L "
        . " WHERE L.status IN (10,11,20) AND (SELECT count(*) FROM arq_leads A WHERE A.status='12' AND A.analista=%s)<1 AND "
        . " (L.analista IS NULL OR L.analista=%s)  ORDER BY L.datastatus ASC LIMIT 1",$dt->user,$dt->user);

$result = mysqli_query($con,$query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $resp['processo']= $row;
    //Atualiza o status da lead com 'Em analise'
    mysqli_query($con,sprintf("UPDATE arq_leads SET status=12,datastatus=NOW(), analista=%s WHERE id=%s",$dt->user,$row['lead']));
    //Registar o movimento no registo de contacto 
    mysqli_query($con, sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,motivocontacto) "
            . " VALUES(%s,%s,1,NOW(),11)",$row['lead'],$dt->user));
    
    echo json_encode($resp);
} else {
    echo $query;
}