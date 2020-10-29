<?php

/* 
 * Atualiza O ststus da lead para suspendida
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$query = sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s",$dt->status, $dt->lead);
$result= mysqli_query($con, $query);

if($result){
    echo 'OK';
} else {
    echo $query;
}