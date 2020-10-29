<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../php/openCon.php';
$json = file_get_contents("php://input");
$tml = json_decode($json);


//criar filtros 
if(isset($tml->data11)){
    !isset($tml->data22) ? $tml->data22= $tml->data11:null;
    $datacontacto = " DATE(dtcontacto) BETWEEN '".$tml->data11." 00:00:00' AND '".$tml->data22." 23:59:59' ";
    $datastatus = " datastatus BETWEEN '".$tml->data11." 00:00:00' AND '".$tml->data22." 23:59:59' ";
    $data = " data BETWEEN '".$tml->data11." 00:00:00' AND '".$tml->data22." 23:59:59' ";
    $dataFin = " F.datafinanciado BETWEEN '".$tml->data11." 00:00:00' AND '".$tml->data22." 23:59:59' ";
} else {
   
   $tml->opc=='mes' ? $datacontacto = " YEAR(dtcontacto)=YEAR(NOW()) AND MONTH(dtcontacto)=MONTH(NOW()) ":null;
   $tml->opc=='dia' ? $datacontacto = " DATE(dtcontacto)=DATE(NOW()) ":null;
   $tml->opc=='mes' ? $datastatus = " YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW()) ":null;
   $tml->opc=='dia' ? $datastatus = " DATE(datastatus)=DATE(NOW()) ":null;
   $tml->opc=='mes' ? $data = " YEAR(data)=YEAR(NOW()) AND MONTH(data)=MONTH(NOW()) ":null;
   $tml->opc=='dia' ? $data = " DATE(data)=DATE(NOW()) ":null;   
   $tml->opc=='mes' ? $dataFin = " YEAR(F.datafinanciado)=YEAR(NOW()) AND MONTH(F.datafinanciado)=MONTH(NOW()) ":null;
   $tml->opc=='dia' ? $dataFin = " DATE(F.datafinanciado)=DATE(NOW()) ":null;   
}

$resp = array();
$temp1 =array();
//Selecionar Gestores
$result = mysqli_query($con, "SELECT id,nome FROM cad_utilizadores WHERE tipo='Analista' ORDER BY nome");
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $temp2 = array();
        $temp2['analista']= $row['nome'];
        $temp2['id']= $row['id'];
        
        //Obter os puxados por cada analista
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM cad_registocontacto "
                . " WHERE ".$datacontacto." AND motivocontacto=11 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['puxadas']=$row0[0];
        } else {
           $temp2['puxadas']=0; 
        }
      
        //Obter as PENDENTES 
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=13 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['pendentes']=$row0[0];
        } else {
           $temp2['pendentes']=0; 
        }   
        //Obter as que aguardam documentação
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=21 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['adoc']=$row0[0];
        } else {
           $temp2['adoc']=0; 
        }         
        //Obter as que estão anuladas
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=14 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['anulados']=$row0[0];
        } else {
           $temp2['anulados']=0; 
        } 
        //Obter as que foram aprovadas
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=16 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['aprovados']=$row0[0];
        } else {
           $temp2['aprovados']=0; 
        }         
        //Obter as que foram financiadas
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads L INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE ".$dataFin." AND F.status=7 AND L.analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['financiados']=$row0[0];
        } else {
           $temp2['financiados']=0; 
        }         
        //Obter as que estão aprovadas por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=16 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['aprovados']=$row0[0];
        } else {
           $temp2['aprovados']=0; 
        }

        //Obter as que estão rejeitados
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status IN(15,19) AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['rejeitados']=$row0[0];
        } else {
           $temp2['rejeitados']=0; 
        } 
        //Obter as desistencias
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=18 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['desistencias']=$row0[0];
        } else {
           $temp2['desistencias']=0; 
        }
        //Obter as que estão anulados por falta de comprovativos
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=25 AND analista=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['anuladosFCP']=$row0[0];
        } else {
           $temp2['anuladosFCP']=0; 
        } 
        
        array_push($temp1, $temp2);        
    }
    $resp['dados'] = $temp1;
    
            //Obter o total dos que entraram para analise no periodo
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_histprocess WHERE ".$data." AND status IN(10,11)"));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $resp['paraanalise']=$row0[0];
        } else {
           $resp['paraanalise']=0; 
        }  
            //Obter o total das leads que se encontram para analise
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads WHERE  status IN(10,11)"));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $resp['openparaanalise']=$row0[0];
        } else {
           $resp['openparaanalise']=0; 
        }  
        
}



echo json_encode($resp);