<?php

/* 
 * Obter as quantidades distribuidas por fornecedor e pelos respectivos estados 
 * filtradas pelas opções de datas
 * Estados: 
 * - recebidas: todas com data de entrada dentro dos parametros de seleção de data
 * - não atribuidos: status=3 com datastatus dentro dos parametros
 * - anuladas: status 4 e 5
 * - aguardam documentação: status 8 e 21
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
    $dataentrada = " DATE(dataentrada) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
    $datastatus = " DATE(L.datastatus) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
    $datafin = " DATE(F.datafinanciado) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
} else {
   
   $dt->opc=='mes' ? $dataentrada = " YEAR(dataentrada)=YEAR(NOW()) AND MONTH(dataentrada)=MONTH(NOW()) ":null;
   $dt->opc=='mes' ? $datastatus = " YEAR(dataentrada)=YEAR(NOW()) AND MONTH(L.datastatus)=MONTH(NOW()) ":null;
   $dt->opc=='mes' ? $datafin = " MONTH(F.datafinanciado)=MONTH(NOW())  AND YEAR(F.datafinanciado)= YEAR(NOW()) ":null;
   $dt->opc=='dia' ? $dataentrada = " DATE(dataentrada)=DATE(NOW()) ":null;
   $dt->opc=='dia' ? $datastatus = " DATE(L.datastatus)=DATE(NOW()) ":null;
   $dt->opc=='dia' ? $datafin = " DATE(F.datafinanciado)=DATE(NOW()) ":null;
}

//Todas que entraram no periodo selecionado
$total = 0;
$temp = array();
$temp2 = array();
$query = "SELECT F.nome, L.fornecedor AS id, COUNT(*) AS qty FROM arq_leads L"
        . " INNER JOIN cad_fornecedorleads F ON F.id=L.fornecedor"
        . " WHERE ".$dataentrada." OR ".$datastatus." GROUP BY F.nome";
$result = mysqli_query($con,$query);
if($result){
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){
        $total += $row['qty'];
        $temp['id'] = $row['id'];
        $temp['ForNome'] = $row['nome'];
        //Recebidas
        $queryNV = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$dataentrada." AND L.fornecedor =%s "
                ,$row['id']);
        $result0 = mysqli_query($con, $queryNV);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['NV']=$row0[0];
        }        
        //Não atribuidos
        $queryNATRB = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$datastatus." AND L.fornecedor =%s "
                . " AND L.status IN(".NATRB.")",$row['id']);
        $result0 = mysqli_query($con, $queryNATRB);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['NATRB']=$row0[0];
        }
        //Anuladas
        $queryANUL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$datastatus." AND L.fornecedor =%s "
                . " AND L.status IN(".ANULGST.",".NATND.")",$row['id']);
        $result0 = mysqli_query($con, $queryANUL);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['ANUL']=$row0[0];
        }
        //recusada no analista
        $queryRECANL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$datastatus." AND L.fornecedor =%s "
                . " AND L.status IN(".RECANL.")",$row['id']);
        $result0 = mysqli_query($con, $queryRECANL);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['RECANL']=$row0[0];
        }
         //para analise - ainda não foram puxadas
        $queryPARANL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE  L.fornecedor =%s "
                . " AND L.status IN(10,11)",$row['id']);
        $result0 = mysqli_query($con, $queryPARANL);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['PARANL']=$row0[0];
        }           
        //pendentes na analise - conta todas
        $queryPNDANL = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE  L.fornecedor =%s "
                . " AND L.status IN(".PNDANL.")",$row['id']);
        $result0 = mysqli_query($con, $queryPNDANL);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['PNDANL']=$row0[0];
        }        
        //Aprovados
        $queryAPRV = sprintf("SELECT  count(*) AS qty FROM `arq_leads` L "
                . "  WHERE ".$datastatus." AND L.fornecedor =%s "
                . " AND L.status='".APRV."' ",$row['id']);
        $result0 = mysqli_query($con, $queryAPRV);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp['APRV']=$row0[0];
        }        
        //Finaciados
        $queryFNC = sprintf("SELECT  count(*) AS qty , SUM(F.montante) AS valor"
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . "  WHERE ".$datafin." AND L.fornecedor =%s "
                . " AND F.status=7 ",$row['id']);
//        echo $queryFNC;
        $result0 = mysqli_query($con, $queryFNC);
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);
            $temp['FNC']=$row0;
        }           
        array_push($temp2,$temp);
    }
    $resp['totalRecebidas'] = $total;
    $resp['byFornecedor'] = $temp2;
    echo json_encode($resp);
} else {
    echo $query;
}
