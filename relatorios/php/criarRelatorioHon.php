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
$totalaprovado = 0;
$totalfinanciado = 0;
$totalhonorarios =0;
$totaldesistiu = 0;
$totalanulado = 0;
$numProcessos = 0;
$numAprovados =0;
$numFinanciados =0;
$numDesistiu =0;
$numAnulados =0;

if(!$dt->sl->data22){
    $dt->sl->data22 = $dt->sl->data11;
}
//fornecedor
if($dt->sl->fornSel == -1){
    $selForn = "";
} else {
    $selForn = " L.fornecedor = ".$dt->sl->fornSel." AND ";
}
//Colaborador

if(!$dt->sl->colabSel){
    $selColab = "";
} else {
    $dt->sl->colabSel->tipo=='Gestor' || $dt->sl->colabSel->tipo=='GExterno' ||  $dt->sl->colabSel->tipo=='COFD' ? 
            $selColab = " L.user = ".$dt->sl->colabSel->id." AND "
            :  $selColab = " L.analista = ".$dt->sl->colabSel->id." AND "  ;
}
//Parceiro
if($dt->sl->parceiroSel == -1){
    $selParceiro = "";
} else {
    $selParceiro = " F.parceiro = ".$dt->sl->parceiroSel." AND ";
}

//Tipo de Credito
if($dt->sl->tipocredito == -1){
    $selTipo = "";
    $selTipoL = "";
} else {
    $selTipo = " F.tipocredito = '".$dt->sl->tipocredito."' AND ";
    $selTipoL = " L.tipocredito = '".$dt->sl->tipocredito."' AND ";
}

//Query dos processos que entraram no mesmo periodo e para o mesmo fornecedor e utilizador
$query1 = sprintf("SELECT count(*) "
        . " FROM arq_leads L WHERE ".$selColab.$selForn.$selTipoL." DATE(L.dataentrada) >= '".$dt->sl->data11."' AND DATE(L.dataentrada) <= '".$dt->sl->data22."' ");
//echo $query1;
$result1 = mysqli_query($con, $query1);
if($result1){
    $row1 = mysqli_fetch_array($result1,MYSQLI_NUM);
}

//Query do financiamentos
$query = sprintf("SELECT L.id, L.dataentrada, FL.nome AS fornecedor, U1.nome AS gestor, U2.nome AS analista, SL.nome AS status, F.montante, F.dataaprovado, F.prazo,  "
        ." F.datafinanciado, P.nome AS parceiro, F.tipocredito, L.datastatus, F.parceiro AS parc, L.status AS statuslead, F.status AS statusfinanc, P.percentagem, P.usaformula, P.formula "
        ." FROM `arq_leads` L "
        ." INNER JOIN cad_fornecedorleads FL ON FL.id=L.fornecedor "
        ." INNER JOIN cad_utilizadores U1 ON U1.id=L.user "
        ." INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
        ." INNER JOIN cad_financiamentos F ON F.lead=L.id "
        ." INNER  JOIN cnf_statuslead SL ON SL.id=L.status "
        ." LEFT JOIN cad_parceiros P ON P.id=F.parceiro "
        ." WHERE ".$selForn.$selColab.$selParceiro.$selTipo." (F.status IN(6,11) AND DATE(L.datastatus) >= '".$dt->sl->data11."' AND  (DATE(L.datastatus) <= '".$dt->sl->data22."')"
        . " OR (F.status=7 AND  DATE(F.datafinanciado) >= '".$dt->sl->data11."' AND DATE(F.datafinanciado) <= '".$dt->sl->data22."')"
        . " OR (F.status=12 AND  DATE(F.datastatus) >= '".$dt->sl->data11."' AND DATE(F.datastatus) <= '".$dt->sl->data22."'))"
        . " ORDER BY L.datastatus " );
//echo $query;
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $row['honorarios'] = 0;
        //Aprovados
        if($row['statuslead']==16){
            $totalaprovado += $row['montante'];
            $numAprovados++;
        }
        //Financiados
        if($row['statuslead']==17 || $row['statuslead']==23 || $row['statuslead']==24 || $row['statuslead']==25 ){
            $totalfinanciado += $row['montante'];
            $numFinanciados++;
            if( $row['usaformula']==0) {
                 $row['honorarios'] = $row['percentagem']/100 * $row['montante'];
           
            } else if ($row['parc'] == 3){  //Credibom
                $percentagem = 1;
                if ($row['prazo'] == 18){ $percentagem = 2;  }
                if ($row['prazo'] >= 24 && $row['prazo'] <= 36){ $percentagem = 3;  }
                if ($row['prazo'] >= 48 && $row['prazo'] <= 84){ $percentagem = 4;  }

                $row['honorarios'] = $percentagem/100 * $row['montante'];
                
           }  else if ($row['parc'] == 7){  //Unicre
               if($row['tipocredito'] == 'CP'){
                    $percentagem = 3;
                    if ($row['prazo'] >= 48 && $row['prazo'] <= 60){ $percentagem = 3.5;  }
                    if ($row['prazo'] >=72 && $row['prazo'] <= 84){ $percentagem = 4;  }
               } else {
                   $percentagem = 6;
               }
                $row['honorarios'] = $percentagem/100 * $row['montante'];
                
           } 
           
            $totalhonorarios += $row['honorarios'];
        }      
        //Total Desistiu
        if($row['statuslead'] == 18){
            $totaldesistiu += $row['montante'];
            $numDesistiu++;
        }
        //Total Anulado -- recusado apos financiamento
        
        $lista = array(14,15,19);
        if(in_array( $row['statuslead'], $lista )){
            $totalanulado += $row['montante'];
            $numAnulados++;
        }        
        array_push($resp, $row);
    }
    $resp['resultados'] = $resp;
    $resp['entradas'] = $row1[0];
    $resp['totalaprovado'] = $totalaprovado;
    $resp['totalfinanciado'] = $totalfinanciado;
    $resp['totaldesistiu'] = $totaldesistiu;
    $resp['totalanulado'] = $totalanulado;
    $resp['numAprovado'] = $numAprovados;
    $resp['numFinanciado'] = $numFinanciados;
    $resp['numDesistiu'] = $numDesistiu;
    $resp['numAnulado'] = $numAnulados;    
    $resp['totalhonorarios'] = $totalhonorarios;
    
}

//Calcular total submetido
$query = "SELECT sum(P.valorpretendido)  FROM arq_processo P "
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " WHERE  ".$selForn.$selColab.$selParceiro.$selTipoL." P.lead IN( "
   ." SELECT distinct(lead) FROM arq_histprocess WHERE status=13 and data BETWEEN '".$dt->sl->data11."'  AND '".$dt->sl->data22."' )";

$result2 = mysqli_query($con, $query);
if($result2){
    $row = mysqli_fetch_array($result2, MYSQLI_NUM);
}
$resp['submetidos'] = $row[0];

//Calcular total APROVADO
$query = "SELECT sum(F.montante)  FROM arq_processo P "
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " INNER JOIN cad_financiamentos F ON F.lead=P.lead"
        . " WHERE  ".$selForn.$selColab.$selParceiro.$selTipo."  F.status IN(6,7) AND P.lead IN( "
   ." SELECT distinct(lead) FROM arq_histprocess WHERE status=16 and data BETWEEN '".$dt->sl->data11."'  AND '".$dt->sl->data22."' )";

$result2 = mysqli_query($con, $query);
if($result2){
    $row = mysqli_fetch_array($result2, MYSQLI_NUM);
}
$resp['aprovado'] = $row[0];

//echo json_encode($honorarios);
echo json_encode($resp);