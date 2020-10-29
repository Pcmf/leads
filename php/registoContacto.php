<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'openCon.php';

$dt = json_decode(file_get_contents("php://input"));


$query = sprintf("INSERT INTO cad_registocontacto(lead, user, contactonum, motivocontacto) "
        . " VALUES(%s, %s, (SELECT max(A.contactonum) FROM cad_registocontacto A WHERE A.lead=%s) +1, %s )"
        , $dt->lead, $dt->user, $dt->lead, $dt->motivo);



$result = mysqli_query($con, $query);

echo json_encode($result);
