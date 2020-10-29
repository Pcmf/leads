<?php

/* 
 * Cancelar o processo por não ser para financiamento 
 * 
 * Registar um CC em que o cliente já aceitou a proposta
 */
require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

//Cancelar o processo
mysqli_query($con, sprintf("UPDATE arq_leads SET status=27, datastatus=NOW(), analista=%s WHERE id=%s ", $dt->user, $dt->lead));

//Inserir no cad_cartaocredito como aceite pelo cliente
$query = sprintf("INSERT INTO cad_cartaocredito(lead, sugerido, formasugestao, dataresposta, respostacliente, status, user) VALUES(%s, NOW(), 'Telefone', NOW(), '1', 2, %s)", $dt->lead, $dt->user);
$result = mysqli_query($con, $query);
if($result){
    echo 'OK';
} else {
    echo $query;
}
