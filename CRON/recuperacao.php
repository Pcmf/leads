<?php

//    Seleciona as leads que foram anuladas,pelo status 5 e 9 á mais de 20 dias, e que não tenham mais 60 dias 
// e guarda o numero da lead, user (gestor e analista),  e o status no ficheiro arq_recuperacao


require_once '../php/openCon.php';


$sql = "SELECT L.id, L.user, L.status"
        . " FROM arq_leads L"
        . " LEFT JOIN arq_recuperacao R ON R.lead=L.id "
        . " WHERE R.lead IS NULL AND  L.status IN (5,9) AND DATEDIFF(NOW(), L.datastatus) BETWEEN 30 AND 60";

$result = mysqli_query($con, $sql);

if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        mysqli_query($con, sprintf("INSERT INTO arq_recuperacao(lead, gestor, statusinicial) VALUES(%s, %s, %s)",
                $row['id'], $row['user'], $row['status']));
    }
}

