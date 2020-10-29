<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';

$dt = json_decode(file_get_contents("php://input"));

// No Cliente
if($dt->tipo == 1) {
        $query =  sprintf("SELECT F.*, L.id, P.nome, P.telefone, L.dataentrada, L.datastatus,  U.nome AS gestor, U2.nome AS analista "
                        . " FROM cad_financiamentos F "
                        ." INNER JOIN arq_leads L ON L.id=F.lead "
                        . " INNER JOIN arq_processo P ON P.lead=F.lead "
                        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                        . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                        ." WHERE F.status=6 AND F.dtcontratocliente IS NULL AND F.dt2via IS NULL "
        . " AND F.dtcontratoparceiro IS NULL AND L.status=16 AND L.analista = %s", $dt->analista->id);
   
        
// No Cliente        
} elseif ($dt->tipo == 2) {
        $query =  sprintf("SELECT F.* , L.id, P.nome, P.telefone, L.dataentrada, L.datastatus,  U.nome AS gestor, U2.nome AS analista "
                                . " FROM `cad_financiamentos` F"
                                ." INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                                ." WHERE F.status=6 AND F.dtcontratocliente IS NOT NULL AND F.dt2via IS NULL  "
                . " AND F.dtcontratoparceiro IS NULL AND L.status=16 AND L.analista = %s", $dt->analista->id); 
        
        
// Segunda Via
} elseif ($dt->tipo == 3) {
        $query =  sprintf("SELECT F.* , L.id, P.nome, P.telefone, L.dataentrada, L.datastatus,   U.nome AS gestor, U2.nome AS analista "
                                . " FROM `cad_financiamentos` F"
                                ." INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                                ." WHERE F.status=6 AND F.dtcontratocliente IS NOT NULL AND F.dt2via IS NOT NULL "
                . " AND F.dtcontratoparceiro IS NULL AND L.status=16 AND L.analista = %s", $dt->analista->id);


        
// No Parceiro
} elseif ($dt->tipo == 4) {
        $query =  sprintf("SELECT F.* , L.id, P.nome, P.telefone, L.dataentrada, L.datastatus,   U.nome AS gestor, U2.nome AS analista "
                                . " FROM `cad_financiamentos` F"
                                ." INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                                ." WHERE F.status=6 AND F.dtcontratoparceiro IS NOT NULL AND L.status=16 AND L.analista = %s",
                                $dt->analista->id);

// Suspensos      
} elseif ($dt->tipo == 5) {
        $query =  sprintf("SELECT F.* , L.id, P.nome, P.telefone, L.dataentrada, L.datastatus,  U.nome AS gestor, U2.nome AS analista "
                                . " FROM `cad_financiamentos` F"
                                ." INNER JOIN arq_leads L ON L.id=F.lead "
                . " INNER JOIN arq_processo P ON P.lead=F.lead "
                                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                                . " INNER JOIN cad_utilizadores U2 ON U2.id=L.analista "
                                ." WHERE F.status=6 AND L.status=41 AND L.analista = %s", $dt->analista->id);
}


$resp = array();
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($resp, $row);
    }
}
echo json_encode($resp);