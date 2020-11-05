<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$lead = file_get_contents("php://input");


mysqli_query($con, sprintf("UPDATE arq_leads SET status=25, datastatus=NOW() WHERE id=%s", $lead));
mysqli_query($con, sprintf("UPDATE cad_financiamentos SET montante=montante*(-1), status=12, datastatus=NOW() WHERE lead=%s AND status=7", $lead));


return;