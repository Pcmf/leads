<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

!isset($dt->adminchoice) || !$dt->adminchoice ? $dt->adminchoice=0 : $dt->adminchoice=1;
$result = mysqli_query($con, sprintf("UPDATE cad_outroscreditos SET adminchoice=%s, datachoice=NOW() WHERE lead=%s AND linha=%s ",
        $dt->adminchoice, $dt->lead, $dt->linha));
if($result){
    echo 'Ok';
} else {
    echo 'Erro! '.sprintf("UPDATE cad_outroscreditos SET adminchoice=%s, datachoice=NOW() WHERE lead=%s AND linha=%s ",
        $dt->adminchoice, $dt->lead, $dt->linha);
}
