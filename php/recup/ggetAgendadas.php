<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$userId = file_get_contents("php://input");

$resp= array();
//Anuladas 
$query = sprintf("SELECT L.id, L.nome, L.telefone, L.email, L.nif, A.agendadata, A.agendahora, T.tipoagendamento AS descricao, P.lead, P.user "
        . " FROM arq_leads L "
        . " INNER JOIN cad_agenda A ON A.lead=L.id "
        . " INNER JOIN cnf_tipoagendamento T ON T.id=A.tipoagenda "
        . " LEFT JOIN arq_processo P ON P.lead=L.id "
        . " WHERE  A.status=1 AND A.tipoagenda<>3 AND L.status IN (106, 107) AND L.user=%s ORDER BY A.agendadata , A.agendahora  ",$userId);
$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}
