<?php

/* 
 * Obter todas as sugestões ativas
 */
require_once '../../php/openCon.php';
$user = file_get_contents("php://input");


//sugeridos
$query = sprintf("SELECT C.lead, DATE(C.dataresposta) AS dataresposta, L.nome, L.telefone"
      //  . ", SF.status, F.datastatus "
        . " FROM cad_cartaocredito C INNER JOIN arq_leads L ON L.id=C.lead "
     //   . " LEFT JOIN cad_financiamentos F ON F.lead=C.lead  LEFT JOIN cnf_stsfinanciamentos SF ON SF.id = F.status "
        ."  WHERE C.user=%s AND C.respostacliente=1 AND C.contratoenviado IS NULL AND L.status!=26 AND C.datarespostaparceiro IS NULL ", $user);
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
