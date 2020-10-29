<?php

date_default_timezone_set('Europe/Lisbon');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../classAgendamento.php';
require_once '../../class/sendEmail.php';
//require_once '../../class/configs.php';
require_once '../../restful/pushNotificationFunction.php';
require_once "../passwordHash.php";
include_once '../PasswordGenerator.php';
$data = file_get_contents("php://input");

$dt = json_decode($data);

//PARA ENVIO DE SMS - apenas se o status da lead for nova ou ativa <= 2
if ($dt->lead->status <= 2) {
    $deviceId = $dt->user->deviceId;
    $telefone = $dt->lead->telefone;
    $sms = "Esta a um passo de obter credito. Consulte o seu email ou indique a que horas prefere ser contactado. " . $dt->user->email;
    $msg = array("telefone" => $telefone, "sms" => $sms);
    $smsResponse = json_decode(sendPushNotificationToFCM($deviceId, $msg));
    if ($smsResponse->failure) {
        echo $smsResponse->results[0]->error;
    } else {
        echo $smsResponse->success;
    }
    mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s',%s,'m') ",
                    'Lead: ' . $dt->lead->id . '  Resp: ' . json_encode($smsResponse) . 'SMS: ' . $sms, $dt->user->id));
}

//Verificar as condições de agendamento //Vai buscar a data do primeiro agendamento
$query00 = sprintf("SELECT data FROM cad_agenda WHERE lead=%s  AND user=%s ORDER BY data ASC LIMIT 1", $dt->lead->id, $dt->user->id);
$result00 = mysqli_query($con, $query00);
if ($result00) {
    $row00 = mysqli_fetch_array($result00, MYSQLI_ASSOC);

    $objAg = new classAgendamento($row00['data']);
    //Obtem o primeiro dia util  (não tem em conta os feriados)
    $agenda = $objAg->getDataAgenda();

    //Se for para agendar
    if ($agenda['agenda']) {
        //agendar - vai tentar agendar para a primeira hora livre (por intervalos de 1m);
        //verifica, para a data pretendida, a hora do ultimo agendamento
        $r1 = mysqli_query($con, sprintf("SELECT agendahora FROM cad_agenda "
                        . " WHERE agendadata='%s' AND agendaperiodo =%s AND tipoagenda=1 AND status=1  AND user=%s "
                        . " ORDER BY agendahora DESC limit 1", $agenda['dataAg'], $agenda['periodoAg'], $dt->user->id));
        if ($r1) {
            $row = mysqli_fetch_array($r1, MYSQLI_NUM);
            if ($row[0] > 0) {
                $horaAg = date("H:i:s", strtotime('+1 minutes', strtotime($row[0])));
            } else {  //Se não tiver agendamento para a data selecionada
                if ($agenda['periodoAg'] == 1) {
                    $horaAg = date("H:i:s", strtotime('9:30:00'));
                }
                if ($agenda['periodoAg'] == 2) {
                    $horaAg = date("H:i:s", strtotime('14:30:00'));
                }
            }
            //coloca status a zero antes de fazer um novo agendamento 
            mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s", $dt->lead->id, $dt->user->id));
            //Agendar
            mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
                            . " VALUES(%s,%s,'%s','%s',%s,1,1)", $dt->lead->id, $dt->user->id, $agenda['dataAg'], $horaAg, $agenda['periodoAg']));
            //atualizar o status da lead
            //Alterar o status da lead para Agendada
            $query = sprintf("UPDATE arq_leads SET status=6,datastatus=NOW(), user=%s WHERE id=%s", $dt->user->id, $dt->lead->id);
            mysqli_query($con, $query);
            //Obter uma senha
            $pass = gerarPassword(6);
            //Criar registo no cad_clientes com uma password
            mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, password) VALUES(%s, '%s', '%s', '%s') ",
                            $dt->lead->id, $dt->lead->nome, $dt->lead->email, passwordHash::hash($pass)));

            $assunto = "Ref: " . $dt->lead->id . " - Obtenha o seu crédito agora!!";

            $motivoContacto = 1;
            ///Envio do email.
            if ($dt->lead->status > 2) {
                $emailDestino = $dt->lead->email;
                $nomeCliente = $dt->lead->nome;
                $emailOrigem = $dt->user->email;
                $nomeGestor = $dt->user->nome;
                $senha = '';
                isset($pass) ? $senha = "Senha de acesso: " . $pass : null;


                $result = mysqli_query($con, sprintf("SELECT nomelead FROM arq_leads WHERE id=:lead", $dt->lead->id));
                if ($result) {
                    $row00 = mysqli_fetch_array($result, MYSQLI_ASSOC);
                } else {
                    $row00['nomelead'] = "Gestlifes";
                }

                $msg = "<p>Olá " . $nomeCliente . "!</p>"
                        . "<p>Recebemos o seu pedido do crédito através do " . $row00['nomelead'] . " Por favor continue a submissão de dados"
                        . " através da "
                        . "<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>"
                        . "<h3><strong>( " . $senha . " )</strong></h3>"
                        . "<p>Trabalhamos com as melhores marcas de crédito em Portugal e temos a licença "
                        . " <a href='https://www.bportugal.pt/intermediariocreditofar/jpcom-unipessoal-lda'>nº0001409</a> do Banco Portugal. </p>"
                        . "<p>Desta forma conseguimos comparar por si a melhor oferta para que obtenha "
                        . "<strong>as melhores taxas, sem custos e sem compromissos.</strong></p>";

                //Enviar email
                $result = new sendEmail($con, $dt->user->id, $emailOrigem, $emailDestino, $assunto, $msg, "", 4, $dt->lead->id);
                if ($result) {
                    echo "Mensagem enviada com sucesso!";
                } else {
                    echo "Erro no envio do email. Por favor contacte suporte!";
                }
            }
        }
    } else {
        $emailDestino = $dt->lead->email;
        $nomeCliente = $dt->lead->nome;
        $emailOrigem = $dt->user->email;
        $nomeGestor = $dt->user->nome;
        //Não é para agendar porque excedeu o tempo para tentaivas de contacto
        //Vai anular a LEAD status 5
        //coloca status a zero na agenda
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s", $dt->lead->id, $dt->user->id));
        //Colocar a LEAD com o status 5
        mysqli_query($con, sprintf("UPDATE arq_leads SET status=5,datastatus=NOW(), user=%s WHERE id=%s", $dt->user->id, $dt->lead->id));
        //Enviar email com mensagem de ultima oportunidade
        //Enviar o email
        $assunto = "ID: " . $dt->lead->id . " - Ups! Pedido Cancelado! ";

        $motivoContacto = 9;
        $msg = "<p>Olá " . $nomeCliente . "!</p>"
                . "<p>Infelizmente o seu pedido de crédito foi <strong>cancelado</strong>.</p>"
                . "<p>Se ainda estiver interessado/a pode fazer uma simulação totalmente grátis e sem compromissos através da:  "
                . "<strong><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></strong>"
                . " <strong>( senha: " . $senha . " )</strong></p>"
                . "<p>Caso pretenda ajuda via telefone basta indicar-nos o melhor horário de contacto respondendo a este e-mail.</p>"
                . "<p>Obrigado e até já!</p>";

        //Enviar email
        $result = new sendEmail($con, $dt->user->id, $emailOrigem, $emailDestino, $assunto, $msg, "", 4, $dt->lead->id);
        if ($result) {
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }
    }
}

