<?php

/* 
 * Recebe como parametro o periodo
 */
require_once '../php/openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();

if($dt->sl->fornecedor==99){
    $fornecedor='';
} else {
    $dt->sl->fornecedor ? $fornecedor =" L.fornecedor=".$dt->sl->fornecedor." AND " : $fornecedor='';
}
$selecaoFin ='';
$selecao= '';
if(isset($dt->sl->opc) && $dt->sl->opc =='mes'){
    $selecao = ' YEAR(H.data)=YEAR(NOW()) AND MONTH(H.data) = MONTH(NOW()) '; 
    $selecaoEnt = ' YEAR(L.dataentrada) = YEAR(NOW()) AND MONTH(L.dataentrada) = MONTH(NOW()) '; 
    $selecaoFin = ' YEAR(F.datafinanciado) = YEAR(NOW()) AND MONTH(F.datafinanciado) = MONTH(NOW()) ';
} elseif (isset($dt->sl->opc) && $dt->sl->opc=='dia') {
    $selecao = ' DATE(H.data) = DATE(NOW()) ';
    $selecaoEnt = ' DATE(L.dataentrada) = DATE(NOW()) ';
    $selecaoFin = ' DATE(F.datafinanciado) = DATE(NOW()) ';    
} else {
    if(!$dt->sl->data22){
        $dt->sl->data22 = $dt->sl->data11;
    }   
    $selecao = "H.data  BETWEEN '".$dt->sl->data11." 00:00:00' AND '".$dt->sl->data22." 23:59:59' ";
    $selecaoEnt = "L.dataentrada  BETWEEN '".$dt->sl->data11." 00:00:00' AND '".$dt->sl->data22." 23:59:59' ";
    $selecaoFin = "F.datafinanciado  BETWEEN '".$dt->sl->data11." 00:00:00' AND '".$dt->sl->data22." 23:59:59' ";
}

//Recebidos no periodo
$query = "SELECT count(*) FROM arq_leads L WHERE ".$fornecedor." ".$selecaoEnt;
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['recebidos'] = $row[0];
}

//Aprovados no periodo
$query = "SELECT count(*), SUM(F.montante) FROM arq_histprocess H "
        . " INNER JOIN arq_leads L ON L.id=H.lead "
        . " INNER JOIN cad_financiamentos F ON F.lead=H.lead "
        . " WHERE ".$fornecedor." F.status=5 AND H.status=16 AND ".$selecao;
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['aprovados'] = $row[0];
    $resp['valorAprovado'] = $row[1];
}
    
//Financiados
$query = "SELECT count(*), SUM(F.montante) FROM cad_financiamentos F INNER JOIN arq_leads L ON L.id=F.lead "
        . " WHERE ".$fornecedor." F.status=7 AND ".$selecaoFin;
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['financiados'] = $row[0];
    $resp['valorFin'] = $row[1];
}

//Desistencias
$query = "SELECT count(*), SUM(F.montante) FROM arq_histprocess H "
        . " INNER JOIN arq_leads L ON L.id=H.lead "
        . " INNER JOIN cad_financiamentos F ON F.lead=H.lead "
        . " WHERE ".$fornecedor." F.status=10 AND H.status=18 AND ".$selecao;
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['desistencias'] = $row[0];
    $resp['valorDesistencias'] = $row[1];
}


//Obter financimentos por dia da semana
$temp = array();
$query = "SELECT weekday(dataentrada) AS dia, count(*) AS qty, SUM(F.montante) AS valor FROM arq_leads L "
        ." INNER JOIN cad_financiamentos F ON F.lead=L.id "
        ." WHERE ".$fornecedor." F.status=7 AND ".$selecaoFin." GROUP BY dia ";
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['semanal'] = $temp;
}

echo json_encode($resp);