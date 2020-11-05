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

!isset($dt->outraInfo)?$dt->outraInfo ='':null;

if($dt->status != 23){
        //Atualiza outras informações no processo
    mysqli_query($con, sprintf("UPDATE arq_processo SET  outrainfo = CONCAT(outrainfo, '%s') WHERE lead=%s ", '. '.$dt->outraInfo, $dt->process->lead));
    //financiamento normal, 
    $query = sprintf("UPDATE cad_financiamentos SET datafinanciado=NOW(), status=%s,datastatus=NOW(), outrainfo = CONCAT(outrainfo, '%s') "
            . " WHERE lead=%s AND processo='%s' ",$dt->status,'. '.$dt->outraInfo, $dt->process->lead,$dt->process->processo);
    $result = mysqli_query($con,$query);
    if($result){
        $dt->status==7?$status=17:null;
        $dt->status==5?$status=19:null;
        $dt->status==9?$status=20:null;
        $dt->status==10?$status=18:null;
        $query1 = sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s", $status, $dt->process->lead);
        $result1 = mysqli_query($con,$query1);
        if($result1){
            if($dt->status ==5){
                insertRejecoes($dt->process, $dt->opRej, $con);
            }
            if($status == 17){
                       
                $texto = "</p>Informamos que o seu processo se encontra financiado.</br>Agradecemos toda a confiança e atenção dispensada. Ficando desde já ao seu dispor.</p>";

                sendEmailToClient($dt->process->lead, $texto, $con);
            }
            echo 'Ok';
        } else {
            echo $query1;
        }
    } else {
       echo $query; 
    }
} else {
    //Para quando fica a aguardar comprovativos de pagamento
        //financiamento normal, 
        mysqli_query($con, sprintf("UPDATE cad_financiamentos SET datafinanciado=NOW(), status=7, datastatus=NOW()  "
            . " WHERE lead=%s AND processo='%s' ", $dt->process->lead, $dt->process->processo));

        //inserir os comprovativos pedidos
        $linha =1;
        $comprovativos ="<ul>";
        forEach( $dt->cpag->comp AS $cp){
            mysqli_query($con, sprintf("INSERT INTO cad_comprovativos(lead, linha, instituicao, tipo, montante, datapedido,status) VALUES(%s,%s,'%s','%s',%s,NOW(), 0) "
                    , $dt->process->lead, $linha, $cp->instituicao, $cp->tipo, $cp->montante));
            $comprovativos .= "<li>".$cp->instituicao."  - ". $cp->tipo." - ".$cp->montante."</li>";
            $linha++;
        }
        $comprovativos .="</ul>";
        $query1 = sprintf("UPDATE arq_leads SET status=23, datastatus=NOW() WHERE id=%s", $dt->process->lead);
        $result1 = mysqli_query($con,$query1);
        if($result1){
            $texto = "<p>Informamos que o seu processo se encontra financiado, contudo para a sua conclusão é necessário,"
                    . " o envio dos comprovativos de liquidação dos seguintes créditos:</p>".$comprovativos;
            sendEmailToClient($dt->process->lead, $texto, $con);
            echo 'Ok';
        } else {
            echo $query1;
        }

}


function insertRejecoes($p,$op, $con){
    !isset($op->motivo) ? $op->motivo ='':null;
    !isset($op->obs) ? $op->obs ='':null;
    
    mysqli_query($con, sprintf("INSERT INTO cad_rejeicoes(lead, motivo, outro, obs) VALUES(%s,'%s', '%s','%s') ", $p->lead, $op->motivoTipo, $op->motivo, $op->obs));
    
    //Enviar o email com a recusa
    //Enviar o email para o cliente
    //Obter os dados do cliente e do analista
    $query0 = sprintf("SELECT L.analista AS analistaId, P.nome,P.email,U.nome AS analista, U.email AS aemail,U.telefone"
            . " FROM arq_leads L INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
            . " WHERE L.id=%s", $p->lead);
    
    $result0 = mysqli_query($con,$query0);
    if($result0){
        $row = mysqli_fetch_array($result0,MYSQLI_ASSOC);
        
        //Prepara para criar e enviar email
        $assunto = "Ref: ".$p->lead." - Anulação da proposta.";
        $msg = "<p>Exmo(a). Sr(a) ".$row['nome']."</p>"
               . "<p>Informamos que a proposta de simulação de Crédito solicitada por si. Foi cancelada pelo(s) motivo(s) seguinte(s): </p>"
                . "<ul><li>".$op->motivoTipo."</li><li>".$op->motivo."</li></ul>"
            ."<p>Agradecendo a sua confiança e ficando à sua disposição para qualquer estudo posterior.</p>";

        
        
                //Enviar email
        $result = new sendEmail($con,$row['analistaId'],  $row['aemail'], $row['email'], $assunto, $msg,"",7,$p->lead);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }         
        
    }
    return;
}


function sendEmailToClient($lead, $texto, $con) {
        //Obter os dados do cliente e do analista
    $query0 = sprintf("SELECT L.analista AS analistaId, P.nome,P.email,U.nome AS analista, U.email AS aemail, U.telefone"
            . " FROM arq_leads L INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
            . " WHERE L.id=%s", $lead);
    
    $result0 = mysqli_query($con,$query0);
    if($result0){
        $row = mysqli_fetch_array($result0,MYSQLI_ASSOC);
    
           //Prepara para criar e enviar email
        $assunto = "Ref: ".$lead." - Informação sobre situação do pedido de financiamento.";
        $msg = "<p>Exmo(a). Sr(a) ".$row['nome']."</p>"
                .$texto;
                
                
                //Enviar email
        $result = new sendEmail($con,$row['analistaId'],  $row['aemail'], $row['email'], $assunto, $msg,"",4,$lead);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        } 
        
    }
    return;
}
