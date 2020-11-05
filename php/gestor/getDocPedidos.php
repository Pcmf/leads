<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$lead = file_get_contents("php://input");

$resp=array();
$query= sprintf("SELECT P.linha,P.recebido,N.*, D.nomefx "
        . "  FROM cad_docpedida P "
        . " INNER JOIN cnf_docnecessaria N ON P.tipodoc = N.id "
        . " LEFT JOIN arq_documentacao D ON D.lead=%s AND D.linha=P.linha "
        . " WHERE P.lead =%s",$lead,$lead);
$result=mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}


