<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../php/openCon.php';
require_once '../class/sendEmail.php';

$result01 = mysqli_query($con, "SELECT * from cad_utilizadores where tipo='Gestor' and ativo=1");
if ($result01) {
    while ($row01 = mysqli_fetch_array($result01, MYSQLI_ASSOC)) {
        $user = $row01['id'];

        //Selecionar Leads que estejam anuladas á 30 dias e enviar email de ultima oportunidade
        $result = mysqli_query($con, sprintf("SELECT L.* FROM arq_leads L "
                        . " LEFT JOIN cad_rejeicoes R ON R.lead=L.id "
                        . " WHERE L.user=%s AND DATEDIFF(NOW(), L.datastatus)=30 AND (L.status IN(5,9) "
                        . " OR (L.status=104 AND R.motivo LIKE 'Cancelado a pedido do cliente') )", $user));
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                sendEmailUltimaOportunidade($con, $row);
                usleep(5000000);
            }
        }
    }
}

//email ultima oportunidade para anulados á 30 dias
function sendEmailUltimaOportunidade($con, $row) {
    $assunto = "(U30)Ref: " . $row['id'] . " - Ultima Oportunidade para o seu Financiamento!";
    $emailDestino = $row['email'];
    $nomeCliente = $row['nome'];
    $result = mysqli_query($con, sprintf("SELECT * FROM cad_utilizadores WHERE id=%s", $row['user']));
    if ($result) {
        $row0 = mysqli_fetch_array($result, MYSQLI_ASSOC);
    }

    $emailOrigem = $row0['email'];
    $nomeGestor = $row0['nome'];


    $nomeGestor = $row0['nome'];

            $msg = "<p>Olá ".$nomeCliente."</p>"
                    . "<p>No seguimento da sua simulação de crédito, vimos oferecer os nossos "
                    . "serviços para o/a ajudar a conseguir o crédito que procura!</p>"
                    . "<p>Estamos <a href='https://www.bportugal.pt/intermediariocreditofar/embrace-calculus-unipessoal-lda'>"
                    . "vinculados no Banco de Portugal</a> e já ajudámos mais de 1.000 pessoas em 2019</p>"
                    . "<p>Trabalhamos com marcas como <strong>Cofídis, Cetelem, Novo Banco, Credibom e Unicre.</strong> "
                    . "Desta forma conseguimos comparar por si a melhor oferta para que obtenha as "
                    . "<strong>melhores taxas e condições sem custos para o cliente!</strong></p>"
                    . "<p>Acelere todo o processo aqui:</p>"
                    . "<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>"
                    . "<p>Após ter preenchido irá receber uma resposta imediata ao pedido!</p>"
                    . "<p><ul>"
                    . "<li>Caso queira ser contactado por telefone, não há problema! Basta responder-nos a este e-mail,"
                    . " indicando qual o melhor período para o fazermos.</li>"
                    . "<li>Caso queira submeter os dados diretamente por e-mail basta <strong><u>seguir estas indicações:</u></strong></li>"
                    . "</ul></p>"
                    . "<p><ol>"
                    . "<li>Valor do Crédito?</li>"
                    . "<li>Quer pagar em quantos meses?</li>"
                    . "<li>Finalidade?</li>"
                    . "<li>Profissão?</li>"
                    . "<li>Estado Cívil?</li>"
                    . "<li>Tipo de habitação? (Arrendada / Familiar / Própria / Própria com Crédito - Que valor paga?)</li>"
                    . "</ol></p>"
                    . "<p>Documentação a enviar: </p>"
                    . "<p>"
                    ."<ol>"
                    ."<li>Cartão de Cidadão. (frente e verso)</li>"
                    ."<li>Comprovativo de morada.<small>(Com data inferior a 3 meses) Ex. ou documentos aceites:"
                        . " comprovativo domicilio fiscal (retirado no aceite das finanças), carta da agua, luz, internet.</small></li>"
                    ."<li>Último IRS ou código de validação que pode encontrar este código no canto superior da primeira página do seu IRS.</li>"
                    ."<li>3 recibos de vencimento ou comprovativo de reforma.</li>"
                    ."<li>Comprovativo do IBAN. <small>Onde venha mencionado nome do titular (Com data inferior a 3 meses)</small></li>"
                    ."<li>Mapa de Responsabilidades Cidadão (<a href='http://bit.ly/mapaderesponsabilidades'>pode descarregar aqui</a>)."
                    . " <small><strong>Cidadão, Utilizar dados do Portal das Finanças(NIF + Senha das Finaças)</strong></small> </li>"
                    . "</ol>"
                    ."</p>";   
    //Enviar email
    $result = new sendEmail($con, $row['user'], $emailOrigem, $emailDestino, $assunto, $msg, "",29, $row['id']);
//    if ($result) {
//        //  Registar no contacto
//        mysqli_query($con, sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
//                        . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)", $row['id'], $row['user'], $row['id'], $row['user'], 1, 15));
//    }
}
