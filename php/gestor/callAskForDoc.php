<?php
require_once '../openCon.php';

$lead = file_get_contents("php://input");

$resp = array();

$query = sprintf("SELECT L.user, L.dataentrada, L.datastatus, S.nome AS status, P.lead, P.nome, P.email, P.telefone, P.idade,"
        . " P.valorpretendido, P.tipocredito, P.prazopretendido, P.prestacaopretendida, P.finalidade "
        . " FROM arq_leads L"
        . " INNER JOIN arq_processo P ON P.lead=L.id"
        . " INNER JOIN cnf_statuslead S ON S.id=L.status"
        . " WHERE L.id=%s ", $lead);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $resp['lead'] = $row;
    
    // Obter os documentos pedidos/recebidos
    $result0 = mysqli_query($con, sprintf("SELECT D.linha, recebido,A.nomedoc FROM cad_docpedida D "
            . " INNER JOIN cnf_docnecessaria A ON A.id=D.tipodoc "
            . " WHERE lead=%s ", $lead));
    if($result0){
        $temp=array();
        while ($row1 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
            array_push($temp, $row1);
        }
        $resp['docs'] = $temp;
    }
}

echo json_encode($resp);