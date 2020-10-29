<?php

/*
 * Obter os dados do processo.
 * Tenta primeiro no arq_process_form e se não existir vais buscar ao arq_processo e grava no primeiro
 */

require_once '../openCon.php';
$lead = file_get_contents("php://input");

$result = mysqli_query($con, sprintf("SELECT * FROM arq_process_form WHERE lead=%s", $lead));
if ($result && $result->num_rows > 0) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    echo json_encode($row);
} else {
    //Obter os dados do processo e guardar no process_form
    $result = mysqli_query($con, sprintf("SELECT * FROM arq_processo WHERE lead=%s", $lead));
    if ($result) {
        $row0 = mysqli_fetch_array($result, MYSQLI_ASSOC);
        // Inserir alguns valores a partir do arq_processo
       !isset($row0['estadocivil']) ? $row0['estadocivil']=0 : null;
       !isset($row0['filhos']) ? $row0['filhos']=0 : null;
       !isset($row0['nif']) ? $row0['nif']=0 : null;
       !isset($row0['segundoproponente']) ? $row0['segundoproponente']=0 : null;
       !isset($row0['nome2']) ? $row0['nome2']='' : null;
       !isset($row0['nif2']) ? $row0['nif2']=0 : null;
       !isset($row0['relacaofamiliar']) ? $row0['relacaofamiliar']=0 : null;
       !isset($row0['tipohabitacao']) ? $row0['tipohabitacao']=0 : null;
       !isset($row0['anoiniciohabitacao']) ? $row0['anoiniciohabitacao']=0 : null;
       !isset($row0['tipohabitacao2']) ? $row0['tipohabitacao2']=0 : null;
       !isset($row0['anoiniciohabitacao2']) ? $row0['anoiniciohabitacao2']=0 : null;
       !isset($row0['telefone']) ? $row0['telefone']='' : null;
       !isset($row0['email']) ? $row0['email']='' : null;
       !isset($row0['telefone2']) ? $row0['telefone2']='' : null;
       !isset($row0['tipocontrato']) ? $row0['tipocontrato']=0 : null;
       !isset($row0['tipocontrato2']) ? $row0['tipocontrato2']=0 : null;
       !isset($row0['anoinicio']) ? $row0['anoinicio']='' : null;
       !isset($row0['mesinicio']) ? $row0['mesinicio']='' : null;
       !isset($row0['anoinicio2']) ? $row0['anoinicio2']='' : null;
       !isset($row0['mesinicio2']) ? $row0['mesinicio2']='' : null;
       !isset($row0['mesinicio2']) ? $row0['mesinicio2']='' : null;
       !isset($row0['diaprestacao']) ? $row0['diaprestacao']=1 : null;
        
        
        $query = sprintf("INSERT INTO arq_process_form( lead, nome, estadocivil, filhos, nif, segundoproponente, nome2, nif2, relacaofamiliar, "
                . " tipohabitacao, anoiniciohabitacao, tipohabitacao2, anoiniciohabitacao2, telefone, email, telefone2, tipocontrato, tipocontrato2, "
                . " desde, desdemes, desde2, desdemes2, diaprestacao, sector, sector2) "
                . " VALUES(%s, '%s', %s, %s, %s, %s, '%s', %s, %s, %s, %s, %s, %s, '%s', '%s', '%s', %s, %s, '%s', '%s', '%s', '%s', %s, '%s', '%s')",
                $lead, $row0['nome'], $row0['estadocivil'], $row0['filhos'], $row0['nif'], $row0['segundoproponente'], $row0['nome2'],
                $row0['nif2'], $row0['relacaofamiliar'], $row0['tipohabitacao'], $row0['anoiniciohabitacao'], $row0['tipohabitacao2'],
                $row0['anoiniciohabitacao2'], $row0['telefone'], $row0['email'], $row0['telefone2'], $row0['tipocontrato'], $row0['tipocontrato2'], 
                $row0['anoinicio'], $row0['mesinicio'], $row0['anoinicio2'], $row0['mesinicio2'], $row0['diaprestacao'], $row0['profissao'], $row0['profissao']);
        $result = mysqli_query($con, $query);
        if ($result) {
            $result = mysqli_query($con, sprintf("SELECT * FROM arq_process_form WHERE lead=%s", $lead));
            if ($result->num_rows == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                echo json_encode($row);
            }
        } else {
            echo $query;
        }
    }
}

