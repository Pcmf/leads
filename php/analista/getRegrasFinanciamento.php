<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';

$resp = array();

$query = sprintf("SELECT P.nome, R.* FROM cnf_regrasfinanciamento R INNER JOIN cad_parceiros P ON P.id=R.parceiro WHERE P.ativo=1 AND tipoparceiro=0 ");

$result = mysqli_query($con, $query);

if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
}

echo json_encode($resp);
