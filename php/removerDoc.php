<?php

/* 
 * Elimina documentos e pedido
 * ou
 * Cancela pedido de documento
 */
require_once 'openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);

if($dt->op=='Delete'){
    //Apaga o ficheiro da Bd e coloca como não recebido
    mysqli_query($con, sprintf("DELETE FROM arq_documentacao WHERE lead=%s AND linha=%s",$dt->lead,$dt->doc->linha));
    mysqli_query($con, sprintf("UPDATE cad_docpedida SET recebido=0,datarecebido=NULL WHERE lead=%s AND linha=%s",$dt->lead,$dt->doc->linha));
} else{ 
    //Apaga o pedido
    mysqli_query($con, sprintf("DELETE FROM cad_docpedida WHERE lead=%s AND linha=%s",$dt->lead,$dt->doc->linha));
}