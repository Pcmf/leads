<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$userId = file_get_contents("php://input");

$resp=array();

$query = sprintf("SELECT P.lead, P.nome, P.nif, P.valorpretendido, DATE(L.datastatus) AS dtstatus, L.status, L.tipo, S.nome AS stsnome, S.descricao "
        . " FROM arq_processo P "
        . " INNER JOIN arq_leads L ON L.id=P.lead "        
        . " INNER JOIN cnf_statuslead S ON L.status = S.id "
        . " WHERE L.status IN(12,13,20,21,22) AND L.analista=%s "
        . " ORDER BY L.status, L.datastatus ASC",$userId);
$result=mysqli_query($con,$query);
if($result){
    while ($row1 = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        if($row1['status']==13){
            $query0 = sprintf("SELECT S.status, P.nome AS parceiro, F.datastatus"
                . " FROM cad_financiamentos F "
                . " INNER JOIN cnf_stsfinanciamentos S ON S.id=F.status "
                . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
                . " WHERE F.lead=%s ORDER BY F.datastatus DESC ",$row1['lead']);
            $result0 = mysqli_query($con,$query0);
            if($result0){
                $temp = array();
                while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)){
                    array_push($temp, $row0);
                }
                $row1['parcerias'] = $temp;
            }
        }
        
        array_push($resp, $row1);
    }
    echo json_encode($resp);
} else {
    echo $query;
}