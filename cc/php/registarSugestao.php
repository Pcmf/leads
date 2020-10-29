<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


//Verificar se a lead existe e se existir obter o nome do cliente e telefone e se tem financiamentos
$query = sprintf("SELECT L.id, L.nome, L.telefone, SF.status, SF.descricao, F.datastatus "
        . " FROM arq_leads L "
        . " LEFT JOIN cad_financiamentos F ON F.lead=L.id "
        . " LEFT JOIN cnf_stsfinanciamentos SF ON SF.id = F.status"
        . " WHERE L.id=%s ORDER BY F.datastatus DESC LIMIT 1", $dt->lead);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    
    //Inserir no cad_cartaocredito
    $query = sprintf("INSERT INTO cad_cartaocredito(lead, sugerido, formasugestao, status, user) VALUES(%s, NOW(), '%s', %s,  %s)", $dt->lead, $dt->formaSugestao, 1, $dt->user);
    $result = mysqli_query($con, $query);
    if($result){
        echo 'OK';
    } else {
        echo $query;
    }
    
} else {
    echo 'Essa lead não existe!';
}