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


if(isset($dt->opc) && $dt->opc ){
        if( $dt->opc=="dia"){
            $datasubmetido = ' DATE(F.datasubmetido)= DATE(NOW())'; 
            $datastatus = ' DATE(F.datastatus)= DATE(NOW())'; 
            $dataaprovado = ' DATE(F.dataaprovado)= DATE(NOW())'; 
            $datafinanciado = ' DATE(F.datafinanciado)= DATE(NOW())'; 
        } else {
            $datasubmetido = ' YEAR(F.datasubmetido)=YEAR(NOW()) AND MONTH(F.datasubmetido)=MONTH(NOW())';
            $datastatus = ' YEAR(F.datastatus)=YEAR(NOW()) AND MONTH(F.datastatus)=MONTH(NOW())';
            $dataaprovado = ' YEAR(F.dataaprovado)=YEAR(NOW()) AND MONTH(F.dataaprovado)=MONTH(NOW())';
            $datafinanciado = ' YEAR(F.datafinanciado)=YEAR(NOW()) AND MONTH(F.datafinanciado)=MONTH(NOW())';
        }
} else {
    $datasubmetido = " DATE(F.datasubmetido) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $datastatus = " DATE(F.datastatus) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $dataaprovado = " DATE(F.dataaprovado) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $datafinanciado = " DATE(F.datafinanciado) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
}


if($dt->status==13){
        //obter o submetidos por parceiro - CC
        $query = sprintf("SELECT L.id, L.dataentrada, P.nome, P.telefone, F.montante,"
                . " U.nome AS gestor, U2.nome AS analista, F.datasubmetido AS datastatus "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE F.parceiro=%s AND F.tipocredito='%s' AND ".$datasubmetido,
                $dt->parceiro, $dt->tipocredito);
} elseif ($dt->status==14) {
        $query = sprintf("SELECT L.id, L.dataentrada, P.nome, P.telefone, F.montante,"
                . " U.nome AS gestor, U2.nome AS analista, F.datastatus"
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE F.parceiro=%s AND F.tipocredito='%s' AND F.status IN(3,5) AND ".$datastatus,
                $dt->parceiro, $dt->tipocredito);
    
} elseif ($dt->status==16) {
        $query = sprintf("SELECT L.id, L.dataentrada, P.nome, P.telefone, F.montante,"
                . " U.nome AS gestor, U2.nome AS analista, F.dataaprovado  AS datastatus "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE F.parceiro=%s AND F.tipocredito='%s' AND ".$dataaprovado,
//                . "AND (".$dataaprovado." OR ".$datafinanciado
//                ." OR (".$datastatus." AND F.status IN(8,9,12)))",
                $dt->parceiro, $dt->tipocredito);
        
} elseif ($dt->status==17) {
        $query = sprintf("SELECT L.id, L.dataentrada, P.nome, P.telefone, F.montante,"
                . " U.nome AS gestor, U2.nome AS analista, F.datafinanciado AS datastatus"
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE F.parceiro=%s AND F.tipocredito='%s' AND F.status=7 AND ".$datafinanciado,
                $dt->parceiro, $dt->tipocredito);
} elseif ($dt->status==18) {
        $query = sprintf("SELECT L.id, L.dataentrada, P.nome, P.telefone, F.montante,"
                . " U.nome AS gestor, U2.nome AS analista, F.datastatus "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE F.parceiro=%s AND F.tipocredito='%s' AND F.status IN(8,9,12) AND ".$datastatus,
                $dt->parceiro, $dt->tipocredito);
}

//echo $query;
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
}