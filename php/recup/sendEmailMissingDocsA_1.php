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

//Desativar no cad_agendadoc caso exista
mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s" , $dt->lead));

/**
 * Inserir ou atualizar no cad_docpedida
 */
//Obter linha
if(isset($dt->docFalta)){
    $result0 = mysqli_query($con, sprintf("SELECT MAX(linha)+1 FROM cad_docpedida WHERE lead=%s",$dt->lead));
    if($result0){
        $row = mysqli_fetch_array($result0,MYSQLI_NUM);
        $linha= $row[0];
        if($linha){
        //Inserir  os novos documentos anexados
        $ln = $linha;
        } else {
        $ln=1;    
        }
        foreach($dt->docFalta as $doc){

            $query = sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc,recebido) "
                    . " VALUES(%s,%s,%s,0)"
                ,$dt->lead,$ln,$doc->id);
            mysqli_query($con,$query);
            $ln++;
        } 
    }
}
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

//Obter a dados do cliente 
$query0 = sprintf("SELECT P.nome, P.email,L.status FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id WHERE L.id=%s ", $dt->lead);
$result0=mysqli_query($con,$query0);
if($result0){
        $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);
        

    //se o array nao estiver vazio envia email 
        $assunto = "Ref: ".$dt->lead." - Documentação necessária para obter o seu crédito.";
        $emailDestino = $row0['email'];
        $nomeCliente = $row0['nome'];
        $emailOrigem = $dt->user->email;
        $nomeGestor = $dt->user->nome;

        $lista = "<ol>";
        foreach($docFalta AS $d){
            if($d['nomedoc']!='Diversos'){
                $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span></li>";
            }
        }
        if($dt->outroDoc){
            $lista .="<li><u>Diversos: ".$dt->outroDoc.".</u></li>";
        }
        $lista .="</ol>";
        
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
        $result = new sendEmail($con, $dt->user->id, $emailOrigem, $emailDestino, $assunto, $msg,"", 10, $dt->lead);        
        if($result){
            //atualizar o status da LEAD para 8 por defeito
            $sts=8; 
            
            if($dt->user->tipo =='Analista' || $row0['status']==21){
                $sts =21;
            } elseif ($dt->user->tipo =='GRec') {
                $sts = 108;
            } else {
                //Obter o status atual da lead
                $res = mysqli_query($con, sprintf("SELECT status FROM arq_leads WHERE id=%s", $dt->lead));
                if($res){
                    $row= mysqli_fetch_array($res, MYSQLI_ASSOC);
                    if($row['status']==36 || $row['status']==38){
                        $sts = 38;
                    }
                }
            }
            //Limpar a agenda
            mysqli_query($con, sprintf("UPDATE cad_agenda SET status = 0 WHERE lead=%s AND status=1", $dt->lead));
            //regista na agenda para o dia seguinte
            mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, agendaperiodo, tipoagenda, status) "
                                . " VALUES(%s, %s, (CURDATE() + INTERVAL 1 DAY),  CURTIME(), 1 , 3, 1)" , $dt->lead, $dt->user->id));
            mysqli_query($con,sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s",$sts,$dt->lead));

            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        } 
}
