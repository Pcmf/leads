<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$dt = json_decode(file_get_contents("php://input"));

$resp= array();
//Anuladas 
$query = sprintf("SELECT L.*,S.nome AS stsnome, S.descricao FROM arq_leads L "
        . " INNER JOIN cnf_statuslead S ON L.status=S.id "
        . " WHERE L.status=%s AND L.user=%s AND DATEDIFF(NOW(), datastatus)<60 ",$dt->type, $dt->userId);
$result = mysqli_query($con,$query);
if($result){
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        $row['docpedida'] =0;
        //Verificar se tem documentação pedida. 
        $result0 = mysqli_query($con, sprintf("SELECT *  FROM cad_docpedida WHERE lead=%s",$row['id']));
        $row['docpedida'] = mysqli_num_rows($result0);
        array_push($resp, $row);
    }
    echo json_encode($resp);
} else {
    echo $query;
}
