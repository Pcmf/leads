<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

    $list = array();

    $query = sprintf("SELECT L.id,L.nome,L.dataentrada,L.nif,L.email,L.telefone,S.nome AS status,L.datastatus,U.nome AS usernome,U1.nome AS analista,L.montante "
            . " FROM arq_leads L "
            . " INNER JOIN cnf_statuslead S ON S.id=L.status"
            . " INNER JOIN cad_utilizadores U ON U.id=L.user "
            . " LEFT JOIN cad_utilizadores U1 ON U1.id=L.analista "
            . " WHERE L.id<>%s AND ((L.nif=%s AND L.nif>0) OR (L.email='%s' AND L.email<>'') OR (L.telefone='%s' AND L.telefone<>'')) ",
            $dt->lead, $dt->nif, $dt->email, $dt->telefone);
    $result = mysqli_query($con, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($list, $row);
        }
        echo json_encode( $list);
    } else {
        echo NULL;
    }
