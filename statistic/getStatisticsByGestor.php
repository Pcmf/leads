<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../php/openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);
//criar filtros 
if(isset($dt->data11)){
    !isset($dt->data22) ? $dt->data22= $dt->data11:null;
    $dtcontacto = " DATE(dtcontacto) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
    $datastatus = " DATE(datastatus) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
    $data = " DATE(data) BETWEEN '".$dt->data11."' AND '".$dt->data22."' ";
} else {
   
   $dt->opc=='mes' ? $dtcontacto = " YEAR(dtcontacto)=YEAR(NOW()) AND MONTH(dtcontacto)=MONTH(NOW()) ":null;
   $dt->opc=='mes' ? $datastatus = " YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus)=MONTH(NOW()) ":null;
   $dt->opc=='mes' ? $data = " YEAR(data)=YEAR(NOW()) AND MONTH(data)=MONTH(NOW()) ":null;
   $dt->opc=='dia' ? $dtcontacto  = " DATE(dtcontacto )=DATE(NOW()) ":null;
   $dt->opc=='dia' ? $datastatus = " DATE(datastatus)=DATE(NOW()) ":null;
   $dt->opc=='dia' ? $data = " DATE(data)=DATE(NOW()) ":null;
}

$resp = array();
//Selecionar Gestores
$result = mysqli_query($con, "SELECT id,nome FROM cad_utilizadores WHERE tipo='Gestor' ORDER BY nome");
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $temp2 = array();
        $temp2['gestor']= $row['nome'];
        $temp2['id']= $row['id'];
        
        //Obter os puxados por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM cad_registocontacto"
                . " WHERE ".$dtcontacto." AND contactonum=1 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['puxadas']=$row0[0];
        } else {
           $temp2['puxadas']=0; 
        }
        //Tentativas de contacto para cada gestor
//        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM cad_registocontacto"
//                . " WHERE ".$dtcontacto." AND motivocontacto=0 AND user=%s",$row['id']));
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_histprocess"
        . " WHERE ".$data." AND status=2 AND user=%s",$row['id']));
        $temp2['tentativas']=0; 
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['tentativas']=$row0[0];
        }      
        //Obter as não atendidas por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=6 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['naoatendidas']=$row0[0];
        } else {
           $temp2['naoatendidas']=0; 
        }        
        //Obter as que estão anuladas por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status IN(3,4,5) AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['anuladas']=$row0[0];
        } else {
           $temp2['anuladas']=0; 
        } 
        //Obter as que aguardam documentação por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE status=8 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['agdocs']=$row0[0];
        } else {
           $temp2['agdocs']=0; 
        }         
        //Obter as que estão na analise por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status>=10 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['analise']=$row0[0];
        } else {
           $temp2['analise']=0; 
        }         
        //Obter as que estão aprovadas por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=16 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['aprovados']=$row0[0];
        } else {
           $temp2['aprovados']=0; 
        }
        //Obter as que estão financiados por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status=17 AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['financiados']=$row0[0];
        } else {
           $temp2['financiados']=0; 
        }  
        //Obter as que estão rejeitados e desistencias por cada gestor
        $result0 = mysqli_query($con, sprintf("SELECT count(*) FROM arq_leads "
                . " WHERE ".$datastatus." AND status IN(14,15,18,19) AND user=%s",$row['id']));
        if($result0){
            $row0 = mysqli_fetch_array($result0,MYSQLI_NUM);
            $temp2['rejeitados']=$row0[0];
        } else {
           $temp2['rejeitados']=0; 
        } 
        array_push($resp, $temp2);
    }
}



echo json_encode($resp);