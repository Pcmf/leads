<?php

/* 
 * Regista se o CC foi ou não ativado pelo cliente
 */

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


mysqli_query($con, sprintf("UPDATE cad_cartaocredito SET ativado=%s, dataativado=NOW(), status =7  WHERE lead=%s", $dt->opc, $dt->lead));

return;