<?php

/* 
 * Vai obter os dados do cad_cartaocredito para o dashboard
 *             sugeridos
               aceites 
               listaContratos 
               qtySugeridos 
               qtyAceites
 *            qtyContratos
               atribuidos 
               ativados
 */
require_once '../../php/openCon.php';
$user = file_get_contents("php://input");

$resp = array();
$resp['qtySugeridos'] =0;
$resp['qtyAceites'] = 0;
$resp['qtyContratos'] = 0;


//sugeridos
$query = sprintf("SELECT C.lead, DATE(C.sugerido) AS sugerido, C.formasugestao, L.nome, L.telefone  "
        . " FROM cad_cartaocredito C INNER JOIN arq_leads L ON L.id=C.lead "
        ."  WHERE C.user=%s AND C.status=1 AND L.status!=26 ", $user);
$result = mysqli_query($con, $query);
//echo $query;
if($result){
    $count = 0;
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        if($count<2){
        $result0 = mysqli_query($con, sprintf("SELECT F.datastatus, S.status FROM cad_financiamentos F "
                 . " INNER JOIN cnf_stsfinanciamentos S ON S.id=F.status"
                . " WHERE F.lead=%s  ORDER BY F.datastatus DESC LIMIT 1" , $row['lead']));
        $row['status'] = '';
        if($result0){
             $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
                    $row['status'] = $row0['status'];
        }
        
        array_push($temp, $row);
        }
        $count++;
    }
    $resp['sugeridos'] = $temp;
    $resp['qtySugeridos'] = $count;
}



//aceites
$query = sprintf("SELECT C.lead, DATE(C.dataresposta) AS dataresposta, C.montante, L.nome, L.telefone, L.datastatus  "
        . " FROM cad_cartaocredito C INNER JOIN arq_leads L ON L.id=C.lead "
        ."  WHERE C.user=%s AND C.respostacliente=1  AND C.contratoenviado IS NULL AND C.datarespostaparceiro IS NULL ORDER BY L.datastatus DESC", $user);
$result = mysqli_query($con, $query);
if($result){
    $count = 0;
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        if($count<2){
            array_push($temp, $row);
        }
        $count++;
    }
    $resp['aceites'] = $temp;
    $resp['qtyAceites'] = $count;
}

// lista contratos
$query = sprintf("SELECT C.*,  L.nome, L.telefone, L.datastatus "
        . " FROM cad_cartaocredito C "
        . " INNER JOIN arq_leads L ON L.id=C.lead "
        ."  WHERE C.user=%s AND C.respostaparceiro=1 AND C.datarespostaparceiro IS NOT NULL AND C.status<6 ORDER BY  L.datastatus  DESC", $user);
$result = mysqli_query($con, $query);
if($result){
    $count = 0;
    $temp = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        if($count<2){
            array_push($temp, $row);
        }
        $count++;
    }
    $resp['listaContratos'] = $temp;
    $resp['qtyContratos'] = $count;
}

//Quantidade de atribuidos
$query = sprintf("SELECT count(*) FROM cad_cartaocredito WHERE user=%s AND status IN (5,7)  ", $user);
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['atribuidos'] = $row[0];
}
//Quantidade de ativados
$query = sprintf("SELECT count(*) FROM cad_cartaocredito WHERE user=%s AND status=7 ", $user);
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['ativados'] = $row[0];
}
//Quantidade de recusados
$query = sprintf("SELECT count(*) FROM cad_cartaocredito WHERE user=%s AND status IN(6,9) ", $user);
$result = mysqli_query($con, $query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['recusados'] = $row[0];
}

echo json_encode($resp);