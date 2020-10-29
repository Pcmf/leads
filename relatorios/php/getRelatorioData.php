<?php

/* 
 * Retorna os dados para o relatorio dos gestores
 */

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


$resp = array();

if($dt->params->allGestores){
    $userSelect = null;
} else {
    $userSelect = ' L.user='.$dt->params->gestor.' AND ';
}

if(isset($dt->tml->opc) && $dt->tml->opc =='mes'){
    $periodo = " MONTH(datastatus) = MONTH(NOW()) ";
}
if(isset($dt->tml->opc) && $dt->tml->opc =='dia'){
    $periodo = " DATE(datastatus) = DATE(NOW()) ";
}

if(!$dt->data2){
    $dt->data2 = $dt->data1;
}

if($dt->data1){
    $periodo = " DATE(datastatus) >= '".$dt->data1."' AND DATE(datastatus) <= '".$dt->data2."' ";
}


//ANULADAS
$query = sprintf("select L.id, L.status,R.motivo, C.duracao, C.data AS dataChamada, L.datastatus AS dataStatus  "
        . " FROM arq_leads L "
        . " LEFT JOIN cad_registochamadas C ON C.telefone=L.telefone "
        . " LEFT JOIN cad_rejeicoes R ON R.lead=L.id "
        . " WHERE ".$userSelect."  L.status IN(3,4) AND ".$periodo." GROUP BY L.id  ORDER BY C.duracao,L.status, L.id, L.datastatus ");

$result = mysqli_query($con, $query);
if($result){
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['anuladas']=$temp;
}


//AGENDADAS - Não atendidas
if(isset($dt->tml->opc) && $dt->tml->opc =='mes'){
    $periodo = " MONTH(C.data) = MONTH(NOW()) ";
}
if(isset($dt->tml->opc) && $dt->tml->opc =='dia'){
    $periodo = " DATE(C.data) = DATE(NOW()) ";
}
if(!$dt->data2){
    $dt->data2 = $dt->data1;
}

if($dt->data1){
    $periodo = " DATE(C.data) >= '".$dt->data1."' AND DATE(C.data) <= '".$dt->data2."' ";
}

$query = sprintf("SELECT L.id,C.id AS cid, C.data,C.duracao,C.telefone FROM `cad_agenda` A "
        . " INNER JOIN arq_leads L ON L.id=A.lead "
        . " INNER JOIN cad_registochamadas C ON C.telefone=L.telefone "
        . " WHERE ".$userSelect." C.sentido='OUT' AND C.duracao<40 AND ".$periodo
        . " GROUP BY C.id ORDER BY L.id, C.data ");

$result = mysqli_query($con, $query);
if($result){
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['agendadas']=$temp;
}


echo json_encode($resp);