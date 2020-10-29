<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../php/openCon.php';
require_once '../restful/pushNotificationFunction.php';
require_once '../class/sendEmail.php';
require_once '../class/RegistContact.php';

$RegContacto = new RegistContact();

$result01 = mysqli_query($con,"SELECT * from cad_utilizadores where (tipo='Gestor' OR tipo='GRec') AND ativo=1");
if ($result01) {
    while ($row01 = mysqli_fetch_array($result01, MYSQLI_ASSOC)) {
        $user = $row01;

//Selecionar LEADS do utilizador que tenham status=8, 37, 38 ou 108 e que a data agendada seja menor ou igual á atual
//Obter do processo o nome, email
//da documentação pedida o que já foi recebido e o que falta receber 
        $query = sprintf("SELECT L.id, L.status, U.nome AS gestor, U.email AS gemail, U.deviceid, U.telefone,"
                . " P.nome, P.email, P.telefone AS telefoneSMS, P.tipocredito, P.valorpretendido, DATEDIFF(NOW(),A.agendadata) AS agendadias "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cad_agenda A ON A.lead=L.id "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " WHERE L.status IN(8,37,38, 108) AND L.user=%s AND A.tipoagenda IN(3,5)"
                . " AND A.status=1 AND A.agendadata<=DATE(NOW())", $user['id']);
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

                //Enviar SMS
                if ($row['agendadias'] == 0) {
                    //Dia agendado
                    $sms = "Está a um pequeno passo de obter crédito! Basta enviar-nos a documentação para concluirmos o processo.    ".$row['gestor'];

                    $deviceId = $row['deviceid'];
                    $telefone = $row['telefoneSMS'];
                    $msg = array("telefone" => $telefone, "sms" => $sms);
                    sendPushNotificationToFCM($deviceId, $msg);
                    $RegContacto->registContact($row['id'], $row['gestor'], 10, 1);
                    mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'I') ", $sms . ' ;   Lead: ' . $row['id'], $row['gestor']));
                }

                // Obter  a lista da documentação
                $query0 = sprintf("SELECT N.* FROM cad_docpedida D "
                        . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc"
                        . " WHERE D.lead=%s AND D.recebido=0 ", $row['id']);
                $result0 = mysqli_query($con, $query0);
                if ($result0) {
                    $listDocs = array();
                    while ($row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
                        array_push($listDocs, $row0);
                    }
                    echo envEmail($row, $listDocs, $user, $con);
                    usleep(5000000); //pausa por 5s
                }
            }
        }
    }
}

