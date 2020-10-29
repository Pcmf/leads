<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp=array();

if(isset($dt->lead) && $dt->lead>0){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "
            . " WHERE L.id=%s",$dt->lead);
}
if(isset($dt->nome) && $dt->nome!=''){
    
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM arq_leads L "
            . " LEFT JOIN arq_processo P ON P.lead=L.id "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE L.nome LIKE '%s'",$dt->nome.'%'  );
}
if(isset($dt->telefone) && $dt->telefone>0){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE L.telefone = '%s'",$dt->telefone);
}
if(isset($dt->nif) && $dt->nif>0){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE L.nif =%s ",$dt->nif);
}
if(isset($dt->email) && $dt->email!=''){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE L.email LIKE '%s'",$dt->email.'%');
}
if(isset($dt->process) && $dt->process!=''){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM cad_financiamentos F "
            . " INNER JOIN arq_leads L ON L.id=F.lead "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE F.processo='%s' ",$dt->process);
}
if(isset($dt->parceiro) && $dt->parceiro!=''){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM cad_financiamentos F "
            . " INNER JOIN arq_leads L ON L.id=F.lead "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE F.parceiro='%s' ",$dt->parceiro->id);
}
if(isset($dt->leadorig) && $dt->leadorig>0){
    $query = sprintf("SELECT L.*,S.nome AS stsnome,S.descricao,U.nome AS nomeUser, U2.nome AS nomeAnalista "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON L.status=S.id "
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT  JOIN cad_utilizadores U2 ON U2.id=L.analista "            
            . " WHERE L.idleadorig=%s",$dt->leadorig);
}
$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}