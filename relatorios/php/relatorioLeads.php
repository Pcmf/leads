<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

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

$resp = array();
    //Obter as recebidas
// echo "SELECT count(*) AS qty FROM arq_leads  WHERE ".$dataentrada.$fornecedor;
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads  WHERE ".$dataentrada.$fornecedor );
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['recebidas'] = $row['qty'];
    }

    //Obter as que foram puxadas 
//    $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads  WHERE status=5 AND ".$dataentrada.$fornecedor.$gestor);
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM cad_registocontacto C INNER JOIN arq_leads L ON L.id=C.lead  WHERE C.motivocontacto=0 AND ".$dataC.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['puxadas'] = $row['qty'];
    }
    //Obter as não atribuidos
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM  arq_leads L WHERE L.status=3 AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['naoAtribuidas'] = $row['qty'];
    } 
    
    //Obter dados Anuladas
    $query = "SELECT count(*) AS qty, R.motivo AS Motivo "
                            ." FROM cad_rejeicoes R "
                            ." INNER JOIN arq_leads L ON L.id=R.lead "
                            ." WHERE  L.status=4 AND ".$datastatus.$fornecedorL.$gestorL
                            ." GROUP BY  R.motivo ";

    $result = mysqli_query($con, $query);
    if ($result) {
        $temp = array();
        $total = 0;
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($temp, $row);
            $total += $row['qty'];
        }
        $resp['anuladas'] = $temp;
        $resp['totalAnuladas'] = $total;
    }
        
    //Obter as canceladas por excesso de tempo - não atendidas
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=5 AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['cancExcTmp'] = $row['qty'];
    }
    //Obter as canceladas por não receberem documentação
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM  arq_leads L WHERE L.status=9 AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['anulaFaltaDoc'] = $row['qty'];
    }    
    //Obter as que Agendadas 6
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=6 AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['agendadas6'] = $row['qty'];
    } 
    //Obter as que Agendadas 7
    $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=7 AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['agendadas7'] = $row['qty'];
    }      
    //Obter as que passaram para analise
    $result = mysqli_query($con, "SELECT count(DISTINCT H.lead ) AS qty, SUM(P.valorpretendido) AS valor "
            . " FROM arq_histprocess H "
            . " INNER JOIN arq_leads L ON L.id=H.lead "
            . " INNER JOIN arq_processo P ON P.lead=H.lead "
            . " WHERE H.status IN(10,11,20) AND ".$dataH.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['paraAnalise'] = $row;
    } 
    //Obter as que estão para analise - toAnalise
    $result = mysqli_query($con, "SELECT count(*) AS qty, SUM(P.valorpretendido) AS valor "
            . " FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            ." WHERE L.status IN(10,11,20) AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['toAnalise'] = $row;
    }
    //Obter as que estão em analise
    $result = mysqli_query($con, "SELECT count(*) AS qty, SUM(P.valorpretendido) AS valor "
            . " FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            ." WHERE L.status IN(12,13,21,22) AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['emAnalise'] = $row;
    }
        //Obter as Recusadas ou não aprovadas na analise
    $result = mysqli_query($con, "SELECT count(DISTINCT H.lead) AS qty , SUM(P.valorpretendido) AS valor "
            . " FROM arq_histprocess H "
            . " INNER JOIN arq_leads L ON L.id=H.lead"
            . " INNER JOIN arq_processo P ON P.lead=H.lead"
            . " WHERE H.status IN(14,15,19) AND ".$dataH.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['recNAproAnalise'] = $row;
    }
    //Obter as que Aguardam Documentação
   $result = mysqli_query($con, "SELECT count(*) AS qty , SUM(P.valorpretendido) AS valor "
           . " FROM arq_leads L "
           . " INNER JOIN arq_processo P ON P.lead=L.id "
           . " WHERE L.status=8 AND ".$datastatus.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['aguardamDoc'] = $row;
    }  
    
    //Obter as que foram aprovadas no periodo
    $result = mysqli_query($con, "SELECT count(DISTINCT H.lead) AS qty "
            . " FROM arq_histprocess  H "
            . " INNER JOIN arq_leads L ON L.id=H.lead "
//            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " WHERE  H.status=16  AND ".$dataH.$fornecedorL.$gestorL); 
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['aprovadas']['qty'] = $row['qty'];
    }       
    
      $result = mysqli_query($con, "SELECT sum(F.montante) AS valor"
                . " FROM ( SELECT distinct( H.lead) AS lead  FROM arq_histprocess  H "
            . " INNER JOIN arq_leads L ON L.id=H.lead "
            . " WHERE  H.status=16 AND  ".$dataH.$fornecedorL.$gestorL.") A "
             . " INNER JOIN cad_financiamentos F ON F.lead=A.lead AND F.status IN (6,7) ");      
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['aprovadas']['valor'] = $row['valor'];
    }
    
    //Obter as que estão como aprovadas 
    $result = mysqli_query($con, "SELECT count(*) AS qty "
            . " FROM  arq_leads L  "
            . " WHERE  L.status=16 ".$fornecedorL.$gestorL); 
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['aprovadasPorFinanciar']['qty'] = $row['qty'];
    }       
    
      $result = mysqli_query($con, "SELECT sum(F.montante) AS valor "
                . " FROM ( SELECT  L.id  FROM arq_leads L  WHERE  L.status=16 ".$fornecedorL.$gestorL.") A "
             . " INNER JOIN cad_financiamentos F ON F.lead=A.id AND F.status=6 ORDER BY F.dataaprovado ");      
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['aprovadasPorFinanciar']['valor'] = $row['valor'];
    }     
    
    //Obter as desistencias
    $result = mysqli_query($con, "SELECT count(*) AS qty, SUM(P.valorpretendido) AS valor "
            . " FROM arq_leads L "
            . " INNER JOIN arq_processo P ON P.lead=L.id "
            . " WHERE L.status=18 AND ".$datastatus.$fornecedor.$gestor); 
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['desistencias'] = $row;
    }
    
    //Obter as financiadas e o valor
    $result = mysqli_query($con, "SELECT count(*) AS qty, sum(F.montante) AS valor "
            . " FROM arq_leads L "
            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " WHERE  L.status IN(17) AND F.status=7 AND ".$dataF.$fornecedorL.$gestorL);
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['financiadas'] = $row;
    }      
    
    //Obter as financiadas ACP e o valor
    $result = mysqli_query($con, "SELECT count(*) AS qty, sum(F.montante) AS valor FROM arq_leads L"
            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " WHERE  L.status IN(23,24) AND F.status=7 AND ".$dataF.$fornecedorL.$gestorL);                                   //AND MONTH(F.datafinanciado)=%s 
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['financiadasCP'] = $row;
    } 
    
    //Obter as anulados FCP e o valor
    $result = mysqli_query($con, "SELECT count(*) AS qty, sum(F.montante) AS valor FROM arq_leads L"
            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
            . " WHERE  L.status=25 AND F.status=12 AND ".$datastatus.$fornecedorL.$gestorL); 
    if($result){
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $resp['financiadasFCP'] = $row;
    }    
    
    

        
        
        
echo json_encode($resp);