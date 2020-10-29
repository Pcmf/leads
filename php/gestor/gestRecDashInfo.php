<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);



$resp = array();
//Novas
$result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L "
                . " LEFT JOIN arq_histrecuperacao R ON R.lead=L.id "
                . " WHERE R.lead IS NULL AND L.status IN(5,9)  AND  DATEDIFF( DATE(NOW()), DATE(L.dataentrada) ) < 60 ");

if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['novas'] = $row[0];
} else {
    $resp['novas']=0;
}
//Ativa
$result = mysqli_query($con, sprintf("SELECT count(*) AS qty FROM arq_leads WHERE  status=28", $dt->id));
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['ativa'] = $row[0];
}

//Agendadas com data ultrapassada
$result = mysqli_query($con, sprintf("SELECT count(*) AS qty FROM arq_leads L "
                . " INNER JOIN cad_agenda A ON A.lead=L.id WHERE L.status IN(32, 33) "
                . "  AND A.status=1 AND (A.agendadata < DATE(NOW()) OR ( A.agendadata=DATE(NOW()) AND HOUR(A.agendahora) <= HOUR(NOW()))) "
                . " ORDER BY A.agendadata ASC, A.agendahora ASC", $dt->id));
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['agenda'] = $row[0];
} else {
    $resp['agenda'] = 0;
}


echo json_encode($resp);