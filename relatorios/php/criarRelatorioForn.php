<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);

$resp = array();

if(!$dt->sl->data22){
    $dt->sl->data22 = $dt->sl->data11;
}

//Obter o total de leads por situação
//Recebidas - recebidas no periodo selecionado
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(dataentrada)  BETWEEN '%s' AND '%s' ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['recebidas'] = $row[0];
}


//Por contactar 
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=1 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['porcontactar'] = $row[0];
}

//Não Atribuidos 
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=3 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['naoatribuidas'] = $row[0];
}

//Anulados pelo gestor
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=4 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['anulgestor'] = $row[0];
}

//Anulados por excesso de tempo
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=5 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['anultempo'] = $row[0];
}

//Agendados automaticamente
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=6 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['agendaauto'] = $row[0];
}

//Agendados pelo Gestor
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=7 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['agendagestor'] = $row[0];
}

//Aguarda documentação
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status IN(8,21) ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['aguardadoc'] = $row[0];
}

//Passaram para analise
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status IN(10,11,12,20,22) ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['paraanalise'] = $row[0];
}

//Pendente - em analise pelo parceiro
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=13 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['pendente'] = $row[0];
}

//Recusada pelo analista
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=14 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['recusada'] = $row[0];
}

//Não aprovado por nenhum parceiro
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=15 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['naoaprovado'] = $row[0];
}

//Aprovados
$query = sprintf("SELECT count(*) AS QTY, SUM(F.montante) AS TOTAL FROM `cad_financiamentos` F INNER JOIN arq_leads L ON L.id=F.lead "
        . " WHERE DATE(F.datastatus)  BETWEEN '%s' AND '%s' AND F.status=6 AND L.fornecedor=%s", $dt->sl->data11, $dt->sl->data22, $dt->sl->fornSel->id);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $resp['aprovado'] = $row;
}

//Financiados
$query = sprintf("SELECT count(*) AS QTY, SUM(F.montante) AS TOTAL FROM `cad_financiamentos` F INNER JOIN arq_leads L ON L.id=F.lead "
        . " WHERE((DATE(F.datafinanciado)  BETWEEN '%s' AND '%s' AND F.status=7 AND L.status IN(17,23,24)) "
        . " OR (DATE(F.datastatus)  BETWEEN '%s' AND '%s' AND F.status=12 AND L.status=25)) "
        . " AND L.fornecedor=%s", $dt->sl->data11, $dt->sl->data22, $dt->sl->data11, $dt->sl->data22, $dt->sl->fornSel->id);
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $resp['financiado'] = $row;
}

//Desistiu
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=18 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['desistiu'] = $row[0];
}

//Recusado após financiamento
$query = sprintf("SELECT count(*) FROM arq_leads  WHERE fornecedor=%s AND DATE(datastatus)  BETWEEN '%s' AND '%s'  AND status=19 ", $dt->sl->fornSel->id, $dt->sl->data11, $dt->sl->data22);

$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['recaposfin'] = $row[0];
}

echo json_encode($resp);