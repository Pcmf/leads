<?php

/* 
 * Calcular o vencimento liquido em função dos valores passados nos parametros
 */

require_once '../openCon.php';
$dt = json_decode(file_get_contents("php://input"));
$resp = array();
$taxaSS = 0.11;
!isset($dt->vencBase1) ? $dt->vencBase1=0 : null;
!isset($dt->vencBase2) ? $dt->vencBase2=0 : null;
!isset($dt->subRef1) ? $dt->subRef1=0 : null;
!isset($dt->subRef2) ? $dt->subRef2=0 : null;
!isset($dt->subRefValor1) ? $dt->subRefValor1=0 : null;
!isset($dt->subRefValor2) ? $dt->subRefValor2=0 : null;
!isset($dt->subRefDias1) ? $dt->subRefDias1=0 : null;
!isset($dt->subRefDias2) ? $dt->subRefDias2=0 : null;
!isset($dt->outrosRend1) ? $dt->outrosRend1=0 : null;
!isset($dt->outrosRend2) ? $dt->outrosRend2=0 : null;
!isset($dt->outrosRendIRS1) ? $dt->outrosRendIRS1=0 : null;
!isset($dt->outrosRendIRS2) ? $dt->outrosRendIRS2=0 : null;
!isset($dt->outrosRendIsentos1) ? $dt->outrosRendIsentos1=0 : null;
!isset($dt->outrosRendIsentos2) ? $dt->outrosRendIsentos2=0 : null;

switch ($dt->situacao) {
    case 0:
        $situacao = 0;
        $titulares = 1;
        break;
    case 1:
        $situacao = 1;
        $titulares = 1;
        break;
    case 2:
        $situacao = 1;
        $titulares = 2;
        break; 
    default:
        $situacao = 0;
        $titulares = 1;
        break;
}
if($dt->vencBase1){
    $subRef1 = calculaSubRef($dt->subRef1, $dt->subRefValor1, $dt->subRefDias1);
    $rendTributavel1 = $dt->vencBase1 + $dt->outrosRend1 + $subRef1['tributavel'] + $dt->outrosRendIRS1;
    $taxa1 = getTaxas($con, $situacao, $titulares, $dt->filhos, $rendTributavel1);
    $resp['vencLiq1'] = round($rendTributavel1 - $rendTributavel1*$taxa1 - ($rendTributavel1-$dt->outrosRendIRS1)*$taxaSS
            + $subRef1['naoTributavel'] + $dt->outrosRendIsentos1, 2);

    if(isset($dt->subRef2)){
        $subRef2 = calculaSubRef($dt->subRef2, $dt->subRefValor2, $dt->subRefDias2);
        $rendTributavel2 = $dt->vencBase2 + $dt->outrosRend2 + $subRef2['tributavel'] + $dt->outrosRendIRS2;
        $taxa2 = getTaxas($con, $situacao, $titulares, $dt->filhos, $rendTributavel2);
        $resp['vencLiq2'] = round($rendTributavel2 - $rendTributavel2*$taxa2 - ($rendTributavel2 - $dt->outrosRendIRS2)*$taxaSS 
                + $subRef2['naoTributavel'] + $dt->outrosRendIsentos2, 2);
}

echo json_encode($resp);
}
//Fim






function getTaxas($con, $situacao, $titulares, $filhos, $vencimento) {
    $filhos>5 ? $filhos=5 : null;
    $result = mysqli_query($con, sprintf("SELECT taxa FROM t_retencao WHERE situacao=%s AND titulares=%s AND filhos=%s "
            . " AND vencimentos>%s ORDER BY vencimentos ASC LIMIT 1", $situacao, $titulares, $filhos, $vencimento));
    if($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return $row['taxa'];
    } else {
        return 0;
    }
}



function calculaSubRef($tipo, $valorDia, $dias) {
    $resp = array();
    $resp['tributavel'] = 0;
    switch ($tipo){
    case 0:
        $resp['naoTributavel'] = 0;
        return $resp;
        break;
    case 1: //Remuneração - 4.77
        $max = 4.77;
        break;
    case 2:   //Cartão - 7.63
        $max = 7.63;
        break; 
    default:
        break;   
    }
    
    if( $valorDia >= $max) {
            $resp['tributavel'] = ($valorDia - $max) * $dias;
            $resp['naoTributavel']  = $max * $dias;
    } else {
            $resp['naoTributavel'] = $valorDia * $dias;
    }
    return $resp;
}