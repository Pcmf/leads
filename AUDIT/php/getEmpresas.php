<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';

$resp = array();
$result = mysqli_query($con, "SELECT DISTINCT(empresa) FROM cad_fornecedorleads WHERE ativo=1 ORDER BY empresa");
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
    
}
echo json_encode($resp);