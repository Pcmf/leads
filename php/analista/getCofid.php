<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$resp= array();

$query = sprintf("SELECT C.*, P.nome, P.nif, P.email, P.telefone, S.nome AS statusnome "
        . " FROM arq_cofidisdirecto C "
        . " INNER JOIN arq_processo P ON P.lead=C.lead "
        . " INNER JOIN cnf_statuslead S ON S.id=C.status"
        . " WHERE C.status IN(28,30,31) ");
$result = mysqli_query($con, $query);
if($result){
    while ($row= mysqli_fetch_array($result, MYSQLI_ASSOC)){
        array_push($resp, $row);
    }
    echo json_encode($resp);
}