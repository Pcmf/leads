<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);



//coloca status a zero antes de fazer um novo agendamento 
 mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND status=1",$dt->lead));
//Agendar
 mysqli_query($con,sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
         . " VALUES(%s,%s,'%s','09:00:00',1,3,1)",$dt->lead,$dt->user,$dt->data));
 //limpa agendaDoc
 mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s",$dt->lead ));
 
//          Registar no contacto
  $query0 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
      . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
      $dt->lead, $dt->user, $dt->lead, $dt->user, 0, 6);
  $result = mysqli_query($con,$query0);

  echo json_encode($result);