<?php

/*
 * guarda alterações e novos dados financeiros nos ficheiros relevantes:
 *  arq_processo, cad_simula, cad_simulag
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);
$gestor = $dt->gestor;
$lead = $dt->lead;
$sim = $dt->sim;



// Simulações para Email
if ($sim) {
    $linha = 1;
    $result = mysqli_query($con, sprintf("SELECT max(linha) AS max FROM cad_simulag WHERE lead=%s", $lead));
    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $linha = $row['max'] + 1;
    }
    mysqli_query($con, sprintf("INSERT INTO cad_simulag(lead, linha, gestor, tipocredito, valor, prestacao, prazo) "
                    . " VALUES(%s, %s, %s, '%s', %s, %s, %s)",
                    $lead, $linha, $gestor, $sim->tipocredito, $sim->valor, $sim->prestacao, $sim->prazo));
}

$resp = array();
$result = mysqli_query($con, sprintf("SELECT * FROM cad_simulag WHERE lead=%s", $lead));
if($result) {
    while ($row1 = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row1);
    }
    echo json_encode($resp);
}