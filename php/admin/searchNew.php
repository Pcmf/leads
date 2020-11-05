<?php

/* 
 * Procurar nas LEADS novas com Telefone, NIF ou email
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$query0 = '';
isset($dt->telefone)? $query0 = "L.telefone='".$dt->telefone."'":null;
isset($dt->nif)?  $query0="L.nif =".$dt->nif:null;
isset($dt->email)? $query0="L.email LIKE '".$dt->email."'":null;

$resp = array();
$query = "SELECT L.*,F.nome FROM arq_leads L "
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
        . " WHERE status=1 AND ".$query0;
//echo $query.'<br/>';
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}