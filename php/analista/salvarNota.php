<?php

/* 
 * Atualiza o campo nota com informação das anotações do analista
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);

$query = sprintf("UPDATE arq_processo SET nota='%s' WHERE lead=%s",$dt->nota,$dt->lead);
mysqli_query($con, $query);