<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once './DB.php';
require_once './pushNotificationFunction.php';


$json = file_get_contents("php://input");
$dt= json_decode($json);

$db = new DB();
if($dt->lead>0){
    $db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, dtcontacto, motivocontacto) "
            . " VALUES(:lead, :user, (SELECT MAX(A.contactonum) FROM cad_registocontacto A WHERE A.lead=:lead)+1, NOW(), 30) "
            , [':user'=>$dt->user->id, ':lead'=>$dt->lead]);
}


$deviceId = $dt->user->deviceId;
$telefone = $dt->telefone;
$msg = array("telefone"=>$telefone);
echo sendPushNotificationToFCM($deviceId,$msg);

