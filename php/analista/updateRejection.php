<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../../class/sendEmail.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


//Atualizar o status da LEAD
$result = mysqli_query($con, sprintf("UPDATE arq_leads SET status=14, datastatus=NOW() WHERE id=%s", $dt->lead));
if ($result) {

    $anexo = "";
    //Regista o motivo
    !isset($dt->op->obs) ? $dt->op->obs = null : null;
    !isset($dt->op->motivo) ? $dt->op->motivo = null : null;
    mysqli_query($con, sprintf("INSERT INTO cad_rejeicoes(lead,motivo,outro,obs) VALUES(%s,'%s','%s','%s')", $dt->lead, $dt->op->motivoTipo, $dt->op->motivo, $dt->op->obs));
    //Enviar o email para o cliente
    //Obter os dados do cliente e do analista
    $query0 = sprintf("SELECT L.analista AS analistaId, P.nome,P.email,U.nome AS analista, U.email AS aemail,U.telefone"
            . " FROM arq_leads L INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
            . " WHERE L.id=%s", $dt->lead);

    $result0 = mysqli_query($con, $query0);

    if ($result0) {
        $row = mysqli_fetch_array($result0, MYSQLI_ASSOC);

        $sujestaoCC = "";
        if (isset($dt->op->CC) && $dt->op->CC) {
            $sujestaoCC = "Em alternativa, sugerimos um cartão de crédito sem quaisquer custos associados (poderá consultar as condições e ofertas no documento em anexo.</br>"
                    . "Caso pretenda dar seguimento ao mesmo, <u>basta responder a este email com essa mesma intenção.</u>";
            //Regista a sugestão
            mysqli_query($con, sprintf("INSERT INTO cad_cartaocredito(lead, sugerido, formasugestao, status, user) VALUES(%s, NOW(), 'Email', '1', %s) ", $dt->lead, $row['analistaId']));
            $anexo = "../../cc/condicoes_CC.pdf";
        }
        $necessita2t = "";
        //Se o motivo for Necessidade de segundo titular
        if ($dt->op->motivoTipo == '2º Titular') {
            $necessita2t = "<p><strong>Proposta passível de reanálise com inclusão de 2º titular, preferencialmente familiar direto.</strong>"
                    . " Caso pretenda incluir, basta responder a este email incluindo a seguinte documentação do 2º titular:<p>"
                    . "<ul>"
                    . "<li>Cartão de cidadão</li>"
                    . "<li>Comprovativo de morada</li>"
                    . "<li>Três ultimos recibos de vencimento ou comprovativo de reforma</li>"
                    . "<li>Declaração de IRS</li>"
                    . "<li>Mapa de Responsabilidades de Crédito do Banco de Portugal</li>"
                    . "</ul> ";
        }

        $dt->op->motivoTipo == 'outro motivo a registar' ? $motivoComum = null : $motivoComum = "<li>" . $dt->op->motivoTipo . "</li>";
        //Prepara para criar e enviar email
        
            $assunto = "Ref: " . $dt->lead . " - Anulação da proposta.";
            $msg = "<p>Exmo(a). Sr(a) " . $row['nome'] . "</p>"
                    . "<p>Informamos que a proposta de simulação de Crédito solicitada por si. Foi cancelada pelo(s) motivo(s) seguinte(s): </p>"
                    . "<ul>" . $motivoComum . "<li>" . $dt->op->motivo . "</li></ul>"
                    . "<br/>" . $necessita2t
                    . "<p>Agradecendo a sua confiança e ficando à sua disposição para qualquer estudo posterior.</p>"
                    . "<p></p>"
                    . "<p>" . $sujestaoCC . "</p>"
                    . "<p></p>";
        }
        
        if ($dt->op->motivoTipo != "outros SEM ENVIAR EMAIL") {
        //Enviar email
        $result = new sendEmail($con, $row['analistaId'], $row['aemail'], $row['email'], $assunto, $msg, $anexo, 7, $dt->lead);
        if ($result) {
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }
    }
}


return;

