<?php

/* 
 * Registar a resposta da cofidis
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$dt->processo->valorfinanciado? null : $dt->processo->valorfinanciado=0;

mysqli_query($con, sprintf("UPDATE arq_cofidisdirecto SET valorfinanciado=%s, status=%s WHERE lead=%s ",
        $dt->processo->valorfinanciado, $dt->sts, $dt->processo->lead));

mysqli_query($con, sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW(), analista=%s WHERE id=%s ", 
        $dt->sts, $dt->user, $dt->processo->lead));

return;