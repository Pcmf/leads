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

//Obter a lista de documentação em falta
$docFalta = array();
$query0 = sprintf("SELECT N.nomedoc,N.descricao FROM cad_docpedida P INNER JOIN cnf_docnecessaria N ON N.id=P.tipodoc "
        . " WHERE P.lead=%s AND P.recebido=0 ", $dt->lead);
$result0=mysqli_query($con,$query0);
if($result0){
    while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
        array_push($docFalta, $row0);
    }
}
//se o array nao estiver vazio envia email 
if(sizeof($docFalta)>0){
    $assunto ="Ref:".$dt->lead." - Documentação necessária para obter o seu crédito.";
    $emailDestino = $dt->cliente->email;
    $nomeCliente = $dt->cliente->nome;
    $emailOrigem = $dt->gestor->email;
    $nomeGestor = $dt->gestor->nome;
    
    $lista = "<ul>";
    foreach($docFalta AS $d){
        $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span></li>";
    }
    $lista .="</ul>";
    
                
        $simulacao ='';
        // Outras simulações
        $resultSim = mysqli_query($con, sprintf("SELECT * FROM cad_simulag WHERE lead =%s", $dt->lead));
        if ($resultSim) {
            $ln = 1;
            while ($line = mysqli_fetch_array($resultSim, MYSQLI_ASSOC)) {
                    $simulacao .= "<p><h5><strong>Valor de simulação ".$ln.")</strong> &nbsp;&nbsp;&nbsp;<em>Valor pretendido: <strong>" . $line['valor'] . " Euros</strong></em>"
                . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prazo: <strong>" . $line['prazo'] . " Meses</strong></em>"
                . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prestação: <strong>" . $line['prestacao'] . " Euros</strong></em></h5></p>";

                $ln++;
            }
        
        $simulacao .= " <p><small><em>(Informamos que estes valores são meramente indicativos e correspondem  a um valor médio de simulação)</em></small></p>"
            . "</br></br>";
        }

        $msg = "<p>Olá Sr.(a) ".$nomeCliente."</p>"
                . "<p>Antes de mais agradeço a disponibilidade e a confiança na nossa empresa.</p>"
                
                . "<p>Para podermos dar seguinte ao seu pedido, junto enviamos a simulação e a documentação necessária. </p>"
                . $simulacao
                . "<p>Documentação a enviar, para obter a sua aprovação:</p>"
                . " ".$lista." <br/><br/>"
                ."<p>Caso pretenda ajuda a organizar a sua documentação e poupar o seu tempo,"
                ." <strong> basta facultar a sua senha das finanças e podemos faze-lo por si</strong>,"
                ." deste modo obtemos os seguintes documentos:</p>"
                ."<p>- Mapa de Responsabilidades de Crédito do Banco de Portugal, último IRS entregue e o comprovativo de morada.</p>" 
                ."<p>Assim, só terá que nos fazer chegar a restante documentação solicitada.</p>"
                
            . "<p>Use a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a>, "
                . " o <a href='https://api.whatsapp.com/send?1=pt&phone=351".$dt->user->telefone."'>&#9758; WhatsApp</a>"
                . " ou responda a este email anexando a documentação pedida.</p>"
            ."<p>Ao usar a <a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a> para anexar a documentação estará a dar mais rapidez ao seu processo! </p>";                 
           
        
    
        //Enviar o email
        $result = new sendEmail($con, $dt->gestor->id, $emailOrigem, $emailDestino, $assunto, $msg,"", 10, $dt->lead);        
        if($result){
                //atualizar o status da LEAD para 8
                mysqli_query($con,sprintf("UPDATE arq_leads SET status=8, datastatus=NOW() WHERE id=$dt->lead"));
                //atualizar o status e data expectavel 
                mysqli_query($con,sprintf("UPDATE cad_agenda SET agendadata=DATE_ADD(CURDATE(), INTERVAL 5 DAY) WHERE lead=%s",$dt->lead));

                //  Registar no contacto
//                mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
//                    . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
//                    $dt->lead,$dt->gestor->id,$dt->lead ,$dt->gestor->id,1,13));

                echo 'Mensagem enviada com sucesso.';
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        } 
}
