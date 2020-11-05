<?php

/* 
 * Obter as leads em analise 
 */

require_once '../openCon.php';
$user = file_get_contents("php://input");

$resp=array();

$query = sprintf("SELECT P.lead,P.nome,P.telefone,P.email,P.valorpretendido,"
        . " S.nome AS status,L.datastatus,U.nome AS analista "
        . " FROM arq_processo P INNER JOIN arq_leads L ON L.id=P.lead "
        . " LEFT JOIN cad_utilizadores U ON U.id=L.analista "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status"
        . " WHERE L.user=%s AND L.status>=10 AND L.status<26 AND YEAR(L.datastatus)=YEAR(NOW())"
        . " AND  MONTH(L.datastatus)>=MONTH(NOW())-1",$user);

$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
}
