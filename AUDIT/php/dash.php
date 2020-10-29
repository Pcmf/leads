<?php

/* 
 * Obter as leads recusadas com datastatus de 5 dias atras até ao momento e que 
 * não estejam no ficheiro cad_audit
 */
require_once '../../php/openCon.php';
$tm = json_decode(file_get_contents("php://input"));

if($tm->opc=='dia'){
    $sel=' DATE(L.datastatus) = DATE(NOW())';
    
}
   
if($tm->opc=='mes'){
        $sel=' YEAR(L.datastatus) = YEAR(NOW()) AND MONTH(L.datastatus) = MONTH(NOW())';
}

if($tm->opc==''){
    $sel = " L.datastatus BETWEEN '".$tm->data11." 00:00:00' AND '".$tm->data22." 23:59:59' ";
}

if($tm->analista != 99) {
    $analista = " AND L.analista=".$tm->analista;
} else {
    $analista="";
}

$resp = array();

$result = mysqli_query($con, "SELECT P.lead, P.nome, P.valorpretendido AS montante,"
        . " L.dataentrada, L.datastatus, DATEDIFF(NOW(), L.datastatus)AS dias, U.nome AS analista  "
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
        . " WHERE ".$sel." AND L.status IN(14,15) "
//        . " WHERE DATEDIFF(NOW(), L.datastatus)<=5 AND L.status IN(14,15) "
        . " AND L.id NOT IN(SELECT lead FROM cad_audit)".$analista);

if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $result0 = mysqli_query($con, sprintf("SELECT motivo, outro, DATE(data) AS data FROM cad_rejeicoes WHERE lead=%s", $row['lead']));
        $temp = array(); 
        if($result0){
            while ($row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
                array_push($temp, $row0);
            }
        }
        $row['motivos'] = $temp;
        array_push($resp,$row);
    }
}
echo json_encode($resp);