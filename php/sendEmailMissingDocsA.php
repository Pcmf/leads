<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';
require_once '../class/sendEmail.php';
$json= file_get_contents("php://input");

$dt= json_decode($json);
//Desativar no cad_agendadoc caso exista
mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s" , $dt->lead));

/**
 * Inserir ou atualizar no cad_docpedida
 */
//Obter linha

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
    foreach ($dt->docFalta as $doc){

        $query = sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc,recebido) "
                . " VALUES(%s,%s,%s,0)"
            ,$dt->lead,$ln,$doc->id);
        mysqli_query($con,$query);
        $ln++;
    } 
}

//Obter a dados do cliente 
$query0 = sprintf("SELECT nome, email, status FROM arq_leads  WHERE id=%s ", $dt->lead);
$result0=mysqli_query($con,$query0);
if($result0){
        $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);

    //se o array nao estiver vazio envia email 
        $assunto = "ID: ".$dt->lead.". O Seu Crédito Aqui!";
        $emailDestino = $row0['email'];
        $nomeCliente = $row0['nome'];
        $emailOrigem = $dt->user->email;
        $nomeGestor = $dt->user->nome;
        
        // Obter a lista de documentação em falta
        $lista = "<ol>";
        foreach($dt->docFalta AS $d){
            if($d->nomedoc!='Diversos'){
                $lista .="<li> - ".$d->nomedoc.". <span>".$d->descricao."</span>";
                if ($d->link) {
                    $lista .=" (<a href='".$d->link."' target='_blank'>obter aqui</a>)";
                }
                $lista .="</li>";
            }
        }
        if($dt->outroDoc){
            $lista .="<li> - Diversos: ".$dt->outroDoc."</li>";
        }
        $lista .="</ol>";
        
        // Obter simulação
            $simulacao ='';
            $result = mysqli_query($con, sprintf("SELECT * FROM cad_simula WHERE lead=%s LIMIT 1", $dt->lead));
            if($result) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $simulacao = "Valor: ".$row['valorpretendido']."&euro;, Prazo ".$row['prazopretendido']." Meses, Prestação: ".$row['prestacaopretendida']."&euro; / mês";
            }
        
        //Criar a mensagem
        $msg = "<p>Olá ".$nomeCliente."!</p>"
                . "<p>Está a um passo de obter crédito para a sua simulação!</p>"
                . "<p>".$simulacao."</p>"
                . "<p>Não se esqueça que este valor é meramente indicativo e corresponde a um valor médio de simulação!</p>"
                . "<p>Pode submeter os seus documentos através da sua <a href='https://gestlifes.com/GestLifesAC'>Área de Cliente</a></p>"
                . "<p> ".$lista." </p>"
                ."<p>Pode também fazer-nos chegar através do "
                . "<a href='https://api.whatsapp.com/send?1=pt&phone=351".$dt->user->telefone."'>WhatsApp</a>"
                . " ou em resposta a este e-mail."
                ."<p>Ficamos a aguardar a sua resposta!</p>"
                . "<p>Até já!</p>";                     


        //Enviar o email
        $result = new sendEmail($con, $dt->user->id, $emailOrigem, $emailDestino,$assunto, $msg,"", 10, $dt->lead);
        if($result){
            //atualizar o status da LEAD para 8
            $sts=8; 
            if($dt->user->tipo =='Analista' || $row0['status']==21){
                $sts =21;
            } else {
                //Obter o status atual da lead
                $res = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s", $dt->lead));
                if($res){
                    $row= mysqli_fetch_array($res, MYSQLI_ASSOC);
                    if($row['status']==36 || $row['status']==38){
                        $sts = 38;
                    } elseif ($row['status']==39) {
                        $sts = 8;
                        //Limpar a agenda
                        mysqli_query($con, sprintf("UPDATE cad_agenda SET status = 0 WHERE lead=%s AND status=1", $dt->lead));
                        //regista na agenda para o dia seguinte
                        mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, agendaperiodo, tipoagenda, status) "
                                . " VALUES(%s, %s, (CURDATE() + INTERVAL 1 DAY),  CURTIME(), 1 , 3, 1)" , $dt->lead, $dt->user->id));
                        //Verificar se tem processo criado, se não tiver cria
                        $result = mysqli_query($con, sprintf("SELECT count(*) FROM arq_processo WHERE lead=%s ", $dt->lead));
                        if($result){
                            $rc = mysqli_fetch_array($result, MYSQLI_NUM);
                            if($rc[0] == 0){
                                    $result00 = mysqli_query($con, sprintf("INSERT INTO arq_processo(lead, user, nome, nif, email, telefone, idade, vencimento, valorpretendido) "
                                    . " VALUES(%s, %s, '%s', '%s', '%s', '%s', %s, %s, %s) ",
                                    $dt->lead, $dt->user->id, $row['nome'], $row['nif'], $row['email'], $row['telefone'], $row['idade'], $row['rendimento1'], $row['montante']));
                                    if(!$result00) {
                                        mysqli_query($con, sprintf("UPDATE arq_processo SET  user=%s, nome='%s', nif='%s', email='%s', telefone='%s', idade=%s,"
                                                . " vencimento=%s, valorpretendido=%s WHERE lead=%s",
                                                 $dt->user->id, $row['nome'], $row['nif'], $row['email'], $row['telefone'], $row['idade'], $row['rendimento1'], $row['montante'], $dt->lead));
                                    }
                            }
                        }
                    }
                }
            }
            mysqli_query($con,sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s",$sts,$dt->lead));
            //
            

            echo 'Mensagem enviada com sucesso.';            
        } else {
            echo 'Erro no envio do email. Por favor contacte o suporte!';
        }

  
}
