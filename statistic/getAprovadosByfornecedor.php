<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../php/openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);
$tm = json_decode($dt->tm);

//criar filtros 
if(isset($tm->data11)){
    !isset($tm->data22) ? $tm->data22= $tm->data11:null;
    $dataaprovado = " DATE(F.dataaprovado) BETWEEN '".$tm->data11."' AND '".$tm->data22."' ";
} else {
   
   $tm->opc=='mes' ? $dataaprovado = " YEAR(F.dataaprovado)=YEAR(NOW()) AND MONTH(F.dataaprovado)=MONTH(NOW()) ":null;
   $tm->opc=='dia' ? $dataaprovado = " DATE(F.dataaprovado)=DATE(NOW()) ":null;
}

$resp = array();
if($dt->forn=='all'){
    $query = sprintf("SELECT F.*,L.id, L.nomelead,L.nome, FL.empresa AS nomefornecedor,"
            . " P.nome AS nomeparceiro, U.nome AS gestor, U1.nome AS analista "
            . " FROM cad_financiamentos F INNER JOIN arq_leads L ON L.id=F.lead "
            . " INNER JOIN cad_fornecedorleads FL ON FL.id=L.fornecedor "
            . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " INNER JOIN cad_utilizadores U1 ON U1.id=L.analista "
            . " WHERE L.status=16 AND F.status=6 AND ".$dataaprovado);    
} else {
    $query = sprintf("SELECT F.*,L.id, L.nomelead,L.nome, FL.empresa AS nomefornecedor,"
            . " P.nome AS nomeparceiro, U.nome AS gestor, U1.nome AS analista "
            . " FROM cad_financiamentos F INNER JOIN arq_leads L ON L.id=F.lead "
            . " INNER JOIN cad_fornecedorleads FL ON FL.id=L.fornecedor "
            . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " INNER JOIN cad_utilizadores U1 ON U1.id=L.analista "
            . " WHERE L.status=16 AND  F.status=6 AND ".$dataaprovado." AND L.fornecedor=%s",$dt->forn);
}
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}
