<?php

/* 
 * Este programa vai eliminar documentação de processos que foram anulados há mais de 60 dias
 * 
 * status: 4, 9, 14, 15, 25, 26, 109 
 */

require_once '../php/openCon.php';


$result = mysqli_query($con, "SELECT DISTINCT(L.id)  FROM `arq_leads` L "
                                    ." LEFT JOIN arq_documentacao D ON D.lead=L.id "
                                    ." WHERE status IN(4, 9, 14, 15, 25, 26, 109 ) AND DATEDIFF(NOW(), datastatus) > 60 "
                                    . " AND D.lead IS NOT null");


if($result) {
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        mysqli_query($con, sprintf("DELETE FROM arq_documentacao WHERE lead=%s ", $row['id']));
    }
}
