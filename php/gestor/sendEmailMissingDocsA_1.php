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


//Desativar no cad_agendadoc caso exista
mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s", $dt->lead->id));


// Verificar os titulares
if(isset($dt->lead->segundoproponente)){
    $segundoproponente = $dt->lead->segundoproponente;
} else {
    $segundoproponente = 0;
}
$docs1 = [];
$docs2 = [];
if (isset($dt->docFalta)) {
    $docs1 = $dt->docFalta->docs1;
    $docs2 = $dt->docFalta->docs2;
}

// tipo: 1 - selecionada, 2 - em falta, 3 - selecionada e em falta
// verificar se a documentação pedida já está na lista senão acrescenta
if($dt->tipo == 1 || $dt->tipo == 3) {
    $line = getMaxLine($con, $dt->lead->id);
    foreach ($docs1 as $doc) {
            //verificar se o documento já está pedido
            $result = mysqli_query($con, sprintf("SELECT count(*) AS doc FROM cad_docpedida WHERE lead=%s AND tipodoc=%s",
                    $dt->lead->id, $doc->id));
            if($result) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                //se não estiver insere
                if($row['doc']<=0) {
                    $query = sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc,recebido) "
                            . " VALUES(%s,%s,%s,0)"
                            , $dt->lead->id, $line, $doc->id);
                    mysqli_query($con, $query);
                    $line++;
                }
            }
    }
    if ($segundoproponente) {
            foreach ($docs2 as $doc) {
            //verificar se o documento já está pedido
            $result = mysqli_query($con, sprintf("SELECT count(*) AS doc FROM cad_docpedida WHERE lead=%s AND tipodoc=%s",
                    $dt->lead->id, $doc->id));
            if($result) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                //se não estiver insere
                if($row['doc']<=0) {
                    $query = sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc,recebido) "
                            . " VALUES(%s,%s,%s,0)"
                            , $dt->lead->id, $line, $doc->id);
                    mysqli_query($con, $query);
                    $line++;
                }
            }
    }
    }
}


//Obter a lista de documentação em falta
$docFalta = array();
$query0 = sprintf("SELECT N.* FROM cad_docpedida P INNER JOIN cnf_docnecessaria N ON N.id=P.tipodoc "
        . " WHERE P.lead=%s AND P.recebido=0 ", $dt->lead->id);
$result0 = mysqli_query($con, $query0);
if ($result0) {
    while ($row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
        array_push($docFalta, $row0);
    }
}

//Obter a dados do cliente 
$query0 = sprintf("SELECT P.nome, P.email,L.status FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id WHERE L.id=%s ", $dt->lead->id);
$result0 = mysqli_query($con, $query0);
if ($result0) {
    $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);

    //se o array nao estiver vazio envia email 
    $assunto = "ID: " . $dt->lead->id . " - O Seu Crédito Aqui! ";
    $emailDestino = $row0['email'];
    $nomeCliente = $row0['nome'];
    $emailOrigem = $dt->user->email;
    $nomeGestor = $dt->user->nome;

    $lista = "<ol>";
    foreach ($docFalta AS $d) {
        if ($d['nomedoc'] != 'Diversos') {
            $lista .= "<li> - " . $d['nomedoc'] . ". <span>" . $d['descricao'] . "</span>";
        if ($d['link']) {
                $lista .= " (<a href='" . $d['link'] . "' target='_blank'>obter aqui</a>)";
            }
            $lista .= "</li>";
        }
        if (isset($dt->outroDoc) && $dt->outroDoc) {
            $lista .= "<li><u>Diversos: " . $dt->outroDoc . ".</u></li>";
        }
    }
    $lista .= "</ol>";


    $simulacao = '';
    // Outras simulações
    $resultSim = mysqli_query($con, sprintf("SELECT * FROM cad_simula WHERE lead =%s ORDER BY data DESC LIMIT 1", $dt->lead->id));
    if ($resultSim) {
        $ln = 1;
        $line = mysqli_fetch_array($resultSim, MYSQLI_ASSOC);
        $simulacao .= "<p><strong>Valor de simulação " . $ln . " ) </strong> &nbsp;&nbsp;&nbsp;<em>"
                    . "Valor pretendido: <strong>" . $line['valorpretendido'] . " &euro;</strong></em>"
                    . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prazo: <strong>" . $line['prazopretendido'] . " Meses</strong></em>"
                    . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prestação: <strong>" . $line['prestacaopretendida'] . " &euro;</strong></em></p>";
        // Outras simulações
        $resultSim = mysqli_query($con, sprintf("SELECT * FROM cad_simulag WHERE lead =%s", $dt->lead->id));
        if ($resultSim) {
            $ln++;
            while ($line = mysqli_fetch_array($resultSim, MYSQLI_ASSOC)) {
                    $simulacao .= "<p><h5><strong>Valor de simulação ".$ln.")</strong> &nbsp;&nbsp;&nbsp;<em>Valor pretendido: <strong>" . $line['valor'] . " Euros</strong></em>"
                . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prazo: <strong>" . $line['prazo'] . " Meses</strong></em>"
                . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prestação: <strong>" . $line['prestacao'] . " Euros</strong></em></h5></p>";

                $ln++;
            }
        }
        if ($simulacao != ''){
        $simulacao .= " <p><small><em>(Informamos que estes valores são meramente indicativos e correspondem  a um valor médio de simulação)</em></small></p>"
                . "</br></br>";
        }
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
    $result = new sendEmail($con, $dt->user->id, $emailOrigem, $emailDestino, $assunto, $msg, "", 10, $dt->lead->id);
    if ($result) {
        //atualizar o status da LEAD para 8 por defeito
        $sts = 8;

        if ($dt->user->tipo == 'Analista' || $row0['status'] == 21) {
            $sts = 21;
        } elseif ($dt->user->tipo == 'GRec') {
            $sts = 108;
        } else {
            //Obter o status atual da lead
//            $res = mysqli_query($con, sprintf("SELECT status FROM arq_leads WHERE id=%s", $dt->lead->id));
//            if ($res) {
//                $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
//                if ($row['status'] == 36 || $row['status'] == 38) {
//                    $sts = 38;
//                }
//            }
        }
        //Limpar a agenda
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status = 0 WHERE lead=%s AND status=1", $dt->lead->id));
        //regista na agenda para o dia seguinte
        mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, agendaperiodo, tipoagenda, status) "
                        . " VALUES(%s, %s, (CURDATE() + INTERVAL 1 DAY),  CURTIME(), 1 , 3, 1)", $dt->lead->id, $dt->user->id));
        mysqli_query($con, sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s", $sts, $dt->lead->id));

        echo "Mensagem enviada com sucesso!";
    } else {
        echo "Erro no envio do email. Por favor contacte suporte!";
    }
}


// Functions
function getMaxLine($con, $lead) {
$result0 = mysqli_query($con, sprintf("SELECT MAX(linha)+1 FROM cad_docpedida WHERE lead=%s", $lead));
    if ($result0) {
        $row = mysqli_fetch_array($result0, MYSQLI_NUM);
        $linha = $row[0];
        if ($linha) {
            return $linha;
        }
        return 1;
    }
}
