<?php
require_once '../openCon.php';
/* 
 * Registar nas tentativas de contacto
 */

$json = file_get_contents("php://input");
$dt = json_decode($json);


mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
                . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
                $dt->lead, $dt->user->id, $dt->lead, $dt->user->id, 0, 1));

