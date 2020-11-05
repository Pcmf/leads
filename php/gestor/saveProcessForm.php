<?php

/*
 * guarda alterações e novos dados do formulario do processo na tabela arq_process_form
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$p = $dt->process;
$ic = $dt->ic;
$lead = $dt->lead;

!isset($ic->nome) ? $ic->nome = $ic->nome : null;
!isset($p->datanascimento) ? $p->datanascimento = '' : null;
!isset($p->idade) ? $p->idade = 0 : null;
!isset($p->tipodoc) || !$p->tipodoc ? $p->tipodoc = 0 : null;
!isset($p->numdocumento) ? $p->numdocumento = '' : null;
!isset($p->validade) ? $p->validade = '' : null;
!isset($p->nacionalidade) ? $p->nacionalidade = '' : null;
!isset($p->estadocivil) ? $p->estadocivil = 0 : null;
!isset($p->filhos) || !$p->filhos ? $p->filhos = 0 : null;
!isset($p->nif) || !$p->nif ? $p->nif = 0 : null;
!isset($p->segundoproponente) || !$p->segundoproponente ? $p->segundoproponente = 0 : null;
!isset($p->nome2) ? $p->nome2 = '' : null;
!isset($p->datanascimento2) ? $p->datanascimento2 = '' : null;
!isset($p->tipodoc2) || !$p->tipodoc2 ? $p->tipodoc2 = 0 : null;
!isset($p->numdocumento2) ? $p->numdocumento2 = '' : null;
!isset($p->validade2) ? $p->validade2 = '' : null;
!isset($p->nacionalidade2) ? $p->nacionalidade2 = '' : null;
!isset($p->nif2) ? $p->nif2 = 0 : null;
!isset($p->relacaofamiliar) || !$p->relacaofamiliar ? $p->relacaofamiliar = 0 : null;
!isset($p->morada) ? $p->morada = '' : null;
!isset($p->localidade) ? $p->localidade = '' : null;
!isset($p->cp) ? $p->cp = '' : null;
!isset($p->tipohabitacao) || !$p->tipohabitacao ? $p->tipohabitacao = 0 : null;
!isset($p->valorhabitacao) || !$p->valorhabitacao ? $p->valorhabitacao = 0 : null;
!isset($p->valorhabitacao2) || !$p->valorhabitacao2 ? $p->valorhabitacao2 = 0 : null;
!isset($p->anoiniciohabitacao) ? $p->anoiniciohabitacao = 0 : null;
!isset($p->telefone) ? $p->telefone = '' : null;
!isset($p->email) ? $p->email = '' : null;
!isset($p->mesmahabitacao) || !$p->mesmahabitacao ? $p->mesmahabitacao = 0 : null;
!isset($p->morada2) ? $p->morada2 = '' : null;
!isset($p->localidade2) ? $p->localidade2 = '' : null;
!isset($p->cp2) ? $p->cp2 = '' : null;
!isset($p->tipohabitacao2) || !$p->tipohabitacao2 ? $p->tipohabitacao2 = 0 : null;
!isset($p->anoiniciohabitacao2) ? $p->anoiniciohabitacao2 = 0 : null;
!isset($p->telefone2) ? $p->telefone2 = '' : null;
!isset($p->email2) ? $p->email2 = '' : null;

!isset($p->sector) ? $p->sector = '' : null;
!isset($p->tipocontrato) || !$p->tipocontrato ? $p->tipocontrato = 0 : null;
!isset($p->desde) ? $p->desde = '' : null;
!isset($p->nomeempresa) ? $p->nomeempresa = '' : null;
!isset($p->nifempresa) || !$p->nifempresa ? $p->nifempresa = 0 : null;
!isset($p->telefoneempresa) ? $p->telefoneempresa = '' : null;
!isset($p->sector2) ? $p->sector2 = '' : null;
!isset($p->tipocontrato2) || !$p->tipocontrato2 ? $p->tipocontrato2 = 0 : null;
!isset($p->desde2) ? $p->desde2 = '' : null;
!isset($p->nomeempresa2) ? $p->nomeempresa2 = '' : null;
!isset($p->nifempresa2) || !$p->nifempresa2 ? $p->nifempresa2 = 0 : null;
!isset($p->telefoneempresa2) ? $p->telefoneempresa2 = '' : null;
!isset($p->iban) ? $p->iban = '' : null;
!isset($p->ibandesde) || !$p->ibandesde ? $p->ibandesde = '' : null;
!isset($p->desdemes) || !$p->desdemes ? $p->desdemes = 0 : null;
!isset($p->desdemes2) || !$p->desdemes2 ? $p->desdemes2 = 0 : null;

!isset($p->vencimento) || !$p->vencimento ? $p->vencimento = 0 : null;
!isset($p->vencimento2) || !$p->vencimento2 ? $p->vencimento2 = 0 : null;
!isset($p->venc_cetelem) || !$p->venc_cetelem ? $p->venc_cetelem = 0 : null;
!isset($p->venc_cetelem2) || !$p->venc_cetelem2 ? $p->venc_cetelem2 = 0 : null;
!isset($p->diaprestacao) || !$p->diaprestacao ? $p->diaprestacao = 1 : null;

// Atualizar o arq_leads e o arq_processo
mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s', email='%s', telefone='%s', nif='%s' "
                . " WHERE id=%s",
                $ic->nome, $p->email, $p->telefone, $p->nif, $dt->lead->id));

$result = mysqli_query($con, sprintf("SELECT * FROM arq_processo WHERE lead=%s", $dt->lead->id));
if ($result) {
    mysqli_query($con, sprintf("UPDATE arq_processo SET nome='%s', nif='%s', idade=%s, email='%s', telefone='%s', vencimento=%s, vencimento2=%s, "
                    . " venc_cetelem=%s, venc_cetelem2=%s, segundoproponente=%s, "
                    . " nome2='%s', valorhabitacao=%s, valorhabitacao2=%s, tipohabitacao=%s, tipohabitacao2=%s  "
                    . " WHERE lead=%s ",
                    $ic->nome, $p->nif, $p->idade, $p->email, $p->telefone, $p->vencimento, $p->vencimento2, $p->venc_cetelem, $p->venc_cetelem2, $p->segundoproponente,
                    $p->nome2, $p->valorhabitacao, $p->valorhabitacao2, $p->tipohabitacao, $p->tipohabitacao2,
                    $dt->lead->id));
} else {
    $query = sprintf("INSERT INTO arq_processo (lead, nome, nif, idade, email, telefone, vencimento, vencimento2, "
            . " venc_cetelem, venc_cetelem2, segundoproponente, "
            . " nome2, valorhabitacao, valorhabitacao2, tipohabitacao, tipohabitacao2) "
            . " VALUES(%s, '%s', '%s', %s, '%s', '%s', '%s', %s, %s, %s, %s, '%s', %s, %s, %s, %s)  ",
            $dt->lead->id, $ic->nome, $p->nif, $p->idade,  $p->email, $p->telefone, $p->vencimento, $p->vencimento2, $p->venc_cetelem,
            $p->venc_cetelem2, $p->segundoproponente, $p->nome2, $p->valorhabitacao, $p->valorhabitacao2, $p->tipohabitacao, $p->tipohabitacao2);
    // echo $query;
    mysqli_query($con, $query);
}


// arq_process_form INSERT

$query = sprintf("INSERT INTO arq_process_form (lead, nome, datanascimento, tipodoc, numdocumento, validade, "
        . " nacionalidade, estadocivil, filhos, nif, vencimento, venc_cetelem, segundoproponente,"
        . " nome2, datanascimento2,  tipodoc2, numdocumento2, validade2, nacionalidade2, nif2,"
        . " vencimento2, venc_cetelem2, relacaofamiliar,"
        . " morada, localidade, cp, tipohabitacao, anoiniciohabitacao, telefone, email, mesmahabitacao,"
        . " morada2, localidade2, cp2, tipohabitacao2, anoiniciohabitacao2, telefone2, email2, "
        . " sector, tipocontrato, desde, nomeempresa, nifempresa, telefoneempresa, "
        . " sector2, tipocontrato2, desde2, nomeempresa2, nifempresa2, telefoneempresa2, "
        . " iban, ibandesde, desdemes, desdemes2, valorhabitacao, valorhabitacao2, diaprestacao) "
        . " VALUES(%s,'%s', '%s', %s, '%s', '%s', "
        . "'%s', %s, %s, %s, %s, %s, %s,"
        . "'%s', '%s', %s, '%s', '%s', '%s', %s,"
        . "%s, %s, %s,"
        . "'%s', '%s', '%s', %s, %s,  '%s', '%s', %s, "
        . "'%s', '%s', '%s', %s, %s, '%s', '%s',"
        . "'%s', %s, '%s', '%s', %s, '%s',"
        . "'%s', %s, '%s', '%s', %s, '%s',"
        . "'%s', '%s', %s, %s, %s, %s, %s) "
        , $dt->lead->id, $ic->nome, $p->datanascimento, $p->tipodoc, $p->numdocumento, $p->validade, $p->nacionalidade, $p->estadocivil,
        $p->filhos, $p->nif, $p->vencimento, $p->venc_cetelem,
        $p->segundoproponente, $p->nome2, $p->datanascimento2, $p->tipodoc2, $p->numdocumento2, $p->validade2, $p->nacionalidade2, $p->nif2,
        $p->vencimento2, $p->venc_cetelem2,
        $p->relacaofamiliar, $p->morada, $p->localidade, $p->cp, $p->tipohabitacao, $p->anoiniciohabitacao, $p->telefone, $p->email, $p->mesmahabitacao,
        $p->morada2, $p->localidade2, $p->cp2, $p->tipohabitacao2, $p->anoiniciohabitacao2, $p->telefone2, $p->email2,
        $p->sector, $p->tipocontrato, $p->desde, $p->nomeempresa, $p->nifempresa, $p->telefoneempresa,
        $p->sector2, $p->tipocontrato2, $p->desde2, $p->nomeempresa2, $p->nifempresa2, $p->telefoneempresa2,
        $p->iban, $p->ibandesde, $p->desdemes, $p->desdemes2, $p->valorhabitacao, $p->valorhabitacao2, $p->diaprestacao
);

$result = mysqli_query($con, $query);

if ($result) {
    echo $result;
    return;
} else {

    $query = sprintf("UPDATE arq_process_form SET nome='%s', datanascimento='%s', tipodoc=%s, numdocumento='%s', validade='%s', "
            . " nacionalidade='%s', estadocivil=%s, filhos=%s, nif=%s, vencimento=%s, venc_cetelem=%s, segundoproponente=%s,"
            . " nome2='%s', datanascimento2='%s',  tipodoc2=%s, numdocumento2='%s', validade2='%s', nacionalidade2='%s', nif2=%s,"
            . " vencimento2=%s, venc_cetelem2=%s, relacaofamiliar=%s,"
            . " morada='%s', localidade='%s', cp='%s', tipohabitacao=%s, anoiniciohabitacao='%s', telefone='%s', email='%s', mesmahabitacao=%s,"
            . " morada2='%s', localidade2='%s', cp2='%s', tipohabitacao2=%s, anoiniciohabitacao2='%s', telefone2='%s', email2='%s', "
            . " sector='%s', tipocontrato=%s, desde='%s', nomeempresa='%s', nifempresa=%s, telefoneempresa='%s', "
            . " sector2='%s', tipocontrato2=%s, desde2='%s', nomeempresa2='%s', nifempresa2=%s, telefoneempresa2='%s', "
            . " iban='%s', ibandesde='%s', desdemes=%s, desdemes2=%s, valorhabitacao=%s, valorhabitacao2=%s, diaprestacao=%s "
            . " WHERE lead=%s ", $ic->nome, $p->datanascimento, $p->tipodoc, $p->numdocumento, $p->validade, $p->nacionalidade, $p->estadocivil,
            $p->filhos, $p->nif, $p->vencimento, $p->venc_cetelem,
            $p->segundoproponente, $p->nome2, $p->datanascimento2, $p->tipodoc2, $p->numdocumento2, $p->validade2, $p->nacionalidade2, $p->nif2,
            $p->vencimento2, $p->venc_cetelem2,
            $p->relacaofamiliar, $p->morada, $p->localidade, $p->cp, $p->tipohabitacao, $p->anoiniciohabitacao, $p->telefone, $p->email, $p->mesmahabitacao,
            $p->morada2, $p->localidade2, $p->cp2, $p->tipohabitacao2, $p->anoiniciohabitacao2, $p->telefone2, $p->email2,
            $p->sector, $p->tipocontrato, $p->desde, $p->nomeempresa, $p->nifempresa, $p->telefoneempresa,
            $p->sector2, $p->tipocontrato2, $p->desde2, $p->nomeempresa2, $p->nifempresa2, $p->telefoneempresa2,
            $p->iban, $p->ibandesde, $p->desdemes, $p->desdemes2, $p->valorhabitacao, $p->valorhabitacao2, $p->diaprestacao,
            $dt->lead->id);

    $result = mysqli_query($con, $query);
    echo $result;
    return;
}