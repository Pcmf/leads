<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);


//Alterar o status da lead
$query = sprintf("UPDATE arq_leads SET status=%s,datastatus=NOW(),user=%s WHERE id=%s",$dt->status,$dt->userId,$dt->lead);
$result = mysqli_query($con,$query);
if($result){
    //Se o novo status for 20 (reanalise) vai colocar os status do cad_financiamentos que estejam como aprovado -> cancelado
    if($dt->status == 20){
        mysqli_query($con, sprintf("UPDATE cad_financiamentos SET status=9, datastatus=NOW() WHERE lead=%s AND status=6 " , $dt->lead));
    }
    // se o status for 8 atualiza a agenda
    if($dt->status==8){
        mysqli_query($con, sprintf("UPDATE cad_agenda SET status= 0 WHERE lead=%s", $dt->lead));
        mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, agendaperiodo, tipoagenda, status) "
                . " VALUES(%s, %s, DATE(NOW() + INTERVAL 1 day), '10:30:00', 1, 3,1 )",
                $dt->lead, $dt->userId));
    }
    
    //Registar no contacto
    $query0 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
        . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
        $dt->lead,$dt->userId,$dt->lead,$dt->userId,1,5);
    $result0 = mysqli_query($con,$query0);
    if($result0){
        
    } else {
        echo $query0;
    }
} else {
  echo $query;   
}


