<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
$dt = json_decode(file_get_contents("php://input"));

$resp = array();

if($dt->forn == 99) {
    $forn = "";
} else {
    $forn = ' FN.empresa = "'.$dt->forn.'" AND ';
}

!isset($dt->ano2) ? $dt->ano2=$dt->ano1 : null;
!isset($dt->mes2) ? $dt->mes2=$dt->mes1 : null;
!isset($dt->idade2) ? $dt->idade2=$dt->idade1 : null;
!isset($dt->vencimento2) ? $dt->vencimento2=$dt->vencimento1 : null;
!isset($dt->valorPedido2) ? $dt->valorPedido2=$dt->valorPedido1 : null;

if(isset($dt->financiado1)){
    !isset($dt->financiado2) ? $dt->financiado2=$dt->financiado1 : null;
    $financiado = " AND F.montante BETWEEN ".$dt->financiado1." AND ".$dt->financiado2." ";
} else {
    $financiado = "";
}


if($dt->titulares == 99) {
    $titulares = "";
} else {
    if($dt->titulares==2) {
        $titulares = " AND P.segundoproponente=1 ";
    } else {
        $titulares = " AND P.segundoproponente<>1 ";
    }
}

if($dt->tipoCredito == 99) {
    $tipoCredito = "";
} else {
    if($dt->tipoCredito=='CC') {
        $tipoCredito = " AND P.tipocredito ='CC' ";
    } else {
        $tipoCredito = " AND P.tipocredito ='CP' ";
    }
}

if(isset($dt->filtro) && $dt->filtro) {
    $filtro = " AND F.codigo = '".$dt->filtro."'";
} else {
    $filtro = "";
}


$query = sprintf("SELECT distinct(L.id), L.idleadorig, L.idade, (L.rendimento1 + L.rendimento2) AS vencimentos,"
        . " L.montante AS valorPedido, S.nome AS status, F.codigo "
        . " FROM arq_leads L "
        ." INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " INNER JOIN cad_fornecedorleads FN ON FN.id=L.fornecedor "
        . " LEFT JOIN cad_filtros F ON F.filtro=L.codigofiltro "
        . " WHERE %s  YEAR(L.dataentrada) BETWEEN %s AND %s AND MONTH(L.dataentrada) BETWEEN %s AND %s "
        . " AND L.idade BETWEEN %s AND %s AND (L.rendimento1 + L.rendimento2) BETWEEN %s AND %s "
        . " AND L.montante BETWEEN %s AND %s  %s %s %s", 
        $forn, $dt->ano1, $dt->ano2, $dt->mes1, $dt->mes2, $dt->idade1, $dt->idade2, $dt->vencimento1, $dt->vencimento2,
        $dt->valorPedido1, $dt->valorPedido2, $titulares, $tipoCredito, $filtro
        );
//echo $query;
$result = mysqli_query($con, $query);
if($result){
    $recebidas = 0;
    $qtyFin=0;
    $totalFin=0;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $recebidas++;
        if($financiado){
            $queryF = sprintf("SELECT montante FROM cad_financiamentos WHERE lead=%s AND status=7", $row['id'] );
          //  echo $queryF;
            $resultF = mysqli_query($con, $queryF);
            if($resultF){
                $rowF = mysqli_fetch_array($resultF, MYSQLI_ASSOC);
                $rowF['montante']>0 ? $qtyFin++: null;
                $totalFin +=$rowF['montante'];
                $row['valorFinanciado'] = $rowF['montante'];
            } else {
               $row['valorFinanciado'] =0; 
            }
        }
        array_push($resp, $row);
    }
    $resp['recebidas']=$recebidas;
    $resp['qtyFin']=$qtyFin;
    $resp['totalFin']=$totalFin;
    
    echo json_encode($resp);
}