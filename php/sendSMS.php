<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'openCon.php';
require_once '../class/configs.php';
require_once '../restful/pushNotificationFunction.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

//obter o deviceId do utilizador
$query = sprintf("SELECT  deviceId FROM  cad_utilizadores WHERE id=$dt->user");
$result = mysqli_query($con,$query);
if($result){
   $row = mysqli_fetch_array($result,MYSQLI_ASSOC);

            $deviceId = $row['deviceId'];
            $msg = array("telefone"=>$dt->telefone,"sms"=>$dt->sms);
            //Envia o SMS
            $result =sendPushNotificationToFCM($deviceId,$msg);
            //Regista o envio
            mysqli_query($con, sprintf("INSERT INTO arq_log( log, user, tipo ) "
                    . "VALUES( '%s', %s, '2')", $dt->sms.' ;   Lead: '.$dt->lead.' Resp: '.$result, $dt->user));
        
}