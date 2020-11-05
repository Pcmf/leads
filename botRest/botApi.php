<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once "../php/openCon.php";
require_once "../php/passwordHash.php";
require_once '../class/PortalAccessEmail.php';


$sign = $_SERVER['HTTP_TYPEFORM_SIGNATURE'];

$data = file_get_contents("php://input");
$array = json_decode($data);
$mySign = base64_encode(hash_hmac('sha256', $data, 'teste123', TRUE));

if ($sign == 'sha256=' . $mySign) {

    if ($data) {
        mysqli_query($con, sprintf("INSERT INTO bot(texto) VALUES('%s') ", $data));

//Questões  
        $fieldsArray = $array->form_response->definition->fields;

//respostas
        $answersArray = $array->form_response->answers;
//Array para inserir na lead
        $objLead = array(); //[0]- idleadorigem - event_id,   [2] - nome, [3] - montante, [4] - prazo, [5]- outros creditos (sim/não),  [6] - prestações em atraso,  [7] - email, [8]- telefone
        //
//identificador da lead
        array_push($objLead, $array->event_id);

//Obter as respostas
        foreach ($fieldsArray AS $f) {
            array_push($objLead, getAnswer($f->id, $answersArray));
        }

//Guardar na BD

        if ($objLead[5] == 'Não') {
            $result = mysqli_query($con, sprintf("INSERT INTO arq_leads( idleadorig, nomelead, fornecedor, tipo, nome, email, telefone, montante, prazopretendido, situacao, status) "
                            . " VALUES('%s', 'Credito360', 21, 'CP', '%s', '%s', '%s', %s, %s, '%s', 1 )", $objLead[0], ucwords( mb_strtolower($objLead[2], 'UTF-8')), $objLead[6], $objLead[7], $objLead[3], $objLead[4], 'Outros creditos: ' . $objLead[5] ));
            $email = $objLead[6];
            
        } else {
            $result = mysqli_query($con, sprintf("INSERT INTO arq_leads( idleadorig, nomelead, fornecedor, tipo, nome, email, telefone, montante, prazopretendido, situacao, status) "
                            . " VALUES('%s', 'Credito360', 21, 'CP', '%s', '%s', '%s', %s, %s, '%s', 1 )", $objLead[0], ucwords( mb_strtolower($objLead[2], 'UTF-8')), $objLead[7], $objLead[8], $objLead[3], $objLead[4], 'Outros creditos: ' . $objLead[5] . ';  Prestações em atraso: ' . $objLead[6]));
            $email = $objLead[7];
            
        }
        $lead = mysqli_insert_id($con);
        new PortalAccessEmail($con, $lead, $objLead[2], $email);
        return 'OK';
        http_response_code(200);
    }
} else {
    echo 'Erro no acesso';
    http_response_code(500);
}

function getAnswer($id, $ans) {
    foreach ($ans AS $a) {
        if ($a->field->id == $id) {

            if ($a->type == 'choice')
                return $a->choice->label;

            return $a->{$a->type};
        }
    }
}

