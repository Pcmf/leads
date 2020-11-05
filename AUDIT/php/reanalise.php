<?php

/* 
 * Obter as leads recusadas com datastatus de 5 dias atras até ao momento e que 
 * não estejam no ficheiro cad_audit
 */
require_once '../../php/openCon.php';

$resp = array();

$result = mysqli_query($con, "SELECT P.lead, P.nome, P.valorpretendido AS montante,"
        . " L.dataentrada, L.datastatus, DATEDIFF(NOW(), L.datastatus)AS dias, U.nome AS analista, "
        . " S.nome AS statusnome"
        . " FROM cad_audit A "
        . " INNER JOIN arq_leads L ON L.id=A.lead "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
        . " INNER JOIN cnf_statuslead S ON S.id=L.status "
        . " WHERE DATEDIFF(NOW(), A.data)<=20 AND A.status=1 ");

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