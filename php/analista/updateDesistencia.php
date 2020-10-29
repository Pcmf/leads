<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);


$query = sprintf("UPDATE arq_leads SET status=18, datastatus=NOW() WHERE id=%s",$dt->lead);
$result = mysqli_query($con, $query);
if($result){
    mysqli_query($con, sprintf("UPDATE arq_processo SET outrainfo = concat(outrainfo, '\n %s') WHERE lead=%s ",$dt->motivo,$dt->lead));
    echo $query;
} else {
    echo $query;
}