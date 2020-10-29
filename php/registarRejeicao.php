<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';
require_once '../class/sendEmail.php';
$json = file_get_contents("php://input");

$dt = json_decode($json);
!isset($dt->lead->id) ? $id = $dt->lead->lead : $id = $dt->lead->id;
!isset($dt->motivo->motivo) ? $dt->motivo->motivo = '' : null;
!isset($dt->motivo->obs) ? $dt->motivo->obs = '' : null;

$dt->motivo->motivoComum == "Falta de Documentação" ? $status = 9 : $status = 4;
//Rejeita LEAd e regista o motivo
$query = (sprintf("UPDATE arq_leads SET status=%s ,datastatus=NOW(), info='%s',user=%s WHERE id=%s  "
                , $status, $dt->motivo->motivoComum, $dt->user->id, $id));
mysqli_query($con, $query);
// Desativar da Agenda
mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s", $id));

//Regista o motivo
mysqli_query($con, sprintf("INSERT INTO cad_rejeicoes(lead,motivo,outro,obs) VALUES(%s,'%s','%s','%s')",
                $id, $dt->motivo->motivoComum, $dt->motivo->motivo, $dt->motivo->obs));
//Desativar no cad_agendadoc
mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s", $id));

//Se o motivo for incidentes bancarios
//if ($dt->motivo->motivoComum == 'Incidentes Bancários') {
//    sendEmailRecover($con, $id, $dt->user);
//}

if ($dt->motivo->motivoComum == "OutrosNoEmail") {
    echo 'Sem envio de email';
    return;
}

// Registar no contacto
    $query1 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,outrainfo,envemail,motivocontacto) "
            . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
            $id, $dt->user->id, $id, $dt->user->id, $dt->motivo->motivoComum, 0, 7);
    mysqli_query($con, $query1);

//echo $query0;
//Enviar o email para o cliente
    //Obter os dados do cliente
    $query0 = sprintf("SELECT nome, email, telefone"
            . " FROM arq_leads WHERE id=%s", $id);
    $result0 = mysqli_query($con, $query0);
    if ($result0) {
        $row = mysqli_fetch_array($result0, MYSQLI_ASSOC);

        $dt->motivo->motivoComum == 'outro motivo a registar' ? $motivoComum = null : "<li>" . $motivoComum = $dt->motivo->motivoComum . "</li>";


        //Prepara para criar e enviar email
        $assunto = "ID: " . $id . " - Pedido Cancelado! .";
        $sujestaoCC = '';
//        if ($dt->motivo->motivoComum == 'Taxa de esforço elevada') {

        if ($dt->motivo->motivoComum != 'Incidentes Bancários' && $dt->motivo->motivoComum != 'Titulo de residência temporário' && $dt->motivo->motivoComum != 'Não declara rendimentos') {
            $anexo = "../cc/condicoes_CC.pdf";

            $sujestaoCC = "<p>Em alternativa, sugerimos um cartão de crédito sem quaisquer custos associados (poderá consultar as condições e ofertas no documento em anexo.</br>"
                    . " Caso pretenda dar seguimento ao mesmo, <u>basta responder a este email com essa mesma intenção.</u></p>";
            //Regista a sugestão
            mysqli_query($con, sprintf("INSERT INTO cad_cartaocredito(lead, sugerido, formasugestao, status, user) VALUES(%s, NOW(), 'Email', '1', %s) ", $id, $dt->user->id));
        }


        $msg = "<p>Olá Sr(a) " . $row['nome'] . "</p>"
                . "<p>Informamos que a proposta de simulação de Crédito solicitada por si. Foi cancelada pelo(s) motivo(s) seguinte(s): </p>"
                . "<ul>" . $motivoComum . "<li>" . $dt->motivo->motivo . "</li></ul>"
//                . $sujestaoCC
                . "<p>Agradecemos a atenção dispensada e caso precise de ajuda na obtenção de crédito no futuro, não deixe de nos contactar!</p>";


        //Enviar o email
        $result = new sendEmail($con, $dt->user->id, $dt->user->email, $row['email'], $assunto, $msg, "", 7, $id);
        if ($result) {
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }
    }

    function sendEmailRecover($con, $lead, $user) {
        //Obter os dados do cliente
        $query0 = sprintf("SELECT nome, email, telefone"
                . " FROM arq_leads WHERE id=%s", $lead);
        $result0 = mysqli_query($con, $query0);
        if ($result0) {
            $row = mysqli_fetch_array($result0, MYSQLI_ASSOC);


            $assunto = "Renegociação";
            $msg = "<p>" . $row['nome'] . "</p><p>" . $row['email'] . "</p><p>" . $row['telefone'] . "</p>";


            //Enviar o email
            $result = new sendEmail($con, $user->id, $user->email, "processos@renegociacao.pt", $assunto, $msg, "", 14, $lead);
            if ($result) {
                echo "Mensagem enviada com sucesso!";
            } else {
                echo "Erro no envio do email. Por favor contacte suporte!";
            }
        }
    }

