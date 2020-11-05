<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';
$lead = file_get_contents("php://input");
$query = sprintf("INSERT INTO rgpd(lead,email,nome) VALUES(%s,'%s','%s')", $lead,'teste emai','$nome');

$result = mysqli_query($con,$query);
if($result){
    echo '<script>window.close();</script>';
} else{
    echo 'Obrigado, mas já está! <button onclick="window.close()">Fechar</button>';
}


return;