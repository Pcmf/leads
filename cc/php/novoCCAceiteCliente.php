<?php

/* 
 * Registar um CC em que o cliente já aceitou a proposta
 */
require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);



    //Inserir no cad_cartaocredito como aceite pelo cliente
    $query = sprintf("INSERT INTO cad_cartaocredito(lead, sugerido, formasugestao, dataresposta, respostacliente, user) VALUES(%s, NOW(), 'Telefone', NOW(),'1', %s)", $dt->lead, $dt->user);
    $result = mysqli_query($con, $query);
    if($result){
        echo 'OK';
    } else {
        echo $query;
    }
    
