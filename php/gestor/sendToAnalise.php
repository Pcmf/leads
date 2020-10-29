<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../../class/sendEmail.php';
//$image = file_get_contents('../../img/logo_email_xs.png');
$json = file_get_contents("php://input");
$dt = json_decode($json);

//checks if all required documentation was checked

    $query0 = sprintf("SELECT count(*) FROM cad_docpedida WHERE recebido=0 AND lead=%s",$dt->lead);
    $result0 = mysqli_query($con,$query0);
    if($result0){
        //Atualizar cad_agenda como não ativo
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s  AND status=1",$dt->lead));
        //Atualiza o status do arq_leads
        $row0=mysqli_fetch_array($result0,MYSQLI_NUM);
        if($row0[0]>0){
            //atualiza LEAD status to 11
            mysqli_query($con,sprintf("UPDATE arq_leads SET status=11, datastatus=NOW() WHERE id=%s",$dt->lead)); 
            enviarEmailInfo($dt->lead, 11, $con);
            return;
        } else {
            //atulize LEAD status to 10
            mysqli_query($con,sprintf("UPDATE arq_leads SET status=10, datastatus=NOW() WHERE id=%s",$dt->lead));
            enviarEmailInfo($dt->lead, 10, $con);
            return;
        }
        //Desativa do cad_agendadoc
        mysqli_query("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s", $dt->lead);
        //Regista o contacto
//                mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
//                    . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
//                    $dt->lead, $dt->gestor->id,$dt->lead ,$dt->gestor->id,1,17));

    } else {
    return;    
}

// Função para enviar email a informar a recepção da documentação
/**
 * 
 * @param type $lead
 * @param type $sts
 * @param type $con
 */
function enviarEmailInfo($lead, $sts, $con) {
    $msg="";
    $query = sprintf("SELECT L.id,L.user,U.nome AS gestor,U.email AS gemail,U.telefone, P.nome, P.email"
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_agenda A ON A.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.id=%s ",$lead);

    $result = mysqli_query($con, $query);
    if($result){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
        
        //Toda documentação recebida e passou para analise
        if($sts==10){
            $assunto="Ref: ".$row['id']." - Estamos quase a obter o seu crédito!";

            $msg = "<p>Olá Sr(a) ".$row['nome'].",</p> "
                    . "<p>Agradeço desde já a atenção dispensada.</p>"
                        . "<p>Após verificação da documentação enviada por si, informamos que a mesma foi validade e encaminhada para os nossos analistas.</p>"
                        . "<p>Por favor aguarde por uma resposta da nossa parte. Tentaremos ser o mais breve possível e poderá também acompanhar o estado do seu processo na sua área de cliente em gestlifes.com. </p>"
                    ."<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>";
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
                $lista = "<ol>";
                foreach($listDocs AS $d){
                    $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span></li>";
                };
                $lista .="</ol>";

                $assunto="Ref: ".$row['id']." - Estamos quase a obter o seu crédito!";
                $msg = "<p>Olá Sr(a) ".$row['nome'].",</p> "
                        . "<p>Agradeço desde já a atenção dispensada.</p>"
                        . "<p>Após verificação da documentação enviada por si, informamos que a mesma foi validade e encaminhada para os nossos analistas."
                        . "<br/>Contudo, encontra-se em falta a seguinte documentação para podermos concluir o seu processo:</p>"
                        . "<p>".$lista."</p>"
                        . "<p>Use a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a>"
                        . " , o <a href='https://api.whatsapp.com/send?1=pt&phone=351".$row['telefone']."'>&#9758; WhatsApp</a>"
                       ." ou responda a este email, anexando a documentação pedida. </p>"
                        . "<p>Por favor aguardar por uma resposta da nossa parte.</p>"
                        . "<p>Também pode acompanhar o estado do seu processo, na sua área de cliente em www.gestlifes.com.</p>";                        
            }
        }


        //Enviar o email
        $result = new sendEmail($con, $row['user'], $row['gemail'], $row['email'],$assunto, $msg,"", 4, $row['id']);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        } 
        
    }
}