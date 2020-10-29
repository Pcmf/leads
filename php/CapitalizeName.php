<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';

$nome ='';
$result = mysqli_query($con,"SELECT L.id, L.nome FROM arq_leads L where L.rgpd=0");
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $nome= ucwords( mb_strtolower( $row['nome'], 'UTF-8'));
      //  echo $row['nome']. '   <=>  '.utf8_encode($nome)."<br/>";
        mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s' WHERE id=%s", $nome, $row['id']));
        mysqli_query($con, sprintf("UPDATE arq_processo SET nome='%s' WHERE lead=%s", $nome, $row['id']));
    }
    echo 'Fim';
}
