<?php

session_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);

//Validar os campos
!isset($dt->id) ? $dt->id = 0 : null;
!isset($dt->filtro) ? $dt->filtro = "" : null;
!isset($dt->codigo) ? $dt->codigo = "" : null;
!isset($dt->nome) ? $dt->nome = "" : null;
!isset($dt->status) ? $dt->status = 1 : null;


if ($dt->id > 0) {
    $result = mysqli_query($con, sprintf("UPDATE cad_filtros SET filtro='%s', codigo='%s', nome='%s', status=%s "
                    . " WHERE id=%s",$dt->filtro, $dt->codigo, $dt->nome, $dt->status, $dt->id));
} else {
    $result = mysqli_query($con, sprintf("INSERT INTO cad_filtros(filtro, codigo, nome) "
                    . " VALUES('%s', '%s', '%s')", $dt->filtro, $dt->codigo, $dt->nome));
}

echo json_encode($result);
