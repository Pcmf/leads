<?php

/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once '../openCon.php';
require_once '../../class/sendEmail.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

//Registar os documentos anexados
foreach ($dt->docsAnx->docs as $line){
    $queryDOC = sprintf("UPDATE cad_docpedida SET recebido=1,datarecebido=NOW() "
        . " WHERE lead=%s AND tipodoc=%s",$dt->lead,$line->id );
    mysqli_query($con,$queryDOC);
}        


//Inserir os documentos anexados em base64
$ln =1;
foreach ($dt->files as $line){

    $queryDOC = sprintf("INSERT INTO arq_documentacao(lead,linha,nomefx,tipo,fx64) "
        . " VALUES(%s,%s,'%s','%s','%s')",$dt->lead,$ln,$line->filename,substr($line->filetype,strpos($line->filetype,"/")+1),$line->base64);
    mysqli_query($con,$queryDOC);
    $ln++;
} 

//Alterar o status da lead
$result =mysqli_query($con,sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s",$dt->status,$dt->lead));
if($result){
    enviarEmailInfo($dt->lead, $image, $dt->status);
    echo 'OK';
} else {
    echo "UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s",$dt->status,$dt->lead;
}

//Função para enviar email a informar a recepção da documentação
function enviarEmailInfo($lead,$image,$sts){
    $msg="";
    $query = sprintf("SELECT L.id,L.user,U.nome AS gestor,U.email AS gemail,U.telefone, P.nome,P.email"
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_agenda A ON A.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.id=%s ",$lead);

    $result = mysqli_query($con,$query);
    if($result){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
        
        //Toda documentação recebida e passou para analise
        if($sts==10){
            $assunto="ID: ".$row['id'].". Estamos quase a obter o seu crédito!! ";

            $msg = "<p>Olá ".$row['nome']."!</p> "
                    . "<p>Parabéns! A sua documentação foi aprovada e já está em análise pelos nossos analistas...</p>"
                    . "<p>Prometemos ser rápidos e dar-lhe uma resposta ao seu pedido em menos de 48horas!</p>"
                    ."<p>Até já!</p>";
        }
       
       //Passau para analise com documentação em falta
       if($sts==11){
            $query0 = sprintf("SELECT N.* FROM cad_docpedida D "
                 . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc"
                 . " WHERE D.lead=%s AND D.recebido=0 ", $row['id']);
            $result0 = mysqli_query($con,$query0);
            if($result0){
                $listDocs = array();
                while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
                    array_push($listDocs, $row0);
                }
            
               //Lista dos documentos pedidos
                $lista = "<ul>";
                foreach($listDocs AS $d){
                    $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span></li>";
                }
                $lista .="</ul>";

                $assunto="Ref: ".$row['id'].". Obrigado - Documentação em falta";
                $msg = "<p>Exmo(a). Sr(a) ".$row['nome'].",</p> "
                        . "<p>Venho por este meio confirmar a receção da sua documentação.<br/>"
                        . "No entanto verificamos que se encontra em falta o seguinte:</p>"
                        . "<p>".$lista."</p>"
                        . "<p>Mais informo que a mesma será encaminhada para análise.</p>"
                    . "<p>Veja o estado do seu processo em:</p>"
                    ."<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>";
                   
            }
        }
        
        
        
        
        
        //Enviar email
        $result = new sendEmail($con,$row['user'],  $row['gemail'],  $row['email'], $assunto, $msg,"", 10, $row['id']);        
        if($result){
            mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envsms,envemail,motivocontacto) "
                    . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),0,1,3)"
                     ,$row['id'],$row['user'],$row['id'],$row['user']));
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }   
        
        
        
        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->Host = 'sv02.corporatemail.pt';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = $row['gemail'];
        $mail->Password = '20Embrac15';

        $mail->setFrom($row['gemail'],"GESTLIFES");
        $mail->addAddress($row['email']);
        
    //    $mail->addBCC('gestlifestestes@gmail.com');


        $mail->Subject = utf8_decode($assunto);
        $mail->isHTML(TRUE);
        $mail->Body = utf8_decode($msg);
        $mail->AltBody = utf8_decode($msg);
    //    $mail->addStringEmbeddedImage($image,'logo_email_xs','logo_email_xs.png');
        $mail->WordWrap = 50;
        //LOG
        $log = new regSentemail($con,$row['user'], $row['email'], $assunto);
        if(!$mail->send()){
            echo 'Erro no envio! Mailer error: '.$mail->ErrorInfo;
            $log->registErro($mail->ErrorInfo);
        } else { 
            echo 'Mensagem enviada com sucesso.';
            $log->registOk();
            mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envsms,envemail,motivocontacto) "
                    . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),0,1,3)"
                    ,$row['id'],$row['user'],$row['id'],$row['user']));
        }      
        
    }
}