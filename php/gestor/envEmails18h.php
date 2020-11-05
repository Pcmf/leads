<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../../restful/pushNotificationFunction.php';
require_once '../../class/sendEmail.php';
$user = file_get_contents("php://input");

//Selecionar LEADS do utilizador que tenham status=8 e que a data agendada seja menor ou igual á atual
//Obter do processo o nome, email
//da documentação pedida o que já foi recebido e o que falta receber 
$query = sprintf("SELECT L.id, U.nome AS gestor, U.email AS gemail, U.deviceId, U.telefone,"
        . " P.nome, P.email, P.telefone AS telefoneSMS, DATEDIFF(NOW(),A.agendadata) AS agendadias "
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_agenda A ON A.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.status IN(8,37,38) AND L.user=%s AND A.tipoagenda=3 AND A.status=1 AND A.agendadata<=DATE(NOW())",$user);
$result = mysqli_query($con,$query);
if($result){
     while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        
        //Enviar SMS
        if($row['agendadias']==0){
           //Dia agendado
            $sms = "Está a um pequeno passo de obter crédito! Basta enviar-nos a documentação para concluirmos o processo.    ".$row['gestor'];
            
            $deviceId = $row['deviceId'];
            $telefone = $row['telefoneSMS'];
            $msg = array("telefone"=>$telefone,"sms"=>$sms);
            sendPushNotificationToFCM($deviceId,$msg);
            mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s', %s,'I') ", $sms.' ;   Lead: '.$row['id'], $row['gestor']));
        }        
        

        $query0 = sprintf("SELECT N.* FROM cad_docpedida D "
                . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc"
                . " WHERE D.lead=%s AND D.recebido=0 ", $row['id']);
        $result0 = mysqli_query($con,$query0);
        if($result0){
            $listDocs = array();
            while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
                array_push($listDocs, $row0);
            }
            echo envEmail($row,$listDocs,$user,$con);
           // echo $query0;
        }

    } 
}







//Função para enviar os elmails
function envEmail($row,$listDocs,$user,$con){
    $motivocontaco = 10;
    //Verificar quantas tentativas foram feitas
    $result = mysqli_query($con, sprintf("SELECT count(*) FROM cad_registocontacto WHERE lead=%s AND motivocontacto=10 ",$row['id'] ));
    if($result) {
        $row0= mysqli_fetch_array($result,MYSQLI_NUM);
        
        $recup = checkRecuperacao4($row['id'], $con);
        
        if(($row0[0]>1 && $row0[0]<7) || ($recup>0 && $row0[0] <12) ){
            //normal
            
            $assunto="(A)ID:".$row['id']." - O Seu Crédito Aqui! !";

            //Lista dos documentos pedidos
            $lista = "<ul>";
            foreach($listDocs AS $d){
                $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span>";
                if ($d['link']) {
                    $lista .="(<a href='".$d['link']."'>obter aqui</a>)";
                }
                $lista .="</li>";
            }
            
            // Obter simulação
            $simulacao ='';
            $result = mysqli_query($con, sprintf("SELECT * FROM cad_simula WHERE lead=%s LIMIT 1", $row['id']));
            if($result) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $simulacao = "Valor: ".$row['valorpretendido']."€, Prazo ".$row['prazopretendido']." Meses, Prestação: ".$row['prestacaopretendida']." / mês";
            }
            
            //Obter o telefone do gestor
            $result = mysqli_query($con, sprintf("SELECT telefone FROM cad_utilizadores WHERE id=%s ", $user));
            if($result) {
                $rowTlm = mysqli_fetch_array($result, MYSQLI_ASSOC);
            }
            
            $lista .="</ul>";
            //Mensagem
            $msg = "<p>Olá ".$row['nome']."!</p> "
                    . "<p>Está a um passo de obter crédito para a sua simulação! Não perca mais tempo! </p>"
                    .  "<p>".$simulacao."</p>"
                    . "<p>Não se esqueça que este valor é meramente indicativo e corresponde a um valor médio de simulação!</p>"
                    . "<p>Pode submeter os seus documentos através da sua  "
                    . "<strong><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></strong>"
                    . " <strong>( senha: " . $senha . " )</strong></p>"
                    . "<p>".$lista."</p>"
                    . "<p>Pode também fazer-nos chegar através do "
                    . "<a href='https://api.whatsapp.com/send?1=pt&phone=+351" . $rowTlm['telefone'] . "'>WhatsApp</a>"
                    . "   ou em resposta a este e-mail.</p>"
                    . "<p>Ficamos a aguardar a sua resposta!</p>"
                    . "<p>Até já!</p>";  
            
            //Se o numero de tentativas for 5 vai inserir no cad_agendadoc para obrigar a fazer chamada
            if($row0[0]==4){
                // registar no agendadoc - agenda uma chamada para o dia seguinte
                mysqli_query($con, sprintf("INSERT INTO cad_agendadoc(lead, data) VALUES(%s, DATE(NOW()+ INTERVAL 1 DAY)) ", $row['id']));
            }

        } else {
            //Envio de aviso e anula a lead com status 9 
                $assunto="(A)ID:".$row['id']." - Ups! Pedido Cancelado por Falta de Documentos";

                //Lista dos documentos pedidos
                $lista = "<ul>";
                foreach($listDocs AS $d){
                    $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span></li>";
                }
                $lista .="</ul>";
                //Mensagem
                $msg = "<p>Olá ".$row['nome'].".</p> "
                        . "<p>Se ainda estiver interessado/a pode fazer uma simulação totalmente grátis e sem compromissos através da "
                        . "<a href='https://gestlifes.com/GestLifesAC'>Área de Cliente</a></p>"
                    ."<p>Caso pretenda ajuda via telefone basta indicar-nos o melhor horário de contacto respondendo a este e-mail.</p>";

                        //Cancelar a LEAD e limpa a agenda
                        mysqli_query($con, sprintf("UPDATE arq_leads SET status=9, datastatus=NOW() WHERE id=%s ", $row['id']));
                        mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0  WHERE lead=%s AND status=1", $row['id']));
                        $motivocontaco = 10;
        }
    }   
    
        $result = new sendEmail($con,$user, $row['gemail'], $row['email'], $assunto, $msg,"", 28, $row['id']);        
        if($result){
//            mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envsms,envemail,motivocontacto) "
//                . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),0,1,%s)"
//                ,$row['id'],$user,$row['id'],$user, $motivocontaco));
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }   
 }

 function checkRecuperacao4($lead,$con){
     $result = mysqli_query($con, sprintf("SELECT count(*) FROM arq_histrecuperacao WHERE lead=%s AND status=4 ", $lead));
     if($result){
         $row = mysqli_fetch_array($result, MYSQLI_NUM);
         return $row[0];
     } else {
        return 0;
     }
 }