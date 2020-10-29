<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../php/openCon.php';
require_once '../class/configs.php';
$json = file_get_contents("php://input");
$dt = $json;

if($dt=='hoje'){
    $sel='DATE';
    $sel2 = 'DATE(NOW())';
}

if($dt=='semana'){
    $sel='WEEK';
    $sel2 = 'WEEK(NOW())';
}    
if($dt=='mes'){
    $sel='MONTH';
    $sel2 = 'MONTH(NOW())';
}
if($dt=='mespassado'){
    $sel='MONTH';
    $sel2 = '(MONTH(NOW())-1)';
}
    
$resp = array();
//Todas que entraram no periodo selecionado
$total = 0;
$temp = array();
$temp2 = array();
$result = mysqli_query($con,"SELECT F.nome, L.fornecedor AS id, COUNT(*) AS qty FROM arq_leads L"
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor"
        . " WHERE ".$sel."(dataentrada)=".$sel2." GROUP BY F.nome");
if($result){
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){
        $total += $row['qty'];
        $temp['id'] = $row['id'];
        $temp['ForNome'] = $row['nome'];
        //Recebidas
        $queryNV = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$sel."(L.dataentrada)=".$sel2." AND L.fornecedor =%s "
                ,$row['id']);
        $result0 = mysqli_query($con, $queryNV);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['NV']=$row0[0];
        }        
        //Não atribuidos
        $queryNATRB = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$sel."(L.datastatus)=".$sel2." AND L.fornecedor =%s "
                . " AND L.status IN(".NATRB.")",$row['id']);
        $result0 = mysqli_query($con, $queryNATRB);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['NATRB']=$row0[0];
        }
        //Anuladas
        $queryANUL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$sel."(L.datastatus)=".$sel2." AND L.fornecedor =%s "
                . " AND L.status IN(".ANULGST.",".NATND.")",$row['id']);
        $result0 = mysqli_query($con, $queryANUL);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['ANUL']=$row0[0];
        }
        //recusada no analista
        $queryRECANL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$sel."(L.datastatus)=".$sel2." AND L.fornecedor =%s "
                . " AND L.status IN(".RECANL.")",$row['id']);
        $result0 = mysqli_query($con, $queryRECANL);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['RECANL']=$row0[0];
        }
        
        array_push($temp2,$temp);
    }
    $resp['totalRecebidas'] = $total;
    $resp['byFornecedor'] = $temp2;
}