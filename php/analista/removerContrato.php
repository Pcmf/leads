<?php

/* 
 * Remover contrato da BD
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

mysqli_query($con, sprintf("DELETE FROM arq_contratos WHERE lead=%s AND linha=%s", $dt->lead, $dt->linha));

return;

