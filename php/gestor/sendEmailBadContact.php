<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
require_once "../passwordHash.php";
require_once '../../class/sendEmail.php';
include_once '../../class/PasswordGenerator.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);

//Obter uma senha
$pass = gerarPassword(6);
//Criar registo no cad_clientes com uma password
mysqli_query($con, sprintf("INSERT INTO cad_clientes( lead, nome, email, password) VALUES(%s, '%s', '%s', '%s') ",
        $dt->lead->id, $dt->lead->nome, $dt->lead->email, passwordHash::hash($pass)));

$senha = "Senha de acesso: ".$pass;

$assunto = "Ref: ".$dt->lead->id." - Numero de telefone incorrecto.";
//Enviar o email

                //Obter a origem da lead
            $result = mysqli_query($con, sprintf("SELECT nomelead FROM arq_leads WHERE id=:lead", $dt->lead->id));
            if($result) {
                $row00 = mysqli_fetch_array($result, MYSQLI_ASSOC); 
            } 
    
            $msg = "<p>Olá " . $dt->lead->nome . "!</p>"
                    . "<p>Recebemos o seu pedido do crédito através do ".$row00['nomelead']." Por favor continue a submissão de dados"
                    . " através da "
                    . "<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>"
                    . "<h3><strong>( " . $senha . " )</strong></h3>"
                    . "<p>Trabalhamos com as melhores marcas de crédito em Portugal e temos a licença "
                    . " <a href='https://www.bportugal.pt/intermediariocreditofar/jpcom-unipessoal-lda'>nº0001409</a> do Banco Portugal. </p>"
                    . "<p>Desta forma conseguimos comparar por si a melhor oferta para que obtenha "
                    . "<strong>as melhores taxas, sem custos e sem compromissos.</strong></p>"; 


        //Enviar o email
        $result = new sendEmail($con, $dt->user->id, $dt->user->email, $dt->lead->email, $assunto, $msg,"", 5, $dt->lead->id);        
        if($result){
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        } 

