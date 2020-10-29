<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);

$p = $dt->processo;
if (!isset($p->vencimento)) {
        $p->vencimento = 0;
    }

    if (!isset($p->filhos)) {
        $p->filhos = 0;
    }
    if (!isset($p->valorhabitacao)) {
        $p->valorhabitacao = 0;
    }
    if (!isset($p->declarada)) {
        $p->declarada = '';
    }
    if (!isset($p->prazopretendido)) {
        $p->prazopretendido = 0;
    }
    if (!isset($p->valorprestacao)) {
        $p->valorprestacao = 0;
    }
    if (!isset($p->outrainfo)) {
        $p->outrainfo = '';
    }
    if (!isset($p->finalidade)) {
        $p->finalidade = '';
    }
    if (!isset($p->profissao2)) {
        $p->profissao2 = 'NULL';
    }
    if (!isset($p->idade2)) {
        $p->idade2 = 'NULL';
    }
    if (!isset($p->vencimento2)) {
        $p->vencimento2 = 'NULL';
    }
    !isset($p->tipocontrato2) ? $p->tipocontrato2 = 'NULL' : null;
    if (!isset($p->anoinicio2)) {
        $p->anoinicio2 = 'NULL';
    }
    if (!isset($p->telefone2)) {
        $p->telefone2 = 'NULL';
    }
    if (!isset($p->nif2)) {
        $p->nif2 = 'NULL';
    }
    if (!isset($p->valorhabitacao2)) {
        $p->valorhabitacao2 = 0;
    }
    if (!isset($p->anoiniciohabitacao2)) {
        $p->anoiniciohabitacao2 = 1900;
    }
    if (!isset($p->declarada2)) {
        $p->declarada2 = '';
    }
    if (!isset($p->parentesco2)) {
        $p->parentesco2 = '';
    }
    !isset($p->tipohabitacao2) ? $p->tipohabitacao2 = 'NULL' : null;
    if (!isset($p->mesmahabitacao)) {
        $p->mesmahabitacao = '';
    }
    if (isset($p->mesmahabitacao) && $p->mesmahabitacao == 'Sim') {
        $p->tipohabitacao2 = 'NULL';
        $p->declarada2 = '';
        $p->anoiniciohabitacao2 = 'NULL';
        $p->valorhabitacao2 = 0;
    }
    if (!isset($p->tipocredito)) {
        $p->tipocredito = $p->tipo;
    }
     !isset($p->mesinicio) ? $p->mesinicio=1: null;
     !isset($p->mesinicio2) ? $p->mesinicio2=1: null;
     !isset($p->segundoproponente) || $p->segundoproponente==false ? $p->segundoproponente=0 : $p->segundoproponente=1;

    //Save process
    $query = sprintf("INSERT INTO arq_processo(lead,user,nome,nif,email,telefone,idade,profissao,vencimento,tipocontrato,anoinicio,"
            . " estadocivil,filhos,parentesco2,telefone2,nif2,idade2,profissao2,vencimento2,tipocontrato2,anoinicio2,irs,tipohabitacao,"
            . " valorhabitacao,declarada, anoiniciohabitacao,"
            . " tipohabitacao2,valorhabitacao2,declarada2,anoiniciohabitacao2,mesmahabitacao,"
            . " valorpretendido,prazopretendido,prestacaopretendida,finalidade,outrainfo,moradarua,moradalocalidade,moradacp,"
            . "tipoenviodoc,datainicio,tipocredito, mesinicio,mesinicio2, segundoproponente) "
            . " VALUES(%s,%s,'%s',%s,'%s','%s',%s,'%s',%s,%s,%s,%s,%s,'%s','%s',%s,%s,'%s',%s,%s,%s,'%s',%s,%s,'%s',%s,"
            . " %s,%s,'%s',%s,'%s',%s,%s,%s,'%s','%s','%s','%s','%s','%s',NOW(),'%s', %s,%s, %s)",
            $dt->lead->id, $dt->user, ucwords( mb_strtolower( $p->nome, 'UTF-8')), $p->nif, $p->email, $p->telefone, $p->idade, $p->profissao, $p->vencimento, $p->tipocontrato, 
            $p->anoinicio, $p->estadocivil, $p->filhos, $p->parentesco2, $p->telefone2, $p->nif2, $p->idade2, $p->profissao2, $p->vencimento2, 
            $p->tipocontrato2, $p->anoinicio2, $p->irs, $p->tipohabitacao, $p->valorhabitacao, $p->declarada, $p->anoiniciohabitacao, $p->tipohabitacao2, 
            $p->valorhabitacao2, $p->declarada2, $p->anoiniciohabitacao2, $p->mesmahabitacao, $p->montante, $p->prazopretendido, $p->valorprestacao, 
            $p->finalidade, $p->outrainfo, '', '', '', 'email', $p->tipocredito, $p->mesinicio, $p->mesinicio2, $p->segundoproponente);
    $result = mysqli_query($con, $query);
    if(!$result) {
    //UPDATE process
    $query = sprintf("UPDATE arq_processo SET user=%s, nome='%s', nif='%s', email='%s', telefone='%s', idade=%s, profissao='%s',"
            . " vencimento=%s, tipocontrato=%s, anoinicio=%s, estadocivil=%s, filhos=%s, parentesco2=%s, telefone2='%s', nif2='%s', idade2=%s,"
            . " profissao2='%s', vencimento2=%s, tipocontrato2=%s, anoinicio2=%s, irs='%s', tipohabitacao=%s, valorhabitacao=%s, declarada='%s',"
            . " anoiniciohabitacao=%s, tipohabitacao2=%s, valorhabitacao2=%s, declarada2='%s', anoiniciohabitacao2=%s, mesmahabitacao='%s',"
            . " valorpretendido=%s, prazopretendido=%s, prestacaopretendida=%s, finalidade='%s', outrainfo='%s', moradarua='%s', moradalocalidade='%s',"
            . " moradacp='%s', tipoenviodoc='%s', datainicio='%s', tipocredito='%s', mesinicio=%s, mesinicio2=%s, segundoproponente=%s "
            . " WHERE lead=%s ",
            $dt->user, ucwords( mb_strtolower( $p->nome, 'UTF-8')), $p->nif, $p->email, $p->telefone, $p->idade, $p->profissao, $p->vencimento, $p->tipocontrato, 
            $p->anoinicio, $p->estadocivil, $p->filhos, $p->parentesco2, $p->telefone2, $p->nif2, $p->idade2, $p->profissao2, $p->vencimento2, 
            $p->tipocontrato2, $p->anoinicio2, $p->irs, $p->tipohabitacao, $p->valorhabitacao, $p->declarada, $p->anoiniciohabitacao, $p->tipohabitacao2, 
            $p->valorhabitacao2, $p->declarada2, $p->anoiniciohabitacao2, $p->mesmahabitacao, $p->montante, $p->prazopretendido, $p->valorprestacao, 
            $p->finalidade, $p->outrainfo, '', '', '', 'email', $p->tipocredito, $p->mesinicio, $p->mesinicio2, $p->segundoproponente, $dt->lead->id);
    $result = mysqli_query($con, $query);        
    }
    if ($result) {


        //Insere Outros rendimentos
        if (isset($p->or) && sizeof($p->or) > 0) {
            $ln = 1;
            foreach ($p->or as $line) {
                if ($line->valorrendimento > 0) {
                    $queryOR = sprintf("INSERT INTO cad_outrosrendimentos(lead,linha,tiporendimento,valorrendimento,periocidade) "
                            . " VALUES(%s,%s,'%s',%s,'%s')", $p->id, $ln, $line->tiporendimento, $line->valorrendimento, $line->periocidade);
                    mysqli_query($con, $queryOR);
                    $ln++;
                }
            }
        }
        //Insere Outros Creditos
        if (isset($p->oc) && sizeof($p->oc) > 0) {
            $ln = 1;
            foreach ($p->oc as $line) {
                if (isset($line->prestacao) && $line->prestacao > 0) {
                    !isset($line->valorcredito) ? $line->valorcredito = 0 : null;
                    !isset($line->prestacao) ? $line->prestacao = 0 : null;
                    $queryOC = sprintf("INSERT INTO cad_outroscreditos(lead,linha,tipocredito,valorcredito,prestacao) "
                            . " VALUES(%s,%s,'%s',%s,%s)", $p->id, $ln, $line->tipocredito, $line->valorcredito, $line->prestacao);
                    mysqli_query($con, $queryOC);
                    $ln++;
                }
            }
        }
        //Atualizar o status
        mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s', status=8, datastatus=NOW() WHERE id=%s",ucwords( mb_strtolower( $p->nome, 'UTF-8')), $dt->lead->id));
        //Registar o contacto
        mysqli_query($con, sprintf("INSERT INTO cad_registocontacto(lead, user, contactonum, dtcontacto, motivocontacto) VALUES(%s, %s,"
                . "              (SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s), NOW(), 16) ", 
                $dt->lead->id, $dt->user, $dt->lead->id, $dt->user));
        //Registar na agenda como aguarda documentação
        mysqli_query($com, sprintf("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, agendaperiodo, tipoagenda,status) "
                . " VALUES(%s, %s, DATE(NOW()), HOUR(NOW()), 1, 3,1) ", $dt->lead->id, $dt->user));
        
        
        echo true;
    } else {
    echo false;
}   