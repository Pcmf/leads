<?php

/* 
 * Obter os processos financiados para o analista e para o mes atual
 */

require_once '../openCon.php';
require_once '../passwordHash.php';
include_once '../PasswordGenerator.php';
require_once '../../class/sendEmail.php';

$lead = file_get_contents("php://input");


$result = mysqli_query($con, sprintf("SELECT L.user, P.lead, P.nome, P.email, P.nif, U.email AS useremail"
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.id=%s ", $lead));
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
} else {
    echo 'Erro';
    return;
}

$pass = gerarPassword(6);

$result0 = mysqli_query($con, sprintf("UPDATE cad_clientes  SET password= '%s', ativo=1 WHERE lead=%s ",    passwordHash::hash($pass), $lead));

if(mysqli_affected_rows($con) <= 0) {
    $result0 = mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, nif, password) VALUES(%s, '%s', '%s', %s, '%s') ",
                $lead, $row['nome'], $row['email'], $row['nif'], passwordHash::hash($pass)));
}

$assunto = "Área de Cliente Gestlifes - novo acesso";

$msg = "<p>Olá ".$row['nome']."!</p>"
        . "<p>Como solicitado, enviamos uma nova senha para aceder à "
        . "<a href='https://gestlifes.com/GestLifesAC'>Área de Cliente</a>(senha: <strong>".$pass."</strong>)"
        . " e poder consultar o estado do seu processo.</p>";

$resp = new sendEmail($con, $row['user'], $row['useremail'], $row['email'], $assunto, $msg, "", 4, $lead);
if($resp){
            echo "Nova senha enviada com sucesso!\n Nova senha: ".$pass;
    } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
} 