<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
$dt = json_decode(file_get_contents("php://input"));

//prepara a seleção por tipo de credito
$dt->tipocredito == '' ? $tipocreditoL = '' : $tipocreditoL = " AND L.tipo='" . $dt->tipocredito . "'";
$dt->tipocredito == '' ? $tipocreditoFI = '' : $tipocreditoFI = " AND FI.tipocredito='" . $dt->tipocredito . "'";

//prepara seleção por fornecedor

if ($dt->fornSel == '') {
    $selectbyEmpresa = '';
} else {
    $selectbyEmpresa = " AND F.empresa='" . $dt->fornSel . "' ";
}

$resp = array();


//Entradas
$gid = array();
for ($i = 0; $i < 5; $i++) {
    $temp = array();
    $z=0;
    for ($k = $dt->mes1; $k <= $dt->mes2; $k++) {
        
        $query = sprintf("SELECT count(distinct(L.id)) AS QTY "
                . " FROM arq_leads L"
              //  . " LEFT JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
                . " WHERE YEAR(L.dataentrada)=%s AND MONTH(L.dataentrada)= %s "
                . " AND ( L.idade BETWEEN %s AND %s) "
                . " %s %s",
                $dt->ano, $k, substr($dt->gid[$i], 0, 2), substr($dt->gid[$i], -2), $selectbyEmpresa, $tipocreditoL);
        //    echo $query.'<br/>';
        $result = mysqli_query($con, $query);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $temp[$z] = $row['QTY'];
            $z++;
        }
    }
    $gid[$i] = $temp;
}
$resp['entradas'] = $gid;



// Quantidades financiadas por MES
$gid = array();
$gidV = array();
for ($i = 0; $i < 5; $i++) {
    $temp = array();
    $tempV = array();
    $z=0;
    for ($k = $dt->mes1; $k <= $dt->mes2; $k++) {

        $query = sprintf("SELECT COUNT(distinct(L.id)) AS QTY, SUM(FI.montante) AS VALOR"
                . " FROM `arq_leads` L "
                . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor "
                . " INNER JOIN cad_financiamentos FI ON FI.lead=L.id "
                . " LEFT JOIN arq_processo P ON P.lead=L.id "
                . " WHERE YEAR(L.dataentrada)=%s AND MONTH(L.dataentrada) = %s "
                . " AND P.idade BETWEEN %s AND %s AND FI.status=7 "
                . " %s %s ",
                $dt->ano, $k, substr($dt->gid[$i], 0, 2), substr($dt->gid[$i], -2), $selectbyEmpresa, $tipocreditoFI);
        $result = mysqli_query($con, $query);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            !$row['VALOR'] ? $row['VALOR'] = 0 : null;
            $temp[$z] = $row['QTY'];
            $tempV[$z] = $row['VALOR'];
            $z++;
        }
        $gid[$i] = $temp;
        $gidV[$i] = $tempV;
    }
}
$resp['financiadasQ'] = $gid;
$resp['financiadasV'] = $gidV;



echo json_encode($resp);
