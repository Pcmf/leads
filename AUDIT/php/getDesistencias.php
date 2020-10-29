<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';
$dt = json_decode(file_get_contents("php://input"));
$tm = $dt->tml;
if($tm->opc=='dia'){
    $sel=' DATE(L.datastatus) = DATE(NOW()) ';
}
   
if($tm->opc=='mes'){
        $sel=' YEAR(L.datastatus) = YEAR(NOW()) AND MONTH(L.datastatus) = MONTH(NOW())';
}

if($tm->opc==''){
    $sel = " L.datastatus BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
}



$resp = array();

    if($dt->tipo=="Gestor"){
        //Obter as desistencias se gestores
        $query = sprintf("SELECT L.id, L.nome, L.telefone, L.dataentrada, L.datastatus, "
                . " F.montante, U1.nome AS gestor, U2.nome AS analista FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN cad_utilizadores U1 ON U1.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE L.user=%s AND L.status=18  AND %s GROUP BY L.id", $dt->id, $sel);
        $result = mysqli_query($con, $query);
        if($result){
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                array_push($resp, $row);
            }     
            echo json_encode($resp);
        }
        
    } else {
          //Obter as desistencias se analistas
        $query = sprintf("SELECT L.id, L.nome, L.telefone, L.dataentrada, L.datastatus, "
                . " F.montante, U1.nome AS gestor, U2.nome AS analista FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN cad_utilizadores U1 ON U1.id=L.user "
                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                . " WHERE L.analista=%s AND L.status=18 AND %s GROUP BY L.id", $dt->id, $sel);
    //    echo $query;
        $result = mysqli_query($con, $query);
        if($result){
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                array_push($resp, $row);
            }     
            echo json_encode($resp);
        }      
        
    }
    
 

