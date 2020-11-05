<?php

/* 
 * Atualiza O STATUS DO PROCESSO. para o analista
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");

$dt = json_decode($json);

$query = sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW() WHERE id=%s",$dt->status,$dt->lead);
$result= mysqli_query($con, $query);
//Se for para alterar para ACP
if(isset($dt->financiamento) && $dt->financiamento=='ACP'){
    $query0 = sprintf("UPDATE cad_financiamentos SET status=6 WHERE lead=%s AND status=7", $dt->lead);
    $result0 = mysqli_query($con, $query0);
}
if($result){
    echo 'OK';
} else {
    echo $query;
}