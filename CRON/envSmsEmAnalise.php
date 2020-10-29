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
$result0 = mysqli_query($con, "SELECT * from cad_utilizadores where tipo='Analista' and ativo=1");
if ($result0) {
    while ($row1 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
        $user = $row1['id'];

//Selecionar LEADS do utilizador que tenham status IN(10,11,12,13) e que a data do status seja superior a 2 dias
//Obter do processo o nome, email
        $query = sprintf("SELECT L.id, U.nome AS analista, U.deviceId, U.telefone,"
                . " P.telefone AS telefoneSMS "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                . " WHERE L.status IN(10,11,12,13) AND L.analista=%s AND DATEDIFF(DATE(NOW()), DATE(L.datastatus)) >=2", $user);
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                // Mensagem
                    $sms = "O seu processo de credito esta em analise. Por favor aguarde sua conclusao. Obrigado " . $row['analista'];

                    $deviceId = $row['deviceId'];
                    $telefone = $row['telefoneSMS'];
                    $msg = array("telefone" => $telefone, "sms" => $sms);
                    sendPushNotificationToFCM($deviceId, $msg);
                    $RegContacto->registContact($row['id'], $user, 10,1);
                    mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'2')", $sms . ' ;   Lead: ' . $row['id'], $row['analista']));
                    usleep(100000);
            }
        }
    }
}