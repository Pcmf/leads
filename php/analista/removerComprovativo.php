<?php

/* 
 * Remove a linha com o pedido de comprovativo
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


mysqli_query($con, sprintf("UPDATE cad_comprovativos SET nomedoc=null, documento=null, tipodoc=null, status=0, datastatus=NOW() "
        . " WHERE lead=%s AND linha=%s", $dt->c->lead, $dt->c->linha));

// Se o status da lead for 35 (comprovativos recebidos) volta a colocar a 23 (ACP)
mysqli_query($con, sprintf("UPDATE arq_leads SET status=23, datastatus=NOW() WHERE id=%s AND status=35", $dt->c->lead));

return;
