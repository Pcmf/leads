<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$lead = json_decode($json);

$resp = array();
$docsPed = array();
$docsRec = array();
$docsConf = array();
$email ='';
//get list of required docs
$query = sprintf("SELECT P.lead,P.email,P.nome,P.nif,P.idade,P.profissao,D.linha,D.recebido,N.nomedoc,N.descricao,N.sigla "
        . " FROM arq_processo P"
        . " INNER JOIN cad_docpedida D ON P.lead=D.lead"
        . " INNER JOIN cnf_docnecessaria N ON D.tipodoc=N.id"
        . " WHERE P.lead=%s",$lead);
$result=mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $email = $row['email'];
        array_push($docsPed, $row);
    }
    //Get already received and validated
    $query0 = sprintf("SELECT linha,tipo,nomefx FROM arq_documentacao WHERE lead=%s",$lead);
    $result0 = mysqli_query($con,$query0);
    if($result0){
        while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
            array_push($docsConf, $row0); 
        }
    } else {
        echo $query0;
    }
    //Get received docs
    $query1 = sprintf("SELECT dataentrada,email,linha,xlead,assunto,fxname,fxtype,situacao"
            . " FROM arq_emaildocs WHERE email='%s' AND situacao=1",$email);
    $result1=mysqli_query($con,$query1);
    if($result1){
        while ($row1 = mysqli_fetch_array($result1,MYSQLI_ASSOC)) {
            array_push($docsRec, $row1); 
        }
    } else {
        echo $query1;
    }
    $resp['docsPed']=$docsPed;
    $resp['docsRec'] =$docsRec;
    $resp['docsConf'] =$docsConf;
    
    echo json_encode($resp);
} else {
    echo $query;
}
