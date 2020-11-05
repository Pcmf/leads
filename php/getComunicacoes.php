<?php

/* 
 * Obter o historico das comunicações com cliente de uma lead
 */

require_once './openCon.php';


$lead = file_get_contents("php://input");
$resp  =array();

$result = mysqli_query($con, sprintf("SELECT * FROM arq_comunicacoes WHERE lead=%s ORDER by id", $lead));
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo false;
}