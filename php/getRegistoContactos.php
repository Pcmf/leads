<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'openCon.php';

$dt = json_decode(file_get_contents("php://input"));

$resp = array();

//Historico dos contactos
    $query = sprintf("SELECT H.lead, H.data, S.descricao, U.nome, U.tipo "
            . " FROM `arq_histprocess` H "
            . " INNER JOIN cnf_statuslead S ON S.id=H.status"
            . " LEFT JOIN cad_utilizadores U ON (U.id=H.user OR  U.id= H.analista) "
            . " WHERE `lead` = %s GROUP BY H.data", $dt->lead);
    $result=mysqli_query($con,$query);
    if($result){
        $temp=array();
        while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            array_push($resp, $row);
        }
    }
    $query = sprintf("SELECT R.lead, R.dtcontacto aS data, M.descricao, U.nome, U.tipo "
        ." FROM cad_registocontacto R " 
        ." INNER JOIN cnf_motivocontacto M ON M.id=R.motivocontacto "
        ." INNER JOIN cad_utilizadores U ON U.id=R.user "
        ." WHERE R.lead= %s", $dt->lead);
    $result=mysqli_query($con,$query);
    if($result){
    //    $temp=array();
        while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            array_push($resp, $row);
        }

    }   
    
    echo json_encode($resp);