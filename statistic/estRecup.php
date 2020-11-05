<?php

/* 
 * Obter as quantidades distribuidas por fornecedor e pelos respectivos estados 
 * filtradas pelas opções de datas
 * Estados: 
 * - recebidas: todas com data de entrada dentro dos parametros de seleção de data
 * - não atribuidos: status=103 com datastatus dentro dos parametros
 * - anuladas: status 104 e 105
 * - aguardam documentação: status 108 e 21
 * - para analise: status 10,11
 * - pendentes: status 12,13,20,22
 * - aprovados: status 16 e  cad_financiamentos status=6
 * - financiados: status 17 e  cad_financiamentos status=7
 * - rejeitados: status 14
 * - não aprovados: status 15,19
 * - desistencias: status 18
 */
require_once '../php/openCon.php';
require_once '../class/configs.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);


//criar filtros 
if(isset($dt->data11)){
    !isset($dt->data22) ? $dt->data22= $dt->data11:null;
    $data = " DATE(data) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
    $datastatus = " DATE(L.datastatus) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
} else {
   
   $dt->opc=='mes' ? $data = " MONTH(data)=MONTH(NOW())  AND YEAR(data) = YEAR(NOW()) ":null;
   $dt->opc=='mes' ? $datastatus = " MONTH(L.datastatus)= MONTH(NOW()) AND YEAR(L.datastatus) = YEAR(NOW()) ":null;

    $dt->opc=='dia' ? $data = " DATE(data)=DATE(NOW()) ":null;
  $dt->opc=='dia' ? $datastatus = " DATE(L.datastatus)=DATE(NOW()) ":null;
}

//Todas que entraram no periodo selecionado
$total = 0;
$temp = array();
$temp2 = array();

//Selecionar o que  está para recuperação
$resp['inrecup'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_recuperacao WHERE ".$data );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['inrecup'] =$row[0];
}

//Selecionar o que  está para recuperação
$resp['pararecup'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_recuperacao WHERE statusrec = 1 " );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['pararecup'] =$row[0];
}

//Selecionar o que não atenderam a 2ª chamada e que foram anulados automaticamente
$resp['naoatendidos'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads L WHERE L.status=103 AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['naoatendidos'] =$row[0];
}
 
//Selecionar o que não estavam interessados e foram anulados pelo gestor de recuperação
$resp['naointeressados'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads L WHERE L.status=104 AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['naointeressados'] =$row[0];
}

//Selecionar os recuperados
$resp['recuperados'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads L WHERE L.status=108 AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['recuperados'] =$row[0];
}


//Selecionar os Anulados por falta de documentação
$resp['afd'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads  L"
        . " WHERE  L.status=109 AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['afd'] =$row[0];
}

//Selecionar os que passaram para analise
$resp['analise'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads L "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE U.tipo='GRec' AND L.status>=10 and L.status<=25 AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['analise'] =$row[0];
}

//Selecionar os que foram rejeitados ou recusados na  analise
$resp['rejrec'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads L "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE U.tipo='GRec' AND L.status IN(14,15,18,19,25) AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['rejrec'] =$row[0];
}

//Selecionar os que foram Financiados
$resp['financiados'] = 0;
$result = mysqli_query($con, "SELECT count(*) FROM arq_leads L "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE U.tipo='GRec' AND L.status IN(17,23,24) AND ".$datastatus );

if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['financiados'] =$row[0];
}

//Selecionar o valor dos que foram Financiados
$resp['valorfin'] = 0;
$result = mysqli_query($con, "SELECT sum(F.montante) FROM arq_leads L "
        . " INNER JOIN cad_financiamentos F ON F.lead = L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE U.tipo='GRec' AND  L.status IN(17, 24) AND F.status=7 AND ".$datastatus );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $resp['valorfin'] =$row[0];
}

echo json_encode($resp);
        
