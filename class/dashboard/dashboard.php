<?php

/* 
 * Obter informações para o dashboard
 */

require_once '../php/openCon.php';

$resp = array();
//Se o dia da semana é segunda feira vai buscar desde 3 dias atrás
$numdias =1;
if(jddayofweek(0)==1){
    $numdias = 3;
} 


//Recebidos desde as 18:30 do dia anterior
$query = sprintf("SELECT count(*) FROM `arq_histprocess` WHERE status=1 AND ( (DATE(data)= DATE(SUBDATE(NOW(), %s)) "
        . " AND HOUR(data)>='18:30:00') OR (DATE(data)=DATE(NOW()) AND HOUR(data)<='18:29:59')), $numdias ");
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['recebidas'] = $row[0];
} else {
    $resp['recebidas'] =0;
}


//Por contactar
$query = "SELECT count(*) FROM arq_leads WHERE status=1 OR status=2 ";
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['porcontactar'] = $row[0];
} else {
    $resp['porcontactar'] =0;
}


//Clientes contactados no dia
$query = "SELECT count(*) FROM `cad_registochamadas` WHERE sentido='OUT' AND DATE(data)=DATE(NOW()) AND duracao>50";
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['contactados'] = $row[0];
} else {
    $resp['contactados'] =0;
}

//Passaram para analise
$query = sprintf("SELECT count(*) FROM `arq_histprocess` WHERE status=10 OR status=11 AND "
        . " DATE(data)=DATE(NOW()) ");
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['paraanalise'] = $row[0];
} else {
    $resp['paraanalise'] =0;
}


//não atendidas
$query = "SELECT count(*) FROM arq_leads WHERE status=3 OR status=6 AND DATE(datastatus) = DATE(NOW()) ";
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['naoatendidas'] = $row[0];
} else {
    $resp['naoatendidas'] =0;
}

//não atendidas
$query = "SELECT count(*) FROM arq_leads WHERE status=4 OR status=5 AND DATE(datastatus) = DATE(NOW()) ";
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['anuladas'] = $row[0];
} else {
    $resp['anuladas'] =0;
}

//Pedidos de documentação
$query = sprintf("SELECT count(*) FROM `arq_histprocess` WHERE status=8 AND DATE(data)=DATE(NOW()) ");
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['pedidodoc'] = $row[0];
} else {
    $resp['pedidodoc'] =0;
}

//Aprovados
$query = sprintf("SELECT count(*) FROM `arq_histprocess` WHERE status=16 AND DATE(data)=DATE(NOW()) ");
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['aprovados'] = $row[0];
} else {
    $resp['aprovados'] =0;
}

//Pedidos de documentação
$query = sprintf("SELECT count(*) FROM `arq_histprocess` WHERE status=17 AND DATE(data)=DATE(NOW()) ");
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['financiados'] = $row[0];
} else {
    $resp['financiados'] =0;
}

echo json_encode($resp);