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

//Selecionar LEADS do utilizador que tenham status=16 e que o contrato esteja no cliente á mais de 2 dias
// e que incompleto seja NULL
//Obter do processo o nome, email
        $query = sprintf("SELECT L.id, U.nome AS analista, U.deviceId, U.telefone, U.email, "
                . " P.telefone AS telefoneSMS, DATEDIFF(DATE(NOW()), DATE(F.dtcontratocliente)) AS dias"
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cad_financiamentos F ON F.lead=L.id"
                . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                . " WHERE L.status=16 AND L.analista=%s AND F.dtcontratocliente IS NOT NULL AND F.dtcontratoparceiro IS NULL "
                . " AND incompleto IS NULL AND DATEDIFF(DATE(NOW()), DATE(F.dtcontratocliente)) >2", $user);
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                //se ficou de enviar no dia anterior
                    //Dia seguinte
                if ($row['dias']=1){
                    $sms = "Conforme combinado, estamos a aguardar a devolucao do seu contrato de credito. Obrigado  " . $row['email'];
                } elseif($row['dias']<10) {
                    $sms = "Relembramos que ficou de nos devolver o contrato de credito, informamos que o mesmo ainda nao foi rececionado. " . $row['email'];
                }
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