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
    $datastatus = " DATE(L.datastatus) BETWEEN '".$tm->data11."' AND '".$tm->data22."' ";
} else {
   
   $tm->opc=='mes' ? $datastatus = " YEAR(L.datastatus)=YEAR(NOW()) AND  MONTH(L.datastatus)=MONTH(NOW()) ":null;
   $tm->opc=='dia' ? $datastatus = " DATE(L.datastatus)=DATE(NOW()) ":null;
}


$resp = array();

switch ($dt->sts) {
    case 'all' :
        $sts = "";
        break;
    case 'natn' :
        $sts = " AND L.status=6 ";
        break;
    case 'nul' :
        $sts = " AND L.status IN(3,4,5) ";
        break;
    case 'agdoc' :
        $sts = " AND L.status=8 ";
        $sel='DATE';
        $sel2='<=NOW()';
        break; 
    case 'apv' :
        $sts = " AND L.status=16 AND F.status=6 ";
        break;
    case 'fin' :
        $sts = " AND L.status=17 AND F.status=7 ";
        break;
    case 'rjt' :
        $sts = " AND L.status IN(15,19) ";
        break;
    case 'anl' :
        $sts = " AND L.status>=10 ";
        break;    
    default:
        break;
}


$query = sprintf("SELECT L.id, L.nome,L.telefone,L.email,L.montante AS valorpretendido,S.nome AS status,L.datastatus,"
        . " U.nome AS gestor, U2.nome AS analista, F.montante "
        . " FROM arq_leads L LEFT JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
        . " LEFT JOIN cad_financiamentos F ON F.lead=L.id  "
        . " WHERE ".$datastatus.$sts."  AND L.user=%s",$dt->id);
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}