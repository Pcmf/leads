<?php

/* 
 * Elimina documentos e pedido
 * ou
 * Cancela pedido de documento
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);
    //Apaga o pedido
    mysqli_query($con, sprintf("DELETE FROM cad_docpedida WHERE lead=%s AND linha=%s",$dt->lead,$dt->doc->linha));
