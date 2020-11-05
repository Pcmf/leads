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


$query = sprintf("UPDATE cad_financiamentos SET dtcontratoparceiro=NOW() "
        . " WHERE lead=%s AND processo='%s' ", $dt->process->lead,$dt->process->processo);
$result = mysqli_query($con,$query);
sendEmailToClient($dt->process->lead, $con);
if($result){

    echo 'OK';
} else {
   echo $query; 
}



function sendEmailToClient($lead, $con) {
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
               . "<p>Rececionamos o seu contrato de crédito e o mesmo será reencaminhado para o parceiro (financeira) no decorrer do dia de hoje.</br>"
               ." Mais informamos que poderá ser contactado pela financeira, afim de confirmar os seus dados para conclusão do seu financiamento.</p>"
             ."<p>Com os melhores cumprimentos</p>";
        
                       //Enviar email
        $result = new sendEmail($con,$row['analistaId'],  $row['aemail'], $row['email'], $assunto, $msg,"",4,$lead);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        } 
        
    }
}