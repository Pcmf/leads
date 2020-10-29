<?php

/* 
 * Receba uma conversa e regista na BD
 */

require 'openCon.php';
$json = file_get_contents("php://input");
$conv = json_decode($json);

$query = sprintf("INSERT INTO mural(origem, destino, assunto, dataenvio, status) VALUES(%s, %s, '%s', NOW(), 0 ) ",
        $conv->origem, $conv->destino, $conv->assunto);
if(mysqli_query($con, $query)){
    echo 'OK';
} else {
    echo $query;
}

