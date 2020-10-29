<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

if($dt->f->status != 6){
  //  Alterar status da lead e do financiamento
  mysqli_query($con, sprintf("UPDATE arq_leads SET status=13, datastatus=NOW() WHERE id=%s ", $dt->f->lead));
  mysqli_query($con, sprintf("UPDATE cad_financiamentos SET tipocredito='%s', montante=%s, prazo=%s, prestacao=%s, status=%s, "
          . "dtcontratocliente=null, dtcontratoparceiro=null, formaenvio=null "
          . " WHERE lead=%s AND processo='%s' ",
        $dt->f->tipocredito, $dt->f->montante, $dt->f->prazo, $dt->f->prestacao, $dt->f->status, $dt->f->lead, $dt->f->processo));
} else {
//Atualizar dados do financiamento
    $query =sprintf("UPDATE cad_financiamentos SET tipocredito='%s', montante=%s, prazo=%s, prestacao=%s WHERE lead=%s AND processo='%s' ",
        $dt->f->tipocredito, $dt->f->montante, $dt->f->prazo, $dt->f->prestacao, $dt->f->lead, $dt->f->processo);
    $result = mysqli_query($con, $query);
    if($result){
        mysqli_query($con, sprintf("INSERT INTO cad_justaltfinanc(lead, user, justificacao,oldfinanc) VALUES(%s,%s,'%s','%s') ", $dt->f->lead,$dt->user->id,$dt->justif, json_encode($dt->oldF) ));
    } else {
        echo $query;
    }
}
