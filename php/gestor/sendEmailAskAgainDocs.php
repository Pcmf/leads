<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once '../../class/sendEmail.php';

$json= file_get_contents("php://input");

$dt= json_decode($json);

$lista = "<ul>";
//atualização da BD
foreach ($dt->docs AS $d){
    //verificar se já foi pedido e ainda não foi recebido. Nesse caso não regista. Caso contrario cria um novo pedido
    $result = mysqli_query($con, sprintf("SELECT count(*) FROM cad_docpedida "
            . "WHERE lead=%s AND tipodoc=%s AND recebido=0",$dt->lead,$dt->id));
    if($result){
        $row= mysqli_fetch_array($result,MYSQLI_NUM);
        if($row[0]==0){
            //faz registo de pedido
            $query =sprintf("INSERT INTO cad_docpedida(lead,linha,numpedido,tipodoc) "
                    . " VALUES(%s,(SELECT MAX(A.linha)+1 FROM cad_docpedida A WHERE A.lead=%s),1,%s) " ,
                    $dt->lead,$dt->lead,$d->id);
            mysqli_query($con,$query);
        }
    }
    $lista .="<li>".$d->nomedoc.". ".$d->descricao."</li>";
    if($dt->otd!=""){
        $lista .="<li>".$dt->otd."</li>";
    }
}
$lista .="</ul>";

//Aceder ao dados do cliente
$result0 = mysqli_query($con, sprintf("SELECT nome,email FROM arq_processo WHERE lead=%s",$dt->lead));
if($result0){
    $row0= mysqli_fetch_array($result0,MYSQLI_ASSOC);
}
$assunto = "GESTLIFES Ref: ".$dt->lead." - Documentação necessária para obter o seu crédito!";

        $msg = "<p>Olá Sr.(a) ".$nomeCliente."</p>"
                . "<p>Antes de mais agradeço a disponibilidade e a confiança na nossa empresa.</p>"
                
                . "<p>Para podermos dar seguinte ao seu pedido, junto enviamos a simulação e a documentação necessária. </p>"
                . "<p>Documentação a enviar, para obter a sua aprovação:</p>"
                . " ".$lista." <br/><br/>"
                ."<p>Caso pretenda ajuda a organizar a sua documentação e poupar o seu tempo,"
                ." <strong> basta facultar a sua senha das finanças e podemos faze-lo por si</strong>,"
                ." deste modo obtemos os seguintes documentos:</p>"
                ."<p>- Mapa de Responsabilidades de Crédito do Banco de Portugal, último IRS entregue e o comprovativo de morada.</p>" 
                ."<p>Assim, só terá que nos fazer chegar a restante documentação solicitada.</p>"
                
            . "<p>Use a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a>, "
                . " o <a href='https://api.whatsapp.com/send?1=pt&phone=351".$dt->gestor->telefone."'>&#9758; WhatsApp</a>"
                . " ou responda a este email anexando a documentação pedida.</p>"
            ."<p>Ao usar a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a> para anexar a documentação estará a dar mais rapidez ao seu processo! </p>";                 
           
        


//Enviar o email
$result = new sendEmail($con, $dt->gestor->id, $dt->gestor->email, $row0['email'], $assunto, $msg,"", 10, $dt->lead);        
if($result){
        //atualizar o status da LEAD para 8
        mysqli_query($con,sprintf("UPDATE arq_leads SET status=8, datastatus=NOW() WHERE id=%s",$dt->lead));
        //atualizar o status e data expectavel 
        mysqli_query($con,sprintf("UPDATE cad_agenda SET agendadata=DATE_ADD(CURDATE(), INTERVAL 1 DAY) WHERE lead=%s",$dt->lead));

        echo 'Mensagem enviada com sucesso.';
} else {
    echo "Erro no envio do email. Por favor contacte suporte!";
} 
        
        
    




