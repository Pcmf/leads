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

////PARA ENVIO DE SMS - apenas se o status da lead for nova ou ativa <= 2
//if($dt->lead->status <=2){
//    $deviceId = $dt->user->deviceId;
//    $telefone = $dt->lead->telefone;
//    $sms = "Esta a um passo de obter credito. Consulte o seu email ou indique a que horas prefere ser contactado. ".$dt->user->email;
//    $msg = array("telefone"=>$telefone,"sms"=>$sms);
//    $smsResponse = json_decode( sendPushNotificationToFCM($deviceId,$msg));
//    if ($smsResponse->failure) {
//        echo $smsResponse->results[0]->error;
//    } else {
//        echo $smsResponse->success;
//    }
//    //return;  //PARA REMOVER
//     mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s',%s,'m') ",
//             'Lead: '.$dt->lead->id.'  Resp: '.json_encode($smsResponse).'SMS: '.$sms, $dt->user->id));
//}

//Verificar as condições de agendamento //Vai buscar a data do primeiro agendamento
$query00 = sprintf("SELECT data FROM cad_agenda WHERE lead=%s  AND user=%s ORDER BY data ASC LIMIT 1",$dt->lead->id,$dt->user->id);
$result00 = mysqli_query($con,$query00);
if($result00){
    $row00 = mysqli_fetch_array($result00, MYSQLI_ASSOC);
    $numAgendamentos = 0;
    $rs = mysqli_query($con, sprintf("SELECT count(*) AS qty FROM cad_agenda WHERE lead=%s  AND user=%s AND tipoagenda IN(5,6)",$dt->lead->id,$dt->user->id));
    if($rs){
        $rowrs = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $numAgendamentos = $rowrs['qty'];
    }

    $objAg = new classAgendamento($row00['data']); //{dataAg,periodoAg} 
    //Obtem o primeiro dia util  (não tem em conta os feriados)
    $agenda = $objAg->getDataAgenda();
    //Se for para agendar
    if($numAgendamentos < 3 && $agenda['agenda']){
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
           mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",$dt->lead->id,$dt->user->id));
           //Agendar
           mysqli_query($con,sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
                   . " VALUES(%s,%s,'%s','%s',%s,5,1)",$dt->lead->id,$dt->user->id,$agenda['dataAg'],$horaAg,$agenda['periodoAg']));
           //atualizar o status da lead
           //Alterar o status da lead para Agendada
            $query = sprintf("UPDATE arq_leads SET status=33, datastatus=NOW(), user=%s WHERE id=%s",$dt->user->id,$dt->lead->id);
            mysqli_query($con,$query);
            //Obter uma senha
            $pass = gerarPassword(6);
            //Criar registo no cad_clientes com uma password
            // limpar os registos no cad_cliente para esta lead
            mysqli_query($con, sprintf("DELETE  FROM cad_clientes WHERE lead=%" , $dt->lead->id));
            // faz um novo registo
            mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, password) VALUES(%s, '%s', '%s', '%s') ",
            $dt->lead->id, $dt->lead->nome, $dt->lead->email, passwordHash::hash($pass)));  

            $assunto = "Ref: ".$dt->lead->id." - Obtenha o seu crédito agora!!";
            
            $motivoContacto = 1;
       }
    } else {
        //Não é para agendar porque excedeu o tempo para tentaivas de contacto
        //Vai anular a LEAD status 5
           //coloca status a zero na agenda
           mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",$dt->lead->id,$dt->user->id)); 
           //Colocar a LEAD com o status 5
           mysqli_query($con,sprintf("UPDATE arq_leads SET status=34,datastatus=NOW(), user=%s WHERE id=%s",$dt->user->id,$dt->lead->id));
           //Enviar email com mensagem de ultima oportunidade
           //Enviar o email
    $assunto = "Ref: ".$dt->lead->id." - Ultima Oportunidade para o seu Financiamento!";
    
    $motivoContacto=9;
    
    } 
    ///Envio do email.
    
    $emailDestino = $dt->lead->email;
    $nomeCliente = $dt->lead->nome;
    $emailOrigem = $dt->user->email;
    $nomeGestor = $dt->user->nome;
    $senha='';
    isset($pass) ?  $senha = "Senha de acesso: ".$pass : null;
    
            $msg = "<p>Olá ".$nomeCliente."</p>"
                    . "<p>No seguimento da sua simulação de crédito, vimos oferecer os nossos "
                    . "serviços para o/a ajudar a conseguir o crédito que procura!</p>"
                    . "<p>Estamos <a href='https://www.bportugal.pt/intermediariocreditofar/embrace-calculus-unipessoal-lda'>"
                    . "vinculados no Banco de Portugal</a> e já ajudámos mais de 1.000 pessoas em 2019</p>"
                    . "<p>Trabalhamos com marcas como <strong>Cofídis, Cetelem, Novo Banco, Credibom e Unicre.</strong> "
                    . "Desta forma conseguimos comparar por si a melhor oferta para que obtenha as "
                    . "<strong>melhores taxas e condições sem custos para o cliente!</strong></p>"
                    . "<p>Acelere todo o processo aqui:</p>"
                    . "<h2><a href='https://gestlifes.com/GestLifesClient/#/login'>&#9758; Portal do Cliente</a></h2>"
                    . "<h3><strong>( ".$senha." )</strong></h3>"
                    . "<p>Após ter preenchido irá receber uma resposta imediata ao pedido!</p>"
                    . "<p><ul>"
                    . "<li>Caso queira ser contactado por telefone, não há problema! Basta responder-nos a este e-mail,"
                    . " indicando qual o melhor período para o fazermos.</li>"
                    . "<li>Caso queira submeter os dados diretamente por e-mail basta <strong><u>seguir estas indicações:</u></strong></li>"
                    . "</ul></p>"
                    . "<p><ol>"
                    . "<li>Valor do Crédito?</li>"
                    . "<li>Quer pagar em quantos meses?</li>"
                    . "<li>Finalidade?</li>"
                    . "<li>Profissão?</li>"
                    . "<li>Estado Cívil?</li>"
                    . "<li>Tipo de habitação? (Arrendada / Familiar / Própria / Própria com Crédito - Que valor paga?)</li>"
                    . "</ol></p>"
                    . "<p>Documentação a enviar: </p>"
                    . "<p>"
                    ."<ol>"
                    ."<li>Cartão de Cidadão. (frente e verso)</li>"
                    ."<li>Comprovativo de morada.<small>(Com data inferior a 3 meses) Ex. ou documentos aceites:"
                        . " comprovativo domicilio fiscal (retirado no aceite das finanças), carta da agua, luz, internet.</small></li>"
                    ."<li>Último IRS ou código de validação que pode encontrar este código no canto superior da primeira página do seu IRS.</li>"
                    ."<li>3 recibos de vencimento ou comprovativo de reforma.</li>"
                    ."<li>Comprovativo do IBAN. <small>Onde venha mencionado nome do titular (Com data inferior a 3 meses)</small></li>"
                    ."<li>Mapa de Responsabilidades Cidadão (<a href='http://bit.ly/mapaderesponsabilidades'>pode descarregar aqui</a>)."
                    . " <small><strong>Cidadão, Utilizar dados do Portal das Finanças(NIF + Senha das Finaças)</strong></small> </li>"
                    . "</ol>"
                    ."</p>";    
    
            //Enviar email
        $result = new sendEmail($con,$dt->user->id,  $emailOrigem, $emailDestino, $assunto, $msg,"", 4,$dt->lead->id);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }   
        
    
}

