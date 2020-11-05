<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
$user = file_get_contents("php://input");

$resp = array();

// lista contratos
$query = sprintf("SELECT C.*,  L.nome, L.telefone, S.status AS statusnome "
        . " FROM cad_cartaocredito C INNER JOIN arq_leads L ON L.id=C.lead "
        . " INNER JOIN cnf_statuscc S ON S.id=C.status "
        ."  WHERE C.user=%s AND C.respostacliente=1 AND C.respostaparceiro=1 AND C.datarespostaparceiro IS NOT NULL AND C.status<6"
        . " ORDER BY L.datastatus DESC", $user);
$result = mysqli_query($con, $query);
if($result){
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
}


echo json_encode($resp);