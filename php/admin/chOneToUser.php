<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);


//Verificar que a lead está num status  inferior  a 10 para Gesto e maior ou igual a 12 para analistas
if ($dt->tipo == 'Gestor') {
    $result0 = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s  AND (status < 10  OR status BETWEEN 100 AND 109)", $dt->lead));
}
if ($dt->tipo == 'GRec') {
    $result0 = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s  AND (status < 10  OR status BETWEEN 100 AND 109)", $dt->lead));
}
if ($dt->tipo == 'Analista') {
    $result0 = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s  AND status >= 12", $dt->lead));
}
if ($result0) {
    $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
    if ($dt->tipo == 'Gestor') {
        if ($row0['status'] > 100) {
            $row0['status'] = $row0['status'] - 100;
        }
        $result = mysqli_query($con, sprintf("UPDATE arq_leads SET user=%s, status=%s WHERE id=%s", $dt->user, $row0['status'], $dt->lead));
        mysqli_query($con, sprintf("UPDATE cad_agenda SET user=%s WHERE lead=%s AND status=1", $dt->user, $dt->lead));
    } elseif ($dt->tipo == 'GRec') {
        if ($row0['status'] < 10) {
            $row0['status'] = $row0['status'] + 100;
        }
        $result = mysqli_query($con, sprintf("UPDATE arq_leads SET user=%s, status=%s WHERE id=%s", $dt->user, $row0['status'], $dt->lead));
        mysqli_query($con, sprintf("UPDATE cad_agenda SET user=%s WHERE lead=%s AND status=1", $dt->user, $dt->lead));
    } elseif ($dt->tipo == 'Analista') {
        $result = mysqli_query($con, sprintf("UPDATE arq_leads SET analista=%s WHERE id=%s", $dt->user, $dt->lead));
    }
    $resp = '{"erro":""}';
    echo $resp;
} else {
    $resp = '{"erro":"Não é possivél realizar esta operação!"}';
    echo $resp;
}   
