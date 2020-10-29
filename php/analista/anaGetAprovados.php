<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$userId = file_get_contents("php://input");

$resp=array();

//List Resultados - 16 e 
$query = sprintf("SELECT P.lead, P.nome AS nome_cliente, P.telefone, A.nome, F.*, DATE(F.dataaprovado) AS dtaprovado, L.tipo, "
        . " DATE(F.dtcontratocliente) AS dtcliente, DATE(F.dtcontratoparceiro) AS dtparceiro, DATE(F.incompleto) AS incompleto  "
        . " FROM arq_processo P"
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " INNER JOIN cad_financiamentos F ON P.lead=F.lead "
        . " INNER JOIN cad_parceiros A ON F.parceiro=A.id "
        . " WHERE L.status=16 AND L.analista=%s AND F.status=6"
        . " ORDER BY L.datastatus",$userId);
$result = mysqli_query($con,$query);
if($result){
    while ($row1 = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row1);
    }
    echo json_encode($resp);
}