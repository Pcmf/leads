<?php

/* 
 * Remove a linha com o pedido de comprovativo
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


mysqli_query($con, sprintf("DELETE FROM cad_comprovativos WHERE lead=%s AND linha=%s", $dt->c->lead, $dt->c->linha));

return;
