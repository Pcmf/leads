<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';
$lead = file_get_contents("php://input");



mysqli_query($con, sprintf("UPDATE cad_cartaocredito SET contratoenviado= NOW() WHERE lead=%s ", $lead));

return;