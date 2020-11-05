<?php

/* 
 * Obter todas as sugestões ativas
 */
require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);
$cancelados = '';
if($dt->sts ==6 ){
    $cancelados = " OR C.status = 9";
}
//sugeridos
$query = sprintf("SELECT C.*, L.nome, L.telefone, SF.status, F.datastatus, S.status AS statusnome "
        . " FROM cad_cartaocredito C INNER JOIN arq_leads L ON L.id=C.lead "
        . " INNER JOIN cnf_statuscc S ON S.id=C.status"
        . " LEFT JOIN cad_financiamentos F ON F.lead=C.lead  LEFT JOIN cnf_stsfinanciamentos SF ON SF.id = F.status "
        ."  WHERE C.user=%s AND (C.status=%s".$cancelados.") ORDER BY F.datastatus DESC", $dt->user, $dt->sts);
$result = mysqli_query($con, $query);
//echo $query;
$resp = array();
if($result){
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
}