//Função para enviar os emails
function envEmail($row, $listDocs, $user, $con) {
    //Verificar quantos dias de atraso
        if ($row['agendadias'] < 7) {
            $tc = '';
            //Tipo de credito
            switch ($row['tipocredito']) {
                case 'CP':
                    $tc = "Crédito Pessoal - " . $row['valorpretendido'] . " Euros";
                    break;
                case 'CC':
                    $tc = "Crédito Consolidado - " . $row['valorpretendido'] . " Euros";
                    break;
                case 'CT':
                    $tc = 'Cartão de Crédito';
                    break;
                case 'CHCC':
                    $tc = "Crédito Hipotecário - Consolidado com Crédito Habitação - " . $row['valorpretendido'] . " Euros";
                    break;
                case 'CH1':
                    $tc = "Crédito  Hipotecário - 1ª Hipoteca" . $row['valorpretendido'] . " Euros";
                    break;
                case 'CH2':
                    $tc = "Crédito  Hipotecário - 2ª Hipoteca" . $row['valorpretendido'] . " Euros";
                    break;
            }
            $assunto = "(A)Ref:" . $row['id'] . " - Documentação em atraso para ".$tc;

            //Lista dos documentos pedidos
            $lista = "<ul>";
            foreach ($listDocs AS $d) {
                $lista .= "<li><u>" . $d['nomedoc'] . ".</u> <span>" . $d['descricao'] . "</span></li>";
            }
            $lista .= "</ul>";
            //Mensagem
            //Mensagem
                $msg = "<p>Olá Sr(a) ".$row['nome'].",</p> "
                        . "<p>Informamos que continuamos aguardar o envio da documentação, para obter a sua aprovação!</p>"
                        .  getSimulacao($row['id'], $con)
                        . "<p>Documentação a enviar, para obter a sua aprovação: </p>"
                        . "<p>".$lista."</p>"
                    ."<p>Caso pretenda ajuda a organizar a sua documentação e poupar o seu tempo, basta facultar a sua senha das finanças"
                     ." e podemos faze-lo por si, deste modo obtemos os seguintes documentos:<br/>"
                      ."-Mapa de Responsabilidades de Crédito do Banco de Portugal, último IRS entregue e o comprovativo de morada.<br/>"
                       ."Assim, só terá que nos fazer chegar a restante documentação solicitada.</p>"
             . "<p>Use a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a>, "
                . " o <a href='https://api.whatsapp.com/send?1=pt&phone=351".$user['telefone']."'>&#9758; WhatsApp</a>"
                . " ou responda a este email anexando a documentação pedida.</p>"
            ."<p>Ao usar a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a> para anexar a documentação estará a dar mais rapidez ao seu processo! </p>";                 
                              


            // Novo pedido de documentação
            $result = new sendEmail($con, $user['id'], $row['gemail'], $row['email'], $assunto, $msg, "", 10, $row['id']);

            
            //Se o numero de tentativas for 5 vai inserir no cad_agendadoc para obrigar a fazer chamada
            if ($row['agendadias'] == 4) {
                // registar no agendadoc - agenda uma chamada para o dia seguinte
                mysqli_query($con, sprintf("INSERT INTO cad_agendadoc(lead, data, ativa) VALUES(%s, DATE(NOW()+ INTERVAL 1 DAY), 1) ", $row['id']));
            }
            
            
        } else {
            //Envio de aviso e anula a lead com status 9 ou 109
            $assunto = "(A)Ref:" . $row['id'] . " - Aviso! Cancelamento do processo de credito por falta de documentação.";

            //Lista dos documentos pedidos
            $lista = "<ul>";
            foreach ($listDocs AS $d) {
                $lista .= "<li><u>" . $d['nomedoc'] . ".</u> <span>" . $d['descricao'] . "</span></li>";
            }
            $lista .= "</ul>";
            //Mensagem
                $msg = "<p>Olá Sr(a) ".$row['nome'].",</p> "
                        . "<p>Agradeço desde já a atenção dispensada.</p>"
                        . "<p>Informamos que o seu pedido de crédito irá ser cancelado, por falta de envio da documentação solicitada.</p>"
                        . "<p>Caso ainda pretenda avançar com o seu pedido de crédito, basta enviar a sua documentação e reabrimos o seu processo. </p>"
                        . "<p>Documentação a enviar, para obter a sua aprovação: </p>"
                        . "<p>".$lista."</p>"
                    ."<p>Caso pretenda ajuda a organizar a sua documentação e poupar o seu tempo, basta facultar a sua senha das finanças"
                     ." e podemos faze-lo por si, deste modo obtemos os seguintes documentos:<br/>"
                      ."-Mapa de Responsabilidades de Crédito do Banco de Portugal, último IRS entregue e o comprovativo de morada.<br/>"
                       ."Assim, só terá que nos fazer chegar a restante documentação solicitada.</p>"
             . "<p>Use a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a>, "
                . " o <a href='https://api.whatsapp.com/send?1=pt&phone=351".$user['telefone']."'>&#9758; WhatsApp</a>"
                . " ou responda a este email anexando a documentação pedida.</p>"
            ."<p>Ao usar a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a> para anexar a documentação estará a dar mais rapidez ao seu processo! </p>";                 
                              


            //Cancelar a LEAD e limpa a agenda
            $row['status'] == 8 ? $newSts = 9 : $newSts =109;
            mysqli_query($con, sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s ", $newSts, $row['id']));
            mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0  WHERE lead=%s AND status=1", $row['id']));
            mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0  WHERE lead=%s AND ativa=1", $row['id']));
            //Email de aviso de cancelamento
            $result = new sendEmail($con, $user['id'], $row['gemail'], $row['email'], $assunto, $msg, "", 28, $row['id']);
        }
}

function getSimulacao($lead, $con) {
    $result = mysqli_query($con, sprintf("SELECT valorpretendido, prazopretendido, prestacaopretendida, tipocredito "
            . " FROM cad_simula WHERE lead=%s ORDER BY linha DESC ", $lead));
    if ($result) {
        $lista = "";
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $lista = $lista. "<h3>Valor da simulação: ".$row['valorpretendido']." Euros. Prazo: ".$row['prazopretendido']." meses."
                . " Prestação: ".$row['prestacaopretendida']." Euros.</h3>";
        }
        $lista = $lista . "<small>(Informamos que este valor é meramente indicativo correspondendo a um valor médio de simulação) </small>";
            
        return $lista;
        
    } else {
        return '';
    }
}

