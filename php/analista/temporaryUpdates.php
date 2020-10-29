<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

!isset($dt->prazo) ? $dt->prazo=0 : null;
!isset($dt->prestacao) ? $dt->prestacao=0 : null;
!isset($dt->montante) ? $dt->montante=0 : null;
!isset($dt->tipocredito) ? $dt->tipocredito='' : null;
!isset($dt->parceiro) ? $dt->parceiro='' : null;
//Tenta Inserir no financiamentos
if($dt->status == 6){
    $query = sprintf("INSERT INTO cad_financiamentos(lead,processo,parceiro,tipocredito,montante,prazo,prestacao,status,datasubmetido,dataaprovado) "
            . " VALUES(%s,'%s',%s,'%s',%s,%s,%s,%s,NOW(),NOW()) "
            , $dt->lead, $dt->processo,$dt->parceiro,$dt->tipocredito,$dt->montante,$dt->prazo,$dt->prestacao,$dt->status);
} else{
    $query = sprintf("INSERT INTO cad_financiamentos(lead,processo,parceiro,tipocredito,montante,prazo,prestacao,status,datasubmetido) "
            . " VALUES(%s,'%s',%s,'%s',%s,%s,%s,%s,NOW()) "
            , $dt->lead, $dt->processo,$dt->parceiro,$dt->tipocredito,$dt->montante,$dt->prazo,$dt->prestacao,$dt->status);  

}
$result = mysqli_query($con,$query);
if(!$result){
    //Vai atualizar os status
    if($dt->status == 6){
    $query = sprintf("UPDATE cad_financiamentos SET status=6,datastatus=NOW(),dataaprovado=NOW(), prazo=%s, prestacao=%s, montante=%s, tipocredito='%s' "
            . " WHERE lead=%s AND processo='%s' ",$dt->prazo,$dt->prestacao,$dt->montante,$dt->tipocredito ,$dt->lead,$dt->processo);
    } else {
    $query = sprintf("UPDATE cad_financiamentos SET status=%s, datastatus=NOW(),tipocredito='%s', prazo=%s, prestacao=%s, montante=%s , tipocredito='%s' "
            . " WHERE lead=%s AND processo='%s' ", $dt->status,$dt->tipocredito,$dt->prazo,$dt->prestacao,$dt->montante,$dt->tipocredito ,$dt->lead,$dt->processo);        
    }
    mysqli_query($con,$query);
 //   echo $query;
} else {
 //   echo $query;
    mysqli_query($con,sprintf("UPDATE arq_leads SET status=13, datastatus=NOW(), analista=%s WHERE id=%s", $dt->userId,$dt->lead));
}


