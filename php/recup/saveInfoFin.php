<?php

/* 
 * guarda alterações e novos dados financeiros nos ficheiros relevantes:
 *  arq_processo, cad_simula, cad_simulag
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);
$gestor = $dt->gestor;
$lead = $dt->lead;
$ic = $dt->ic;
$fin = $dt->fin;




// Simulação inicial
!isset($ic->outrosrendimentos) || !$ic->outrosrendimentos ? $ic->outrosrendimentos = 0 : null;
!isset($ic->outroscreditos) || !$ic->outroscreditos ? $ic->outroscreditos = 0 : null;
!isset($ic->valorhabitacao) || !$ic->valorhabitacao ? $ic->valorhabitacao = 0 : null;
!isset($ic->segundoproponente) || !$ic->segundoproponente ? $ic->segundoproponente = 0 : null;

$linha = 1;
$result = mysqli_query($con, sprintf("SELECT max(linha) AS max FROM cad_simula WHERE lead=%s", $lead));
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $linha = $row['max'] +1;
}
mysqli_query($con, sprintf("INSERT INTO cad_simula(lead, linha, valorpretendido, prestacaopretendida, prazopretendido,"
        . " segundoproponente, tipocredito, vencimento, vencimento2, valorhabitacao, filhos, outroscreditos, outrosrendimentos) "
        . " VALUES(%s, %s, %s, %s, %s, %s, '%s', %s, %s, %s, %s, %s, %s)", 
        $lead, $linha, $fin->valorpretendido, $fin->prestacaopretendida, $fin->prazopretendido, 
        $ic->segundoproponente, $fin->tipocredito, $ic->vencimento, $ic->vencimento2, $ic->valorhabitacao, $ic->filhos, 
        $ic->outroscreditos, $ic->outrosrendimentos ));


// Atualizar arq_processo

mysqli_query($con, sprintf("UPDATE arq_processo SET finalidade='%s', outrainfo='%s', diaprestacao=%s WHERE lead=%s" ,
        $ic->finalidade, $ic->outrainfo, $ic->diaprestacao, $lead));

mysqli_query($con, sprintf("UPDATE arq_process_form SET diaprestacao=%s WHERE lead=%s" ,
         $ic->diaprestacao, $lead));






