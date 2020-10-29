<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
require_once '../../class/configs.php';
require_once '../../restful/pushNotificationFunction.php';
$user = file_get_contents("php://input");

//Selecionar LEADS do utilizador que tenham status=8 e que a data agendada seja a do dia anterior
//Obter do processo o nome, email
//da documentação pedida o que já foi recebido e o que falta receber 
$query = sprintf("SELECT L.id, U.nome AS gestor, U.deviceId, U.telefone,"
        . " P.telefone AS telefoneSMS, DATEDIFF(NOW(),A.agendadata) AS agendadias "
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_agenda A ON A.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.status=8 AND L.user=%s AND A.tipoagenda=3 AND A.status=1 AND A.agendadata<DATE(NOW())",$user);
$result = mysqli_query($con,$query);
if($result){
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        //se ficou de enviar no dia anterior
        if($row['agendadias']==2){
            //Dia seguinte
            $sms = "Não se esqueça que basta enviar-nos um e-mail para poder obter o crédito que pediu."
                    . " Vai desistir agora? Estamos a aguardar! ".$row['gestor'];
            
            $deviceId = $row['deviceId'];
            $telefone = $row['telefoneSMS'];
            $msg = array("telefone"=>$telefone,"sms"=>$sms);
            sendPushNotificationToFCM($deviceId,$msg);
         //  mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'I')", $resp.' ;   Lead: '.$row['id'], $row['gestor']));
            mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'2')", $sms.' ;   Lead: '.$row['id'], $row['gestor']));
        }
        
    }
    
    
}