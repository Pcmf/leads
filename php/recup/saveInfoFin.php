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
!isset($fin->valorpretendido) || !$fin->valorpretendido ? $fin->valorpretendido = 0 : null;
!isset($fin->prestacaopretendida) || !$fin->prestacaopretendida ? $fin->prestacaopretendida = 0 : null;
!isset($fin->prazopretendido) || !$fin->prazopretendido ? $fin->prazopretendido= 0 : null;
!isset($fin->segundoproponente) || !$fin->segundoproponente ? $fin->segundoproponente = 0 : null;
!isset($fin->tipocredito) || !$fin->tipocredito ? $fin->tipocredito = 0 : null;
!isset($fin->vencimento) || !$fin->vencimento ? $fin->vencimento = 0 : null;
!isset($fin->vencimento2) || !$fin->vencimento2 ? $fin->vencimento2= 0 : null;
!isset($fin->valorhabitacao) || !$fin->valorhabitacao ? $fin->valorhabitacao = 0 : null;
!isset($fin->venc_cetelem) || !$fin->venc_cetelem ? $fin->venc_cetelem = 0 : null;
!isset($fin->venc_cetelem2 ) || !$fin->venc_cetelem2  ? $fin->venc_cetelem2 = 0 : null;
!isset($ic->finalidade) || !$ic->finalidade ? $ic->finalidade = '' : null;
!isset($ic->diaprestacao ) || !$ic->diaprestacao  ? $ic->diaprestacao = 1 : null;

!isset($ic->outroscreditos) || !$ic->outroscreditos ? $ic->outroscreditos = 0 : null;
!isset($fin->valorhabitacao) || !$fin->valorhabitacao ? $fin->valorhabitacao = 0 : null;
!isset($ic->segundoproponente) || !$ic->segundoproponente ? $ic->segundoproponente = 0 : null;

$linha = 1;
$result = mysqli_query($con, sprintf("SELECT max(linha) AS max FROM cad_simula WHERE lead=%s", $lead));
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $linha = $row['max'] +1;
}
$query = sprintf("INSERT INTO cad_simula(lead, linha, valorpretendido, prestacaopretendida, prazopretendido,"
        . " segundoproponente, tipocredito, vencimento, vencimento2, venc_cetelem, venc_cetelem2, valorhabitacao) "
        . " VALUES(%s, %s, %s, %s, %s, %s, '%s', %s, %s, %s, %s, %s)", 
        $lead, $linha, $fin->valorpretendido, $fin->prestacaopretendida, $fin->prazopretendido, 
        $fin->segundoproponente, $fin->tipocredito, $ic->vencimento, $ic->vencimento2,
        $fin->venc_cetelem, $fin->venc_cetelem2, $fin->valorhabitacao);
$result = mysqli_query($con, $query );

echo json_encode($result);


// Atualizar arq_processo

mysqli_query($con, sprintf("UPDATE arq_processo SET valorpretendido=%s, prestacaopretendida=%s,"
        . " prazopretendido=%s, segundoproponente=%s, tipocredito='%s', vencimento=%s, vencimento2=%s, "
        . " valorhabitacao=%s, venc_cetelem=%s, venc_cetelem2=%s, "
        . " finalidade='%s',  diaprestacao=%s "
        . " WHERE lead=%s" ,
        $fin->valorpretendido, $fin->prestacaopretendida, $fin->prazopretendido, $fin->segundoproponente,
        $fin->tipocredito, $fin->vencimento, $fin->vencimento2, $fin->valorhabitacao, $fin->venc_cetelem,
        $fin->venc_cetelem2, $ic->finalidade, $ic->diaprestacao,  $lead));

mysqli_query($con, sprintf("UPDATE arq_process_form SET  segundoproponente=%s, vencimento=%s, vencimento2=%s, "
        . " valorhabitacao=%s, venc_cetelem=%s, venc_cetelem2=%s,  diaprestacao=%s "
        . " WHERE lead=%s" ,
        $fin->segundoproponente, $fin->vencimento, $fin->vencimento2, $fin->valorhabitacao, $fin->venc_cetelem,
        $fin->venc_cetelem2, $ic->diaprestacao, $lead));

mysqli_query($con, sprintf("UPDATE arq_leads SET  montante=%s, prazopretendido=%s, rendimento1=%s, rendimento2=%s, "
        . " WHERE id=%s" ,
        $fin->valorpretendido, $fin->prazopretendido,  $fin->vencimento, $fin->vencimento2,  $lead));

return;

