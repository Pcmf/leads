<?php

/* 
 * Poe a lead no satus 8 e faz agendamento tipo 3 para a data atual mais 3 dias
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead,user,agendadata, tipoagenda, status) VALUES(%s, %s, DATE_ADD(NOW(), INTERVAL 3 DAY), 3, 1)", $dt->lead, $dt->user));

mysqli_query($con, sprintf("UPDATE arq_leads SET status =8 WHERE id=%s ", $dt->lead));

return;
