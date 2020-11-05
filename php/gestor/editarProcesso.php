<?php

/* 
 * Altera os dados do processo
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);
$ic = $dt->ic;
!isset($ic->nif)?$ic->nif=999999990:null;
!isset($ic->email)?$ic->email='':null;
!isset($ic->telefone)?$ic->telefone='':null;
!isset($ic->idade)?$ic->idade=99:null;
!isset($ic->filhos)?$ic->filhos=0:null;
!isset($ic->telefone2)?$ic->telefone2='' :null;
!isset($ic->nif2)?$ic->nif2=0:null;
!isset($ic->nome2)?$ic->nome2='':null;
!isset($ic->idade2)?$ic->idade2=0:null;
!isset($ic->profissao2)?$ic->profissao2='':null;
!isset($ic->tipocontrato2)?$ic->tipocontrato2=0:null;
!isset($ic->vencimento)?$ic->vencimento=0:null;
!isset($ic->vencimento2)?$ic->vencimento2=0:null;
!isset($ic->tipocontrato)?$ic->tipocontrato=0:null;
!isset($ic->tipocredito)?$ic->tipocredito='CP':null;
!isset($ic->anoinicio)?$ic->anoinicio=0:null;
!isset($ic->anoinicio2)?$ic->anoinicio2=0:null;
!isset($ic->irs)?$ic->irs='':null;
!isset($ic->valorpretendido)?$ic->valorpretendido=0:null;
!isset($ic->prazopretendido)?$ic->prazopretendido=0:null;
!isset($ic->prestacaopretendida)?$ic->prestacaopretendida=0:null;
!isset($ic->finalidade)?$ic->finalidade='':null;
!isset($ic->tipohabitacao)?$ic->tipohabitacao= 0 : null;
!isset($ic->anoiniciohabitacao)?$ic->anoiniciohabitacao=0 :null;
!isset($ic->valorhabitacao) || !$ic->valorhabitacao ?$ic->valorhabitacao=0:null;
!isset($ic->declarada)?$ic->declarada='' : null;
!isset($ic->parentesco2)?$ic->parentesco2='':null;
!isset($ic->estadocivil)?$ic->estadocivil=1:null;
!isset($ic->anoiniciohabitacao2)?$ic->anoiniciohabitacao2=0 :null;
!isset($ic->mesmahabitacao)?$ic->mesmahabitacao= 'Nao' : null;
!isset($ic->tipohabitacao2)?$ic->tipohabitacao2= 0 : null;
!isset($ic->valorhabitacao2)?$ic->valorhabitacao2=0:null;
!isset($ic->declarada2)?$ic->declarada2='' : null;
!isset($ic->outrainfo)?$ic->outrainfo='':null;
!isset($ic->moradarua)?$ic->moradarua='':null;
!isset($ic->moradalocalidade)?$ic->moradalocalidade='':null;
!isset($ic->moradacp)?$ic->moradacp='':null;
!isset($ic->mesinicio) ? $ic->mesinicio=0 : null;
!isset($ic->mesinicio2) ? $ic->mesinicio2=0 : null;
!isset($ic->segundoproponente) || !$ic->segundoproponente? $ic->segundoproponente=0 : $ic->segundoproponente=1;

$query = sprintf("UPDATE arq_processo SET nome='%s', nif='%s', email='%s', telefone='%s', idade=%s, "
        . " profissao='%s', vencimento=%s, tipocontrato=%s, mesinicio=%s, anoinicio=%s, estadocivil=%s, filhos=%s, "
        . " telefone2='%s', nome2='%s', nif2=%s, idade2=%s, profissao2='%s', vencimento2=%s, mesinicio2=%s, anoinicio2=%s, irs='%s', "
        . " tipohabitacao=%s, valorhabitacao=%s, declarada='%s', anoiniciohabitacao=%s, "
        . " valorpretendido=%s, tipocredito='%s', prazopretendido=%s, prestacaopretendida=%s, finalidade='%s', segundoproponente=%s,  "
        . " parentesco2='%s', anoiniciohabitacao2=%s, valorhabitacao2='%s', outrainfo='%s', "
        . " moradarua='%s', moradalocalidade='%s', moradacp='%s', tipocontrato2=%s, declarada2='%s', tipohabitacao2=%s, mesmahabitacao='%s' "
        . " WHERE lead=%s ",ucwords( mb_strtolower( $ic->nome, 'UTF-8')),$ic->nif,$ic->email,$ic->telefone,$ic->idade,
        $ic->profissao,$ic->vencimento,$ic->tipocontrato, $ic->mesinicio , $ic->anoinicio,$ic->estadocivil,$ic->filhos,
        $ic->telefone2, $ic->nome2, $ic->nif2,$ic->idade2,$ic->profissao2,$ic->vencimento2,$ic->mesinicio2, $ic->anoinicio2,$ic->irs
        ,$ic->tipohabitacao,$ic->valorhabitacao,$ic->declarada,$ic->anoiniciohabitacao
        ,$ic->valorpretendido,$ic->tipocredito,$ic->prazopretendido,$ic->prestacaopretendida,$ic->finalidade, $ic->segundoproponente, 
        $ic->parentesco2,$ic->anoiniciohabitacao2,$ic->valorhabitacao2,$ic->outrainfo,$ic->moradarua,
        $ic->moradalocalidade,$ic->moradacp, $ic->tipocontrato2, $ic->declarada2, $ic->tipohabitacao2, $ic->mesmahabitacao,  
        $dt->lead);


$result = mysqli_query($con, $query);


if(mysqli_affected_rows($con) < 0) {
    //Obter o utilizador da lead
    //echo $query;
    $r = mysqli_query($con, sprintf("SELECT user FROM arq_leads WHERE id=%s ", $dt->lead));
    if($r){
        $row0 = mysqli_fetch_array($r, MYSQLI_ASSOC);
        $user =$row0['user'];
    } else {
        $user = 0;
    }
    //Insere no processo
    $query = sprintf("INSERT INTO arq_processo( lead, user, nome, nif, email, telefone, idade, profissao, vencimento, tipocontrato, mesinicio, "
            . " anoinicio, estadocivil, filhos, parentesco2, telefone2, nif2, idade2, profissao2, vencimento2, tipocontrato2, mesinicio2, anoinicio2, irs, "
            . " tipohabitacao, valorhabitacao, declarada, anoiniciohabitacao, tipohabitacao2, valorhabitacao2, declarada2, anoiniciohabitacao2, mesmahabitacao,"
            . " valorpretendido, prazopretendido, prestacaopretendida, tipocredito, finalidade, outrainfo, moradarua, moradalocalidade, moradacp, nome2, segundoproponente ) "
            . " VALUES(%s, %s, '%s', %s, '%s', '%s', %s, '%s', %s, %s, %s,"
            . " %s, %s, %s, '%s', '%s',%s, %s, '%s', %s, %s, %s, %s, '%s',  "
            . " %s, %s, '%s', %s, %s, %s, '%s', %s, '%s', "
            . " %s, %s, %s,'%s', '%s', '%s', '%s', '%s', '%s', '%s', %s )",
            $dt->lead, $user , ucwords( mb_strtolower( $ic->nome, 'UTF-8')), $ic->nif, $ic->email, $ic->telefone, $ic->idade, $ic->profissao, $ic->vencimento, $ic->tipocontrato, $ic->mesinicio
            , $ic->anoinicio, $ic->estadocivil, $ic->filhos, $ic->parentesco2, $ic->telefone2, $ic->nif2, $ic->idade2, $ic->profissao2, $ic->vencimento2, $ic->tipocontrato2, $ic->mesinicio2, $ic->anoinicio2, $ic->irs
        ,$ic->tipohabitacao,$ic->valorhabitacao,$ic->declarada,$ic->anoiniciohabitacao, $ic->tipohabitacao2, $ic->valorhabitacao2, $ic->declarada2, $ic->anoiniciohabitacao2, $ic->mesmahabitacao, 
            $ic->valorpretendido,$ic->prazopretendido, $ic->prestacaopretendida,$ic->tipocredito,$ic->finalidade, $ic->outrainfo,$ic->moradarua, $ic->moradalocalidade,$ic->moradacp, $ic->nome2, $ic->segundoproponente);
    $result = mysqli_query($con, $query);
}

if($result){
    mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s', nif=%s, email='%s', telefone=%s WHERE id=%s ", ucwords( mb_strtolower( $ic->nome, 'UTF-8')),$ic->nif,$ic->email,$ic->telefone,$dt->lead));
    //atualiza o cliente
    mysqli_query($con, sprintf("UPDATE cad_clientes SET nome='%s', email='%s', nif=%s WHERE lead=%s ", $ic->nome, $ic->email, $ic->nif, $dt->lead));
    echo 'OK';
} else {
    echo 'Erro\n'.$query;
}
