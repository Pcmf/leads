<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
//$image = file_get_contents('../../img/logo_email_xs.png');
$json = file_get_contents("php://input");
$dt = json_decode($json);
!isset($dt->status) ? $dt->status=0 : null;
if ($dt->status == 1) {

        mysqli_query($con, sprintf("UPDATE arq_leads SET status=20, datastatus=NOW(), analista=%s"
                        . " WHERE id=%s ", $dt->analista, $dt->lead));
}

mysqli_query($con, sprintf("INSERT INTO cad_audit(lead, status, user) VALUES(%s, %s, %s) "
                , $dt->lead, $dt->status, $dt->user));

return;


