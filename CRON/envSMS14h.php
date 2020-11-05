<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../php/openCon.php';
require_once '../class/configs.php';
require_once '../restful/pushNotificationFunction.php';
require_once '../class/RegistContact.php';

$RegContacto = new RegistContact();
$result0 = mysqli_query($con, "SELECT * from cad_utilizadores where tipo='Gestor' and ativo=1");
if ($result0) {
    while ($row1 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
        $user = $row1['id'];

//Selecionar LEADS do utilizador que tenham status=8 e que a data agendada seja a do dia anterior
//Obter do processo o nome, email
//da documentação pedida o que já foi recebido e o que falta receber 
        $query = sprintf("SELECT L.id, U.nome AS gestor, U.deviceId, U.telefone,"
                . " P.telefone AS telefoneSMS, DATEDIFF(NOW(),A.agendadata) AS agendadias "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cad_agenda A ON A.lead=L.id "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " WHERE L.status=8 AND L.user=%s AND A.tipoagenda=3 AND A.status=1 AND A.agendadata<DATE(NOW())", $user);
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                //se ficou de enviar no dia anterior
                if ($row['agendadias'] == 2) {
                    //Dia seguinte
                    $sms = "Continuamos a aguardar do envio da documentacao afim de podermos  concluir o seu pedido de credito. " . $row['gestor'] . ",Gestlifes";

                    $deviceId = $row['deviceId'];
                    $telefone = $row['telefoneSMS'];
                    $msg = array("telefone" => $telefone, "sms" => $sms);
                    sendPushNotificationToFCM($deviceId, $msg);
                    $RegContacto->registContact($row['id'], $user, 10,1);
                    //  mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'I')", $resp.' ;   Lead: '.$row['id'], $row['gestor']));
                    mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'2')", $sms . ' ;   Lead: ' . $row['id'], $row['gestor']));
                    usleep(100000);
                }
            }
        }
    }
}