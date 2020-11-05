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

if($tm->opc=='dia'){
    $sel='DATE(L.datastatus) = DATE(NOW())';
    $selFin='DATE(F.datafinanciado) = DATE(NOW())';
    
}
   
if($tm->opc=='mes'){
        $sel='YEAR(L.datastatus) = YEAR(NOW()) AND MONTH(L.datastatus) = MONTH(NOW())';
        $selFin='YEAR(F.datafinanciado) = YEAR(NOW()) AND MONTH(F.datafinanciado) = MONTH(NOW())';
}

if($tm->opc==''){
    $sel = "L.datastatus BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
    $selFin = " F.datafinanciado BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
}



$resp = array();

switch ($dt->sts) {
    case 'all' :
        $sts = "";
        break;
    case 'pen' :
        $sts = " AND L.status=13 ";
        break;
    case 'adoc' :
        $sts = " AND L.status=21 ";
        break; 
    case 'nul' :
        $sts = " AND L.status=14 ";
        break; 
    case 'apv' :
        $sts = " AND L.status=16 AND F.status=6 ";
        break;
    case 'fin' :
        $sts = " AND F.status=7 AND ".$selFin;
        break;
    case 'rjt' :
        $sts = " AND L.status IN(15,19) ";
        break;
    case 'des' :
        $sts = " AND L.status=18 ";
        break; 
    case 'afcp' :
        $sts = " AND L.status=25 ";
        break;       
    default:
        break;
}


$query = sprintf("SELECT L.id, P.nome,P.telefone,P.email,S.nome AS status, L.datastatus,"
        . " U.nome AS gestor, U2.nome AS analista, F.montante, L.status AS sts "
        . " FROM arq_leads L LEFT JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " LEFT JOIN cad_financiamentos F ON F.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
        . " WHERE ".$sel.$sts."  AND analista=%s GROUP BY L.id",$dt->id);
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}