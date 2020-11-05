<?php

date_default_timezone_set('Europe/Lisbon');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//require_once '../../sisleadsrest/db/DB.php';
require_once '../openCon.php';
require_once '../passwordHash.php';
require_once '../class.postRgpd.php';
require_once '../../class/sendEmail.php';
include_once '../PasswordGenerator.php';
require_once '../../restful/pushNotificationFunction.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);

$p = $dt->process;
$docs = $dt->docs;
$userId = $dt->user->id;
$a = $dt->address;

isset($a->incSimula) && !$a->incSimula ? $incluirSimulacao = false : $incluirSimulacao = true;

//Registar o contacto
$query0 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,motivocontacto) "
        . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s)", $p->id, $userId, $p->id, $userId, 2);
$result0 = mysqli_query($con, $query0);
if ($result0) {
    if (!isset($p->vencimento) || !$p->vencimento) {
        $p->vencimento = 0;
    }
    if (!isset($a->moradarua)) {
        $a->moradarua = '';
    }
    if (!isset($a->moradalocalidade)) {
        $a->moradalocalidade = '';
    }
    if (!isset($a->moradacp)) {
        $a->moradacp = '';
    }
    if (!isset($p->filhos) || !$p->filhos) {
        $p->filhos = 0;
    }
    if (!isset($p->valorhabitacao) || !$p->valorhabitacao) {
        $p->valorhabitacao = 0;
    }
    if (!isset($p->declarada)) {
        $p->declarada = '';
    }
    if (!isset($p->prazopretendido) || !$p->prazopretendido) {
        $p->prazopretendido = 0;
    }
    if (!isset($p->valorprestacao) || !$p->valorprestacao) {
        $p->valorprestacao = 0;
    }
    if (!isset($p->outrainfo)) {
        $p->outrainfo = '';
    }
    if (!isset($p->finalidade)) {
        $p->finalidade = '';
    }
    if (!isset($p->segprop) || !$p->segprop) {
        $p->segprop = 0;
    }
    if (!isset($p->profissao2)) {
        $p->profissao2 = 'NULL';
    }
    if (!isset($p->idade2)) {
        $p->idade2 = 'NULL';
    }
    if (!isset($p->vencimento2)) {
        $p->vencimento2 = 'NULL';
    }
    !isset($p->tipocontrato2) ? $p->tipocontrato2 = 'NULL' : null;
    if (!isset($p->anoinicio2)) {
        $p->anoinicio2 = 'NULL';
    }

    if (!isset($p->telefone2)) {
        $p->telefone2 = 'NULL';
    }
    if (!isset($p->nif2)) {
        $p->nif2 = 'NULL';
    }
    // !isset($line->prestacao) ? $line->prestacao =0 : null;

    if (!isset($p->valorhabitacao2) || !$p->valorhabitacao2) {
        $p->valorhabitacao2 = 0;
    }
    if (!isset($p->anoiniciohabitacao2) || !$p->anoiniciohabitacao2) {
        $p->anoiniciohabitacao2 = 1900;
    }
    if (!isset($p->declarada2)) {
        $p->declarada2 = '';
    }
    !isset($p->parentesco2) ?  $p->parentesco2 = '' :null;
    !isset($p->tipohabitacao2) ? $p->tipohabitacao2 = 'NULL' : null;
    if (!isset($p->mesmahabitacao)) {
        $p->mesmahabitacao = '';
    }
    if (isset($p->mesmahabitacao) && $p->mesmahabitacao == 'Sim') {
        $p->tipohabitacao2 = 'NULL';
        $p->declarada2 = '';
        $p->anoiniciohabitacao2 = 'NULL';
        $p->valorhabitacao2 = 0;
    }
    if (!isset($p->tipocredito) || !$p->tipocredito) {
        $p->tipocredito = 'CP';
    }
    !isset($p->mesinicio) || !$p->mesinicio ? $p->mesinicio = 1 : null;
    !isset($p->mesinicio2) || !$p->mesinicio2 ? $p->mesinicio2 = 1 : null;
    !isset($p->diaprestacao) || !$p->diaprestacao ? $p->diaprestacao = 1 : null;

    //Save process
    $query = sprintf("INSERT INTO arq_processo(lead,user,nome,nif,email,telefone,idade,profissao,vencimento,tipocontrato,anoinicio,"
            . " estadocivil,filhos,parentesco2,telefone2,nif2,idade2,profissao2,vencimento2,tipocontrato2,anoinicio2,irs,tipohabitacao,"
            . " valorhabitacao,declarada,anoiniciohabitacao,"
            . " tipohabitacao2,valorhabitacao2,declarada2,anoiniciohabitacao2,mesmahabitacao,"
            . " valorpretendido,prazopretendido,prestacaopretendida,finalidade,outrainfo,moradarua,moradalocalidade,moradacp,"
            . "tipoenviodoc,datainicio,tipocredito, mesinicio, mesinicio2, segundoproponente, diaprestacao ) "
            . " VALUES(%s,%s,'%s',%s,'%s','%s',%s,'%s',%s,%s,%s,%s,%s,'%s','%s',%s,%s,'%s',%s,%s,%s,'%s',%s,%s,'%s',%s,"
            . " %s,%s,'%s',%s,'%s',%s,%s,%s,'%s','%s','%s','%s','%s','%s',NOW(),'%s', %s, %s, %s, %s)",
            $p->id, $userId, ucwords(mb_strtolower($p->nome, 'UTF-8')), $p->nif, $p->email, $p->telefone, $p->idade, $p->profissao, $p->vencimento, $p->tipocontrato,
            $p->anoinicio, $p->estadocivil, $p->filhos, $p->parentesco2, $p->telefone2, $p->nif2, $p->idade2, $p->profissao2, $p->vencimento2,
            $p->tipocontrato2, $p->anoinicio2, $p->irs, $p->tipohabitacao, $p->valorhabitacao, $p->declarada, $p->anoiniciohabitacao, $p->tipohabitacao2,
            $p->valorhabitacao2, $p->declarada2, $p->anoiniciohabitacao2, $p->mesmahabitacao, $p->montante, $p->prazopretendido, $p->valorprestacao,
            $p->finalidade, $p->outrainfo, $a->moradarua, $a->moradalocalidade, $a->moradacp, $dt->tipoEnv, $p->tipocredito, $p->mesinicio,
            $p->mesinicio2, $p->segprop, $p->diaprestacao);

        $result = mysqli_query($con, $query);
        
    if(mysqli_affected_rows($con) <= 0){
       // UPDATE process
        $query = sprintf("UPDATE arq_processo SET user=%s, nome='%s', nif='%s', email='%s', telefone='%s', idade=%s, profissao='%s', vencimento=%s,"
            . " tipocontrato=%s, anoinicio=%s, estadocivil=%s, filhos=%s, parentesco2='%s', telefone2='%s', nif2='%s', idade2=%s, profissao2='%s',"
            . " vencimento2=%s, tipocontrato2=%s, anoinicio2=%s, irs='%s', tipohabitacao=%s, valorhabitacao=%s, declarada='%s', anoiniciohabitacao=%s,"
            . " tipohabitacao2=%s, valorhabitacao2=%s, declarada2='%s', anoiniciohabitacao2=%s, mesmahabitacao='%s', valorpretendido=%s, prazopretendido=%s,"
            . " prestacaopretendida=%s, finalidade='%s', outrainfo='%s', moradarua='%s', moradalocalidade='%s', moradacp='%s', tipoenviodoc='%s', tipocredito='%s',"
             . "  mesinicio=%s, mesinicio2=%s, segundoproponente=%s, diaprestacao=%s "
            . " WHERE lead=%s ",
             $userId, ucwords(mb_strtolower($p->nome, 'UTF-8')), $p->nif, $p->email, $p->telefone, $p->idade, $p->profissao, $p->vencimento, 
            $p->tipocontrato, $p->anoinicio, $p->estadocivil, $p->filhos, $p->parentesco2, $p->telefone2, $p->nif2, $p->idade2, $p->profissao2, 
            $p->vencimento2, $p->tipocontrato2, $p->anoinicio2, $p->irs, $p->tipohabitacao, $p->valorhabitacao, $p->declarada, $p->anoiniciohabitacao,
            $p->tipohabitacao2, $p->valorhabitacao2, $p->declarada2, $p->anoiniciohabitacao2, $p->mesmahabitacao, $p->montante, $p->prazopretendido,
            $p->valorprestacao, $p->finalidade, $p->outrainfo, $a->moradarua, $a->moradalocalidade, $a->moradacp, $dt->tipoEnv, $p->tipocredito, 
            $p->mesinicio, $p->mesinicio2, $p->segprop, $p->diaprestacao, $p->id);   
         //   echo $query;
        $result = mysqli_query($con, $query);
    }



    if ($result) {


        //Insere Outros rendimentos
        if (isset($p->or)) {
            if (sizeof($p->or) > 0) {
                $ln = 1;
                foreach ($p->or as $line) {
                    if ($line->valorrendimento > 0) {
                        !isset($line->periocidade) ? $line->periocidade='Mes' : null;
                        $queryOR = sprintf("INSERT INTO cad_outrosrendimentos(lead,linha,tiporendimento,valorrendimento,periocidade) "
                                . " VALUES(%s,%s,'%s',%s,'%s')", $p->id, $ln, $line->tiporendimento, $line->valorrendimento, $line->periocidade);
                        mysqli_query($con, $queryOR);
                        $ln++;
                    }
                }
            }
        }
        //Insere Outros Creditos
        if (isset($p->oc)) {
            if (sizeof($p->oc) > 0) {
                $ln = 1;
                foreach ($p->oc as $line) {
              //      if ((isset($line->prestacao) || isset($line->valorcredito)) && ($line->prestacao > 0 || $line->valorcredito>0)) {
                        !isset($line->tipocredito) ? $line->tipocredito = '' : null;
                        !isset($line->valorcredito) ? $line->valorcredito = 0 : null;
                        !isset($line->prestacao) ? $line->prestacao = 0 : null;
                        !isset($line->liquidar)  ? $line->liquidar = 0 : null;
                        $queryOC = sprintf("INSERT INTO cad_outroscreditos(lead,linha,tipocredito,valorcredito,prestacao, liquidar) "
                                . " VALUES(%s, %s,'%s', %s, %s, %s)", $p->id, $ln, $line->tipocredito, $line->valorcredito, $line->prestacao, $line->liquidar);
                        mysqli_query($con, $queryOC);
                        $ln++;
         //           }
                }
            }
        }
        
        // Guarda as simulações para enviar no email
        if (isset($p->sim)) {
            if (sizeof($p->sim) > 0) {
                $ln = 1;
                foreach ($p->sim as $line) {
                        !isset($line->tipocredito) ? $line->tipocredito = '' : null;
                        !isset($line->valor) ? $line->valor = 0 : null;
                        !isset($line->prestacao) ? $line->prestacao = 0 : null;
                        !isset($line->prazo)  ? $line->prazo = 0 : null;
                        $querySim = sprintf("INSERT INTO cad_simulag(lead, linha, gestor, tipocredito, valor, prestacao, prazo) "
                                . " VALUES(%s, %s, %s, '%s', %s, %s, %s)", $p->id, $ln, $userId, $line->tipocredito, $line->valor, $line->prestacao, $line->prazo);
                        mysqli_query($con, $querySim);
                        $ln++;
                }
            }
        }
        
        //Registar os documentos pedidos
        $ln = 1;
        foreach ($docs->docs as $line) {
            $queryDOC = sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc) "
                    . " VALUES(%s,%s,%s)", $p->id, $ln, $line->id);
            mysqli_query($con, $queryDOC);
            $ln++;
        }
        // Registar no recuperação
        mysqli_query($con, sprintf("INSERT INTO arq_histrecuperacao(lead, status, user) VALUES(%s, 1, %s)", $p->id, $userId));
        
        //Registar AGENDAMENTO 
        //Atualiza o status da linha de agendamento atual
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $p->id));
        //insere a data expectavel
        $data1 = date('Y-m-d', strtotime($a->dataExpectavel));
        $queryAG = sprintf("INSERT INTO cad_agenda(lead,user,data,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
                . " VALUES(%s,%s,NOW(),'%s',%s,%s,%s,%s)", $p->id, $userId, $data1, 0, 0, 3, 1);
        mysqli_query($con, $queryAG);

        //Alterar o status da LEAD na tabela LEADS para a aguardar documentação
        //Atualizar dados da LEAD
        mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s', nif=%s, email='%s', telefone=%s, tipo='%s', status=8, datastatus=NOW(),user=%s WHERE id=%s"
                        , ucwords(mb_strtolower($p->nome, 'UTF-8')), $p->nif, $p->email, $p->telefone, $p->tipocredito, $userId, $p->id));

        //Verificar se o cliente já tem acesso ao portal
        $result = mysqli_query($con, sprintf("SELECT count(*) FROM cad_clientes WHERE lead=%s ", $p->id));
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_NUM);
            if ($row[0] > 0) {
                $pass = "a que já tem";
            } else {
                //Criar registo no cad_clientes com uma password
                $pass = gerarPassword(6);
                mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, nif, password) VALUES(%s, '%s', '%s', %s, '%s') ",
                                $p->id, $p->nome, $p->email, $p->nif, passwordHash::hash($pass)));
            }
        }


        //ENVIO de EMAIL para pedir documentação
        if ($dt->tipoEnv == 'email') {
            $tc = '';
            $pz = ".";
            //Tipo de credito
            switch ($p->tipocredito) {
                case 'CP':
                    $tc = "Crédito Pessoal - " . $p->montante . " Euros";
                    $pz = " pelo prazo de " . $p->prazopretendido . " meses.";
                    break;
                case 'CC':
                    $tc = "Crédito Consolidado - " . $p->montante . " Euros";
                    $pz = " pelo prazo de " . $p->prazopretendido . " meses.";
                    break;
                case 'CT':
                    $tc = 'Cartão de Crédito';
                    $pz = ".";
                    break;
                case 'CHCC':
                    $tc = "Crédito Hipotecário - Consolidado com Crédito Habitação - " . $p->montante . " Euros";
                    $pz = " pelo prazo de " . $p->prazopretendido . " meses.";
                    break;
                case 'CH1':
                    $tc = "Crédito  Hipotecário - 1ª Hipoteca" . $p->montante . " Euros";
                    $pz = " pelo prazo de " . $p->prazopretendido . " meses.";
                    break;
                case 'CH2':
                    $tc = "Crédito  Hipotecário - 2ª Hipoteca" . $p->montante . " Euros";
                    $pz = " pelo prazo de " . $p->prazopretendido . " meses.";
                    break;
            }
            //Assunto
            $assunto = "Ref:" . $p->id . " - Documentação para " . $tc;
            //Lista dos documentos pedidos
            $lista = "<ul>";
            foreach ($docs->docs AS $d) {
                $lista .= "<li><u>" . $d->nomedoc . ".</u> <span>" . $d->descricao . "</span></li>";
            }
            $lista .= "</ul>";

            $simulacao = "";
            if ($incluirSimulacao) {
                $simulacao = "<p><h4><strong>Valor de simulação -</strong> &nbsp;&nbsp;&nbsp;<em>Valor pretendido: <strong>" . $p->montante . " Euros</strong></em>"
                        . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prazo: <strong>" . $p->prazopretendido . " Meses</strong></em>"
                        . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prestação: <strong>" . $p->valorprestacao . " Euros</strong></em></h4></p>";

               // Outras simulações
                if (isset($p->sim)) {
                   if (sizeof($p->sim) > 0) {
                     $ln =2;
                    foreach ($p->sim as $line) {
                            !isset($line->tipocredito) ? $line->tipocredito = '' : null;
                            !isset($line->valor) ? $line->valor = 0 : null;
                            !isset($line->prestacao) ? $line->prestacao = 0 : null;
                            !isset($line->prazo)  ? $line->prazo = 0 : null;
                            
                            $simulacao .= "<p><h5><strong>Valor de simulação  ".$ln.") -</strong> &nbsp;&nbsp;&nbsp;<em>Valor pretendido: <strong>" . $line->valor . " Euros</strong></em>"
                        . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prazo: <strong>" . $line->prazo . " Meses</strong></em>"
                        . " &nbsp;&nbsp;&nbsp;&nbsp;<em>Prestação: <strong>" . $line->prestacao . " Euros</strong></em></h5></p>";
                            
                        $ln++;
                    }
                  }
               }
               
               $simulacao .= " <p><small><em>(Informamos que estes valores são meramente indicativos e correspondem  a um valor médio de simulação)</em></small></p>"
                        . "</br></br>";
            }

            $senha = '';
            if ($pass) {
                $senha = "Senha de acesso: " . $pass;
            }


            //Mensagem
                $msg = "<p>Olá ".$p->nome."</p>"
                . "<p> Agradeço desde já a sua disponibilidade, e confiança, para trabalharmos o seu pedido de crédito.</p>"
                .$simulacao
                . "<p>Solicitamos o envio breve dos seguintes documentos a fim de podermos concluir o mesmo.</p>"
                .$lista
                ."<br/><br/>"
                ."<p>Caso pretenda ajuda a organizar a sua documentação e poupar o seu tempo,"
                ." <strong> basta facultar a sua senha das finanças e podemos faze-lo por si</strong>,"
                ." deste modo obtemos os seguintes documentos:</p>"
                ."<p>- Mapa de Responsabilidades de Crédito do Banco de Portugal, último IRS entregue e o comprovativo de morada.</p>" 
                ."<p>Assim, só terá que nos fazer chegar a restante documentação solicitada.</p>"
                        ."<p>USE O PORTAL DO CLIENTE, o "
                        . "<a href='https://api.whatsapp.com/send?1=pt&phone=351".$dt->user->telefone."'>&#9758; WhatsApp</a>"
                        . " ou responda a este email, anexando a documentação pedida."
                . "<p>Acelere todo o processo aqui:</p>"
                . "<h2><a href='https://gestlifes.com/GestLifesClient/#/login'>&#9758; Portal do Cliente</a></h2>"
                . "<h3><strong>( ".$senha." )</strong></h3>"
                ."<p>Ao usar o Portal do Cliente para anexar a documentação estará a dar mais rapidez ao seu processo!";        
                       

            //Enviar o email
            $result = new sendEmail($con, $dt->user->id, $dt->user->email, $dt->address->email, $assunto, $msg, "", 10, $p->id);
            if ($result) {

              //  echo "Mensagem enviada com sucesso!";
                //Obter dados necessários para envio de SMS
                $result_ = mysqli_query($con, sprintf("SELECT P.telefone, U.deviceId FROM arq_leads L "
                                . " INNER JOIN cad_utilizadores U ON U.id=L.user"
                                . " INNER JOIN arq_processo P ON P.lead = L.id WHERE L.id=%s", $p->id));
                if ($result_) {
                    $row_ = mysqli_fetch_array($result_, MYSQLI_ASSOC);
                    $sms = "Esta a um passo de obter credito."
                            . " Basta enviar os documentos necessarios."
                            . " Consulte o seu email (verifique Spam)"
                            . $dt->user->email ;
                    $deviceId = $row_['deviceId'];
                    $telefone = $row_['telefone'];
                    $msg = array("telefone" => $telefone, "sms" => $sms);
                    $respSMS = sendPushNotificationToFCM($deviceId, $msg);
                    mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo)"
                            . " VALUES('%s', %s,'2')","respSMS: ".$respSMS." \nTEXTO SMS: ".$sms . ' ;   Lead: ' . $p->id, $dt->user->id));

                    echo "SMS enviado";
                }
            } else {
                echo "Erro no envio do email. Por favor contacte suporte!";
            }
        }
    } else {
        echo $query;
    }
} else {
    echo $query0;
}

