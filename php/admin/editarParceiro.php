<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt= json_decode($json);

!isset($dt->parceiro->email) ? $dt->parceiro->email=null : null;
!isset($dt->parceiro->telefone) ? $dt->parceiro->telefone=null : null;
!isset($dt->parceiro->percentagem) ? $dt->parceiro->percentagem=0 : null;
!isset($dt->parceiro->usaformula) ? $dt->parceiro->usaformula=0 : null;
!isset($dt->parceiro->formula) ? $dt->parceiro->formula='' : null; 


if($dt->op == 'IU'){
    if(!isset($dt->parceiro->id)){
    $query = sprintf("INSERT INTO cad_parceiros(nome,email,telefone,ativo,percentagem,usaformula,formula,tipoparceiro) VALUES('%s','%s','%s',1,%s,%s,'%s',%s)",
            $dt->parceiro->nome, $dt->parceiro->email, $dt->parceiro->telefone,$dt->parceiro->percentagem, $dt->parceiro->usaformula,$dt->parceiro->formula,$dt->parceiro->tipoparceiro);
    } else {
        $query = sprintf("UPDATE cad_parceiros SET nome='%s', email='%s', telefone='%s', percentagem=%s, usaformula=%s, formula='%s', ativo=%s, tipoparceiro=%s WHERE id=%s",
                $dt->parceiro->nome,$dt->parceiro->email, $dt->parceiro->telefone,$dt->parceiro->percentagem, $dt->parceiro->usaformula,$dt->parceiro->formula,
                $dt->parceiro->ativo,$dt->parceiro->tipoparceiro,$dt->parceiro->id);   
    }
    mysqli_query($con, $query);
    echo $query;
}
if($dt->op=='D'){
    //TODO - verificar se tem financiamentos pendentes
    $query0 = sprintf("SELECT * FROM cad_financiamentos WHERE parceiro = %s AND status NOT IN(1,2,4)",$dt->parceiro->id);
    $result = mysqli_query($con, $query0);
    if(!(mysqli_affected_rows($con)>0) || $dt->parceiro->ativo==0){
        $query = sprintf("UPDATE cad_parceiros SET ativo=(NOT ativo) WHERE id=%s",$dt->parceiro->id);
    } else {
        echo "Tem financiamentos ativos";
    }
}


    return;