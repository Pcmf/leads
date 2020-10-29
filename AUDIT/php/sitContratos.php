<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../php/openCon.php';

$querys = array();
$analista =0;
// Por enviar
array_push($querys,  "SELECT count(*) AS qty, L.analista, U.nome, SUM(F.montante) AS valor, MIN(DATE(F.dataaprovado)) AS data "
                        . " FROM `cad_financiamentos` F"
                        ." INNER JOIN arq_leads L ON L.id=F.lead "
                        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                        ." WHERE F.status=6 and F.dtcontratocliente IS NULL and F.dt2via IS NULL AND F.dtcontratoparceiro IS NULL AND L.status=16 AND L.analista = %s");


// No Cliente
array_push($querys, "SELECT count(*) AS qty, L.analista, U.nome, SUM(F.montante) AS valor, MIN(DATE(F.dtcontratocliente)) AS data "
        . " FROM `cad_financiamentos` F"
                        ." INNER JOIN arq_leads L ON L.id=F.lead "
                        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                        ." WHERE F.status=6 and F.dtcontratocliente IS NOT NULL and F.dt2via IS NULL AND F.dtcontratoparceiro IS NULL AND L.status=16 AND L.analista = %s");

// Segunda Via
array_push($querys, "SELECT count(*) AS qty, L.analista, U.nome, SUM(F.montante) AS valor, MIN(DATE(F.dt2via)) AS data FROM `cad_financiamentos` F "
                        ." INNER JOIN arq_leads L ON L.id=F.lead "
                        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                        ." WHERE F.status=6 AND F.dtcontratocliente IS NOT NULL AND F.dt2via IS NOT NULL AND F.dtcontratoparceiro IS NULL AND L.status=16 AND L.analista = %s");


// No Parceiro
array_push($querys,  "SELECT count(*) AS qty, L.analista, U.nome, SUM(F.montante) AS valor, MIN(DATE(F.dtcontratoparceiro)) AS data FROM `cad_financiamentos` F "
                        ." INNER JOIN arq_leads L ON L.id=F.lead "
                        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                        ." WHERE F.status=6 AND F.dtcontratoparceiro IS NOT NULL AND L.status=16 AND L.analista = %s");

// Em suspenso
array_push($querys, "SELECT count(*) AS qty, L.analista, U.nome, SUM(F.montante) AS valor, MIN(DATE(F.dataaprovado)) AS data  FROM `cad_financiamentos` F"
                        ." INNER JOIN arq_leads L ON L.id=F.lead "
                        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
                        ." WHERE F.status=6 AND L.status=41 AND L.analista = %s");

$resp = array();

$estados = [
    'porEnviar',
    'noCliente',
    'en2via',
    'noParceiro',
    'suspenso'
];

$analistas = array();
$result0 = mysqli_query($con, "SELECT id, nome FROM cad_utilizadores WHERE tipo='Analista' AND ativo=1 ORDER BY nome");
if($result0) {
    while ($row1 = mysqli_fetch_array($result0, MYSQLI_ASSOC)) {
        array_push($analistas, $row1);
    }
}


foreach ($analistas as $ln) {

$i =0;
$tempEst = array();
    $tempEst['analista'] = $ln ;
    foreach ($estados AS $estado){
        $result = mysqli_query($con, sprintf($querys[$i],$ln['id']));
        if($result){
            $temp = array();
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                array_push($temp, $row);
            }
            $tempEst[$estado] = $temp;
        }
        $i++;
    }
    array_push($resp, $tempEst);
}
echo json_encode($resp);

