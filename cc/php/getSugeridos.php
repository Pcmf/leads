<?php

/* 
 * Obter todas as sugestões ativas
 */
require_once '../../php/openCon.php';
$user = file_get_contents("php://input");


//sugeridos
$query = sprintf("SELECT C.lead, DATE(C.sugerido) AS sugerido, C.formasugestao, L.nome, L.telefone  "
        . " FROM cad_cartaocredito C INNER JOIN arq_leads L ON L.id=C.lead "
        ."  WHERE C.user=%s AND C.respostacliente IS NULL AND C.contratoenviado IS NULL AND L.status!=26", $user);
$result = mysqli_query($con, $query);
//echo $query;
$resp = array();
if($result){
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $result0 = mysqli_query($con, sprintf("SELECT F.datastatus, S.status FROM cad_financiamentos F "
                . " INNER JOIN cnf_stsfinanciamentos S ON S.id=F.status"
                . " WHERE F.lead=%s  ORDER BY F.datastatus DESC LIMIT 1" , $row['lead']));
        $row['status'] = '';
        if($result0){
             $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
                    $row['status'] = $row0['status'];
        }
        array_push($resp, $row);
    }
    echo json_encode($resp);
}
