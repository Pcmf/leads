<?php

/* 
 * Obter a lista das simulações guardadas para uma lead
 */
require_once '../openCon.php';

$lead = file_get_contents("php://input");


$resp = array();

$query = sprintf("SELECT S.*, P.nome"
        . " FROM cad_simula S "
        . " LEFT JOIN cad_parceiros P ON P.id=S.parceiro "
        . " WHERE S.lead=%s ", $lead);
$result = mysqli_query($con, $query);
if($result) {
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
}

echo json_encode($resp);

