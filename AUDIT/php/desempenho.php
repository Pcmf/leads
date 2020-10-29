<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';
$tm = json_decode(file_get_contents("php://input"));



if($tm->opc=='dia'){
    $sel=' DATE(L.datastatus) = DATE(NOW()) ';
    $selH=' DATE(data) = DATE(NOW()) ';
    $selFin = ' DATE(F.datafinanciado) = DATE(NOW()) ';
}
   
if($tm->opc=='mes'){
        $sel=' YEAR(L.datastatus) = YEAR(NOW()) AND MONTH(L.datastatus) = MONTH(NOW())';
        $selH=' YEAR(data) = YEAR(NOW()) AND MONTH(data) = MONTH(NOW())';
        $selFin=' YEAR(F.datafinanciado) = YEAR(NOW()) AND MONTH(F.datafinanciado) = MONTH(NOW())';
}

if($tm->opc==''){
    $sel = " L.datastatus BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
    $selH = " data BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
    $selFin = " F.datafinanciado BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
}


$resp = array();


// Obter a lista dos gestores ativos
$result = mysqli_query($con, "SELECT id, nome, tipo FROM cad_utilizadores WHERE tipo='Gestor' AND ativo=1 AND nome!='João Pereira' ORDER BY nome");
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $temp = array();
        //Obter as leads puxadas pelo Gestor
        $query = sprintf("SELECT count(DISTINCT(lead)) AS puxadas FROM arq_histprocess "
                . " WHERE user=%s AND status=2 AND %s", $row['id'], $selH);
        $resultPG = mysqli_query($con, $query);
        $row['puxadas'] = mysqli_fetch_array($resultPG, MYSQLI_ASSOC)['puxadas']*1;
        
        //Obter as financiadas Qty e valor
        $query1 = sprintf("SELECT count(F.lead) AS qty, SUM(F.montante) AS valor FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE L.user=%s AND F.status=7 AND %s ", $row['id'], $selFin);
        $resultFG = mysqli_query($con, $query1);
        $rowFG = mysqli_fetch_array($resultFG, MYSQLI_ASSOC);
        $row['financiadas'] = $rowFG['qty']*1;
        $row['valor'] = $rowFG['valor']*1;
        
        //Obter as desistencias Qty e valor
        $query2 = sprintf("SELECT count(DISTINCT(L.id)) AS qty, SUM(F.montante) AS valor FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE L.user=%s AND L.status=18 AND %s ", $row['id'], $sel);
        $resultDG = mysqli_query($con, $query2);
        if($resultDG){
            $rowDG = mysqli_fetch_array($resultDG, MYSQLI_ASSOC);
            $row['desistencias'] = $rowDG['qty']*1;
            $row['valorDesistencia'] = $rowDG['valor']*1;
        }        
        array_push($resp, $row);
    }
 
    // Obter a lista dos Analistas ativos
$result = mysqli_query($con, "SELECT id, nome, tipo FROM cad_utilizadores WHERE tipo='Analista' AND ativo=1 AND nome!='João Pereira'  ORDER BY nome");
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $temp = array();
        //Obter as leads puxadas pelo Gestor
        $query = sprintf("SELECT count(DISTINCT(lead)) AS puxadas FROM arq_histprocess "
                . " WHERE analista=%s AND status=12 AND %s", $row['id'], $selH);
        $resultPG = mysqli_query($con, $query);
        $row['puxadas'] = mysqli_fetch_array($resultPG, MYSQLI_ASSOC)['puxadas']*1;
        
        //Obter as financiadas Qty e valor
        $query1 = sprintf("SELECT count(F.lead) AS qty, SUM(F.montante) AS valor FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE L.analista=%s AND F.status=7 AND %s ", $row['id'], $selFin);
        $resultFG = mysqli_query($con, $query1);
        $rowFG = mysqli_fetch_array($resultFG, MYSQLI_ASSOC);
        $row['financiadas'] = $rowFG['qty']*1;
        
        $row['valor'] = $rowFG['valor']*1;
        
        //Obter as desistencias Qty e valor
        $query2 = sprintf("SELECT count(DISTINCT(F.lead)) AS qty, SUM(F.montante) AS valor FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE L.analista=%s AND L.status=18 AND %s ", $row['id'], $sel);
        $resultDG = mysqli_query($con, $query2);
        if($resultDG){
            $rowDG = mysqli_fetch_array($resultDG, MYSQLI_ASSOC);
            $row['desistencias'] = $rowDG['qty']*1;
            $row['valorDesistencia'] = $rowDG['valor']*1;   
        }
        array_push($resp, $row);
    }
    
    
    
    echo json_encode($resp);


