<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dados = json_decode($json);
$dt = $dados->param;
$data = $dt->simulacao;


!isset($dt->valorhabitacao) ? $dt->valorhabitacao = 0 : null;
!isset($dt->vencimento) ? $dt->vencimento = 0 : null;
!isset($dt->vencimento2) ? $dt->vencimento2 = 0 : null;
!isset($dt->filhos) ? $dt->filhos = 0 : null;
!isset($dt->venc_cetelem) ? $dt->venc_cetelem = 0 : null;
!isset($dt->venc_cetelem2) ? $dt->venc_cetelem2 = 0 : null;
!isset($dt->segundoproponente) || !$dt->segundoproponente ? $dt->segundoproponente = 0 : null;
!isset($dt->tipocredito) || !$dt->tipocredito ? $dt->tipocredito = 0 : null;
!isset($dt->outrosrendimentos) ? $dt->outrosrendimentos = 0 : null;
!isset($dt->outroscreditos) ? $dt->outroscreditos = 0 : null;
$query = sprintf("UPDATE arq_processo SET vencimento=%s, vencimento2=%s, valorhabitacao=%s, filhos=%s "
        . " ,venc_cetelem=%s, venc_cetelem2=%s, segundoproponente=%s, tipocredito='%s', outrosrendimentos=%s, outroscreditos=%s "
        . " WHERE lead=%s ", $dt->vencimento, $dt->vencimento2, $dt->valorhabitacao, $dt->filhos, $dt->venc_cetelem, $dt->venc_cetelem2, 
        $dt->segundoproponente, $dt->tipocredito, $dt->outrosrendimentos, $dt->outroscreditos, $dt->lead);

$result = mysqli_query($con, $query);
if ($result) {

//    !isset($parceiro->parceiro) ? $parceiro->parceiro = 'NULL' : null;
//    !isset($parceiro->processo) ? $parceiro->processo = 'NULL' : null;
    !isset($data->valorpretendido) ? $data->valorpretendido = 'NULL' : null;
    !isset($data->prestacaopretendida) ? $data->prestacaopretendida = 'NULL' : null;
    !isset($data->prazopretendido) ? $data->prazopretendido = 'NULL' : null;
    !isset($data->segundoproponente) ? $data->segundoproponente = 'NULL' : null;
    !isset($data->tipocredito) ? $data->tipocredito = 'NULL' : null;
    !isset($data->vencimento) ? $data->vencimento = 'NULL' : null;
    !isset($data->vencimento2) ? $data->vencimento2 = 'NULL' : null;
    !isset($data->venc_cetelem) ? $data->venc_cetelem = 'NULL' : null;
    !isset($data->venc_cetelem2) ? $data->venc_cetelem2 = 'NULL' : null;
    !isset($data->outrosrendimentos) ? $data->outrosrendimentos = 'NULL' : null;
    !isset($data->outroscreditos) ? $data->outroscreditos = 'NULL' : null;
    !isset($data->valorhabitacao) ? $data->valorhabitacao = 'NULL' : null;
    !isset($data->filhos) ? $data->filhos = 'NULL' : null;
    
    // Inserir na cad_simula
    $query = sprintf("INSERT INTO cad_simula(lead, linha, valorpretendido, prestacaopretendida,"
            . " prazopretendido, segundoproponente, tipocredito, vencimento, vencimento2, venc_cetelem, venc_cetelem2,"
            . " outrosrendimentos, outroscreditos, valorhabitacao, filhos) VALUES(%s, (SELECT max(A.linha) FROM cad_simula A WHERE A.lead=%s) +1,"
            . "  %s, %s, %s, %s, '%s', %s, %s, %s, %s, %s, %s, %s, %s) ",
            $dt->lead, $dt->lead, $data->valorpretendido, $data->prestacaopretendida,
            $data->prazopretendido, $data->segundoproponente, $data->tipocredito, $dt->vencimento, $dt->vencimento2, $dt->venc_cetelem,
            $dt->venc_cetelem2, $dt->outrosrendimentos, $dt->outroscreditos, $dt->valorhabitacao, $dt->filhos);
    
    $result = mysqli_query($con, $query);
    if($result) {
        echo "Simulação guardada com sucesso!";
    } else {
        echo "Erro! Não foi possivél guardar simulação";
       // echo $query;
    }
    
    
    
}

