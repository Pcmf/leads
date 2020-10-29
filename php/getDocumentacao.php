<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

    $resp = array();
    if(isset($dt->linha)){
        $query = sprintf("SELECT D.*,N.nomedoc,N.descricao,N.sigla,F.tipo,F.nomefx, F.fx64,F.size "
            . " FROM cad_docpedida D "
            . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc"
            . " LEFT JOIN arq_documentacao F ON F.lead= D.lead AND F.linha=D.linha AND D.recebido=1 "
            . " WHERE D.lead=%s AND D.linha=%s",$dt->lead,$dt->linha);
    } else {
        $query = sprintf("SELECT D.*,N.nomedoc,N.descricao,N.sigla,F.tipo,F.nomefx, F.fx64,F.size "
            . " FROM cad_docpedida D "
            . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc"
            . " LEFT JOIN arq_documentacao F ON F.lead= D.lead AND F.linha=D.linha AND D.recebido=1 "
            . " WHERE D.lead=%s GROUP BY D.lead,D.linha",$dt->lead);
    }
    //Obter qual documentação que foi pedida
    $result0=mysqli_query($con,$query);
    if($result0){
        
        while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
            array_push($resp, $row0);
        }
        echo json_encode($resp);
    }