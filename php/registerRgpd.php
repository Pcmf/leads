<?php

/* 
 * Registar a confirmação de autorização RGPD
 * 
 */
require_once 'openCon.php';

$lead = $_GET['lead'];
$query0 = sprintf("SELECT nome,email FROM arq_processo WHERE lead=%s",$lead);
$result0 = mysqli_query($con,$query0);
if($result0){
    $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);
    $query = sprintf("INSERT INTO arq_rgpd(lead,email,nome) VALUES(%s,'%s','%s')", $lead,$row0['email'],$row0['nome']);

    $result = mysqli_query($con,$query);
    if($result){
        echo '<script>window.close();</script>';
    } else{
        echo 'Obrigado! <button onclick="window.close()">Fechar</button>';
    }
}

