<?php

/* 
 * Adicionar uma linha aos creditos
 */

require_once './openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();

$linha=1;
$rsl = mysqli_query($con, sprintf("SELECT max(linha) FROM cad_outroscreditos  WHERE lead=%s ", $dt->lead));
if($rsl){
    $r = mysqli_fetch_array($rsl,MYSQLI_NUM);
    $r[0]>0 ? $linha = $r[0] +1 : $linha=1;
}


mysqli_query($con, sprintf("INSERT INTO cad_outroscreditos(lead, linha, tipocredito, valorcredito, prestacao, liquidar) "
        . " VALUES(%s, %s, '%s', %s, %s, %s) "
        , $dt->lead, $linha, $dt->oc->tipocredito, $dt->oc->valorcredito, $dt->oc->prestacao, $dt->oc->liquidar));

    $result2 = mysqli_query($con, sprintf("SELECT * FROM cad_outroscreditos WHERE lead=%s ", $dt->lead));
    if($result2){
        $temp = array();
        while ($row = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
            array_push($temp, $row);
        }
        $resp["msg"]="OK";
        $resp["creditos"] = $temp;
        echo json_encode($resp);
    } else {
        $resp["msg"]="Erro 1";
        echo json_encode($resp);
    }

