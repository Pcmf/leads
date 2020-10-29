<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

mysqli_query($con, sprintf("UPDATE arq_leads SET email = '%s' WHERE id=%s", $dt->email, $dt->lead ));
mysqli_query($con, sprintf("UPDATE arq_processo SET email = '%s' WHERE lead=%s", $dt->email, $dt->lead ));

return;