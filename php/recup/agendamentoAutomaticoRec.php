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
require_once '../../class/configs.php';
require_once '../../restful/pushNotificationFunction.php';
require_once "../passwordHash.php";
include_once '../PasswordGenerator.php';
$data = file_get_contents("php://input");

$dt = json_decode($data);
$lead = $dt->lead;


//Verificar as condições de agendamento //Vai buscar a data do primeiro agendamento
$query00 = sprintf("SELECT data FROM cad_agenda WHERE lead=%s  AND user=%s ORDER BY data ASC LIMIT 1",$lead->lead, $dt->user->id);
$result00 = mysqli_query($con,$query00);
if($result00){
    $row00 = mysqli_fetch_array($result00, MYSQLI_ASSOC);
    $numAgendamentos = 0;
    $rs = mysqli_query($con, sprintf("SELECT count(*) AS qty FROM arq_histprocess WHERE lead=%s  AND status=106",
            $lead->lead));
    if($rs){
        $rowrs = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $numAgendamentos = $rowrs['qty'];
    }

    $objAg = new classAgendamento($row00['data']); //{dataAg,periodoAg} 
    //Obtem o primeiro dia util  (não tem em conta os feriados)
    $agenda = $objAg->getDataAgenda();
    //Se for para agendar
    if($numAgendamentos < 2 && $agenda['agenda']){
        //agendar - vai tentar agendar para a primeira hora livre (por intervalos de 1m);
        //verifica, para a data pretendida, a hora do ultimo agendamento
       $r1 = mysqli_query($con,sprintf("SELECT agendahora FROM cad_agenda "
               . " WHERE agendadata='%s' AND agendaperiodo =%s AND tipoagenda=5 AND status=1  AND user=%s "
               . " ORDER BY agendahora DESC limit 1",$agenda['dataAg'],$agenda['periodoAg'],$dt->user->id));
       if($r1){
           $row = mysqli_fetch_array($r1,MYSQLI_NUM);
           if($row[0]>0){
               $horaAg = date("H:i:s", strtotime('+1 minutes', strtotime($row[0])));
           } else {  //Se não tiver agendamento para a data selecionada
               if($agenda['periodoAg'] == 1){
                   $horaAg= date("H:i:s", strtotime('9:30:00'));
               }
               if($agenda['periodoAg'] == 2){
                   $horaAg= date("H:i:s", strtotime('14:30:00'));
               }
           }
          //coloca status a zero antes de fazer um novo agendamento 
           mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",
                   $lead->lead,$dt->user->id));
           //Agendar
           mysqli_query($con,sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
                   . " VALUES(%s,%s,'%s','%s',%s,5,1)",
                   $lead->lead, $dt->user->id, $agenda['dataAg'], $horaAg, $agenda['periodoAg']));
           //atualizar o status da lead
           //Alterar o status da lead para Agendada
            $query = sprintf("UPDATE arq_leads SET status=106, datastatus=NOW(), user=%s WHERE id=%s",
                    $dt->user->id, $lead->lead);
            mysqli_query($con,$query);
            //Obter uma senha
            $pass = gerarPassword(6);
            //Criar registo no cad_clientes com uma password
            // limpar os registos no cad_cliente para esta lead
            mysqli_query($con, sprintf("DELETE  FROM cad_clientes WHERE lead=%" , 
                    $lead->lead));
            // faz um novo registo
            mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, password) VALUES(%s, '%s', '%s', '%s') ",
            $lead->lead, $lead->nome, $lead->email, passwordHash::hash($pass)));  

            $assunto = "Ref: ".$lead->lead." - Obtenha o seu crédito agora!!";
            
            $motivoContacto = 1;
       }
    } else {
        //Não é para agendar porque excedeu o tempo para tentaivas de contacto
        //Vai anular a LEAD status 5
           //coloca status a zero na agenda
           mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",
                   $lead->lead, $dt->user->id)); 
           //Colocar a LEAD com o status 105
           mysqli_query($con,sprintf("UPDATE arq_leads SET status=105,datastatus=NOW(), user=%s WHERE id=%s",
                   $dt->user->id, $lead->lead));
           //Enviar email com mensagem de ultima oportunidade
           //Enviar o email
    $assunto = "Ref: ".$lead->lead." - Anulação de proposta.";
    
    $motivoContacto=9;
    
    } 
    ///Envio do email.
    
    $emailDestino = $lead->email;
    $nomeCliente = $lead->nome;
    $emailOrigem = $dt->user->email;
    $nomeGestor = $dt->user->nome;
    $senha='';
    isset($pass) ?  $senha = "Senha de acesso: ".$pass : null;
    
            $msg = "<p>Olá ".$nomeCliente."</p>"
                    ."<p>Agradeço desde já a atenção dispensada.</p>"
                    . "<p>No seguimento do seu pedido de crédito, gostaríamos de falar consigo afim de podermos perceber melhor os motivos que o levou a desistir/cancelar o seu pedido de crédito.</p>"
                    . "<p>Temos todo o interesse em ajuda-lo a conseguir a melhor oferta para o seu crédito, com as melhores taxas e condições e sem quaisquer custos para o si!</p>"
                    . "<p>Por favor, indique a melhor hora de contacto em resposta a este email ou <a href='https://api.whatsapp.com/send?1=pt&phone=351".$dt->user->telefone."'>WhatsApp</a></p>"
                    . "<p>Use a <strong><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></strong></p>"
                    . "<h3><strong>( ".$senha." )</strong></h3>"
                    . "<p>Ao usar a <strong>Área de Cliente</strong> estará a dar mais rapidez ao seu processo! </p>"
                    
                    . "<p>Em alternativa, sugerimos um cartão de crédito sem quaisquer custos associados"
                    . " (poderá consultar as condições e ofertas no documento em anexo. </p>"
                    . "<p>Caso pretenda dar seguimento ao mesmo, <u>basta responder a este email com essa mesma intenção.</u></p>"
                    . "<p>Agradecendo a sua confiança e ficando à sua disposição para qualquer estudo posterior.</p>"

                    ."</p>";    
    
            //Enviar email
        $anexo = "../cc/condicoes_CC.pdf";
        $result = new sendEmail($con, $dt->user->id,  $emailOrigem, $emailDestino, $assunto, $msg, $anexo, 4, $lead->lead);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }   
        
    
}

