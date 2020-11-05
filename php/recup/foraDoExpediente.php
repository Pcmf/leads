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
require_once '../../restful/pushNotificationFunction.php';
require_once "../passwordHash.php";
include_once '../PasswordGenerator.php';
$data = file_get_contents("php://input");

$dt = json_decode($data);
    

    //coloca status a zero antes de fazer um novo agendamento 
     mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",$dt->lead->id,$dt->user->id));
     //Agendar
     mysqli_query($con,sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
             . " VALUES(%s,%s,CURDATE() + INTERVAL 1 DAY,'10:00:00',1,1,1)",$dt->lead->id,$dt->user->id));
     //atualizar o status da lead
     //Alterar o status da lead para Agendada
      $query = sprintf("UPDATE arq_leads SET status=106,datastatus=NOW(), user=%s WHERE id=%s",$dt->user->id,$dt->lead->id);
      mysqli_query($con,$query);
      //Obter uma senha
      $pass = gerarPassword(6);
      //Criar registo no cad_clientes com uma password
      mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, password) VALUES(%s, '%s', '%s', '%s') ",
      $dt->lead->id, $dt->lead->nome, $dt->lead->email, passwordHash::hash($pass)));  

      $assunto = "Ref: ".$dt->lead->id." - Obtenha o seu crédito agora!";

      $motivoContacto = 1;
       
    ///Envio do email.
    
    $emailDestino = $dt->lead->email;
    $nomeCliente = $dt->lead->nome;
    $emailOrigem = $dt->user->email;
    $nomeGestor = $dt->user->nome;
    $senha='';
    if($pass) { $senha = "Senha de acesso: ".$pass;}
    
                $msg = "<p>Olá Sr.(a) ".$nomeCliente."</p>"
                        . "<p>Agradeço desde já a atenção dispensada.</p>"
                    . "<p>No seguimento da seu pedido de crédito, verificamos que a melhor hora de contacto para si, os nosso serviços encontram-se encerrados.</p>"
                    . "<p>Deste modo, <strong>solicitamos que aceda à</strong><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a>,"
                        . " ou através do <a href='https://api.whatsapp.com/send?1=pt&phone=351".$dt->user->telefone."'>&#9758; WhatsApp</a>, "
                        . "ou responda a este email, anexando a documentação pedida e respondendo ao questionário.</p>"
                        . "<p>Temos todo o interesse em ajuda-lo a conseguir a melhor oferta para o seu crédito, com as melhores taxas e "
                        . "condições e sem quaisquer custos para o si!</p>"
                        . "<p>Após ter preenchido na área de cliente irá receber uma resposta imediata ao pedido!</p>"
                        . "<p>Caso queira submeter os dados diretamente por e-mail basta <strong><u>seguir estas indicações:</u></strong></p>"
                        . "<ol>"
                        . "<li> Valor do Crédito?</li>"
                        . "<li> Quer pagar em quantos meses?</li>"
                        . "<li> Finalidade?</li>"
                        . "<li> Profissão?</li>"
                        . "<li> Estado Cívil?</li>"
                        . "<li> Tipo de habitação? (Arrendada / Familiar / Própria / Própria com Crédito - Que valor paga?)</li>"
                        . "</ol>"
                        . "<p>Documentos a enviar:</p>"
                        . "<ol>"
                        . "<li> Cartão de Cidadão. (frente e verso)</li>"
                        . "<li> Comprovativo de morada.(Com data inferior a 3 meses) "
                        . " Ex. ou documentos aceites: comprovativo domicilio fiscal (retirado no aceite das finanças), carta da agua, luz, internet.</li>"
                        . "<li> Último IRS ou código de validação que pode encontrar este código no canto superior da primeira página do seu IRS.</li>"
                        . "<li> 3 recibos de vencimento ou comprovativo de reforma.</li>"
                        . "<li> Comprovativo do IBAN. Onde venha mencionado nome do titular (Com data inferior a 3 meses)</li>"
                        . "<li> Tipo de habitação? (Arrendada / Familiar / Própria / Própria com Crédito - Que valor paga?)</li>"
                        . "<li> Mapa de Responsabilidades Cidadão (<a href='http://bit.ly/mapaderesponsabilidades'>pode descarregar aqui</a>). (Passos: Cidadão, Utilizar dados do Portal das Finanças(NIF + Senha das Finanças)) </li>"
                        . "</ol>"
                        . "<p>&nbsp;</p>";
          
    
            //Enviar email
        $result = new sendEmail($con,$dt->user->id,  $emailOrigem, $emailDestino, $assunto, $msg,"", 4, $dt->lead->id);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }   
        
    


