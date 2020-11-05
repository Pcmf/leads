<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';

$json = file_get_contents("php://input");

$dt = json_decode($json);

$resp = array();


$dt->fornecedor==99 ? $fornecedor='' : $fornecedor=' AND fornecedor='.$dt->fornecedor;
$dt->fornecedor==99 ? $fornecedorL='' : $fornecedorL=' AND L.fornecedor='.$dt->fornecedor;
$dt->gestor==99 ? $gestor='' : $gestor=' AND user='.$dt->gestor;
$dt->gestor==99 ? $gestorL='' : $gestorL=' AND L.user='.$dt->gestor;

if(isset($dt->opc) && $dt->opc ){
        if( $dt->opc=="dia"){
            $dataentrada = ' DATE(dataentrada)= DATE(NOW())'; 
            $datastatus = ' DATE(L.datastatus)= DATE(NOW())'; 
            $dataH = ' DATE(H.data)= DATE(NOW())'; 
            $dataF = ' DATE(F.datafinanciado)= DATE(NOW())'; 
            $dataC = ' DATE(C.dtcontacto)= DATE(NOW())'; 
        } else {
            $dataentrada = ' YEAR(dataentrada)=YEAR(NOW()) AND MONTH(dataentrada)=MONTH(NOW())';
            $datastatus = ' YEAR(L.datastatus)=YEAR(NOW()) AND MONTH(L.datastatus)=MONTH(NOW())';
            $dataH = ' YEAR(H.data)=YEAR(NOW()) AND MONTH(H.data)=MONTH(NOW())';
            $dataF = ' YEAR(F.datafinanciado)=YEAR(NOW()) AND MONTH(F.datafinanciado)=MONTH(NOW())';
            $dataC = ' YEAR(C.dtcontacto)=YEAR(NOW()) AND MONTH(C.dtcontacto)=MONTH(NOW())';
        }
} else {
    $dataentrada = " DATE(dataentrada) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $datastatus = " DATE(L.datastatus) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $dataH = " DATE(H.data) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $dataF = " DATE(F.datafinanciado) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $dataC = " DATE(C.dtcontacto) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
}


if($dt->tipo==4){
    $query = "SELECT L.id, L.nome, L.telefone, L.montante, L.dataentrada, L.datastatus, U.nome AS gestor "
            . " FROM arq_leads L "
            . " INNER JOIN cad_rejeicoes R ON R.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " WHERE L.status=4 AND R.motivo LIKE '".$dt->motivo."' AND ".$datastatus.$fornecedorL.$gestorL;
} elseif ($dt->tipo==1) {
    $query = "SELECT L.id, L.nome, L.telefone, L.montante, L.dataentrada, L.datastatus, U.nome AS gestor "
            ." FROM arq_histprocess H "
            . " INNER JOIN arq_leads L ON L.id=H.lead"
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " WHERE H.status =1 AND ".$dataH.$fornecedorL.$gestorL." GROUP BY L.id ";
    
} elseif ($dt->tipo==10) {
    $query = "SELECT L.id, P.nome, P.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus, U.nome AS gestor, U1.nome AS analista "
            ." FROM arq_histprocess H "
            . " INNER JOIN arq_leads L ON L.id=H.lead "
            . " INNER JOIN arq_processo P ON P.lead=H.lead "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U1 ON U1.id=L.analista "
            . " WHERE H.status IN(10,11,20) AND ".$dataH.$fornecedorL.$gestorL." GROUP BY L.id ";
} elseif ($dt->tipo==11) {
    $query = "SELECT L.id, P.nome, P.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus, U.nome AS gestor "
            ." FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " WHERE L.status IN(10,11,20) AND ".$datastatus.$fornecedorL.$gestorL." GROUP BY L.id ";
} elseif ($dt->tipo==12) {
    $query = "SELECT L.id, P.nome, P.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus,"
            . " U.nome AS gestor, U2.nome AS analista "
            ." FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE L.status IN(12,13,21,22) AND ".$datastatus.$fornecedorL.$gestorL;
} elseif ($dt->tipo==14) {
    $query = "SELECT L.id, H.lead, P.telefone, P.nome, P.valorpretendido AS montante, L.dataentrada,"
            . " L.datastatus, U.nome AS gestor, U2.nome AS analista  "
            ." FROM arq_histprocess H "
            . " INNER JOIN arq_leads L ON L.id=H.lead "
            . " INNER JOIN arq_processo P ON P.lead=H.lead "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE H.status IN(14, 15, 19) AND ".$dataH.$fornecedorL.$gestorL." GROUP BY H.lead";
} elseif ($dt->tipo==16) {
    $query = "SELECT L.id, P.nome, P.telefone, F.montante, L.dataentrada, L.datastatus,"
            . " U.nome AS gestor, U2.nome AS analista  "
            . " FROM arq_histprocess  H "
            . " INNER JOIN arq_leads L ON L.id=H.lead "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE  H.status=16 AND F.status IN(6,7) AND ".$dataH.$fornecedorL.$gestorL." GROUP BY L.id";
} elseif ($dt->tipo==116) {
    $query = "SELECT L.id, P.nome, P.telefone, F.montante, L.dataentrada, L.datastatus,"
            . " U.nome AS gestor, U2.nome AS analista  "
            . " FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "            
            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE  L.status=16 AND F.status=6 ".$fornecedorL.$gestorL." GROUP BY L.id";
} elseif ($dt->tipo==17) {
    $query = "SELECT L.id, P.nome, P.telefone, F.montante, L.dataentrada, L.datastatus,"
            . " U.nome AS gestor, U2.nome AS analista  "
            . " FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE  L.status IN(17,23,24) AND F.status=7 AND ".$datastatus.$fornecedorL.$gestorL;
} else {
    $query = "SELECT L.id, L.nome, L.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus,"
            . " U.nome AS gestor, U2.nome AS analista  "
            . " FROM arq_leads L "
            . " LEFT JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE L.status=".$dt->tipo." AND ".$datastatus.$fornecedorL.$gestorL;
}

//echo $query;
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
}