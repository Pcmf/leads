<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
require_once '../../php/class.phpmailer.php';
require_once '../../php/class.smtp.php';
require_once '../../class/class.regSentEmail.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

if($dt->resp==1){
    $status = 4;
} else {
    $status = 6;
}
mysqli_query($con, sprintf("UPDATE cad_cartaocredito SET datarespostaparceiro= NOW(), respostaparceiro=%s, status=%s WHERE lead=%s ", $dt->resp, $status,  $dt->lead));



//Enviar um email a informar o cliente da rejeição
if($status == 6){
 $query0 = sprintf("SELECT L.analista AS analistaId, L.nome,L.email,U.nome AS analista, U.email AS aemail,U.telefone"
            . " FROM arq_leads L "
            . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
            . " WHERE L.id=%s", $dt->lead);
    
    $result0 = mysqli_query($con,$query0);
    if($result0){
        $row = mysqli_fetch_array($result0,MYSQLI_ASSOC);
        
        //Prepara para criar e enviar email
        $assunto = "Ref: ".$dt->lead." - Recusa de Cartão de Crédito.";
        $msg = "<p>Exmo(a). Sr(a) ".$row['nome']."</p>"
               . "<p>Face ao estado actual da sua situação, lamentamos informá-lo(a) de que não estamos em condições"
                . " de dar um seguimento favorável ao seu pedido.</p>"
            ."<p>Agradecendo a sua confiança e ficando à sua disposição para qualquer estudo posterior, apresentamos os nossos melhores cumprimentos,</p>"
            . "<p></p>"
            ."<p>GestLifes<br/>Rua de Camões nº111, 2º andar sala 11<br/>4000-144 Porto</p>"
            ."<p>Grato(a) pela atenção dispensada.</p>"
            ."<p> Atenciosamente,</p>"
           // . "<br/><img src='cid:logo_email_xs' width='250px' alt='logotipo Gestlifes'/>"
        . "<p><strong>".$row['analista']."</strong><br/>"
        . "Tlm: +351 ".$row['telefone']."<br/>"
        . "Email: ".$row['aemail']."<br/>"
        . "Rua de Camões, nº111,2ºandar sala11<br/>"
        . "4000-144 Porto<br/>"
        . "www.gestlifes.com</p>"
        . "<p><small>Este correio eletrónico é propriedade da GESTLIFES, deve ser considerado confidencial e dirigido unicamente aos seus destinatários.
            O acesso, cópia ou utilização desta informação por qualquer outra pessoa ou entidade não é autorizado.
            Se recebeu este documento por erro por favor notifique o remetente imediatamente.</small></p>";             
        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->Host = 'sv02.corporatemail.pt';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = $row['aemail'];
        $mail->Password = '20Embrac15';

        $mail->setFrom($row['aemail'],"GESTLIFES");
        $mail->addAddress($row['email']);
        
        //$mail->addBCC('gestlifestestes@gmail.com');


        $mail->Subject = utf8_decode($assunto);
        $mail->isHTML(TRUE);
        $mail->Body = utf8_decode($msg);

        $mail->WordWrap = 50;
        //LOG
        $log = new regSentemail($con,$row['analistaId'], $row['email'], $assunto);
        if(!$mail->send()){
            echo 'Erro no envio! Mailer error: '.$mail->ErrorInfo;
            $log->registErro($mail->ErrorInfo);
        } else { 
            echo 'Mensagem enviada com sucesso. ';
            $log->registOk();
        }         
        
    }
}




return;