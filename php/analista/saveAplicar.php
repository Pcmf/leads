<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

!isset($dt->usar) || !$dt->usar ? $dt->usar=0 : $dt->usar=1;
$result = mysqli_query($con, sprintf("UPDATE cad_outrosrendimentos SET usar=%s WHERE lead=%s AND linha=%s ",
        $dt->usar, $dt->lead, $dt->linha));
if($result){
    echo 'Ok';
} else {
    echo 'Erro! '.sprintf("UPDATE cad_outrosrendimentos SET usar=%s WHERE lead=%s AND linha=%s ",
        $dt->usar, $dt->lead, $dt->linha);
}
