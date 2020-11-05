<?php

/* 
 * Obter a lista das simulações guardadas para uma lead
 */
require_once '../openCon.php';

$lead = file_get_contents("php://input");


$resp = array();

$query = sprintf("SELECT * "
        . " FROM cad_simula  "
        . " WHERE lead=%s ", $lead);
$result = mysqli_query($con, $query);
if($result) {
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
}

echo json_encode($resp);

