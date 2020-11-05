<?php
date_default_timezone_set('Europe/Lisbon');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);
$hora = substr($dt->ag->hora, 11, 5);
$hora = (substr($hora,0,2)+1).':'.substr($hora,-2).':00';

//$data1 = date('Y-m-d',strtotime(substr($dt->ag->data, 0, 10))+(24*3600));
$data1 = $dt->ag->data;

if(!isset($dt->ag->periodo)){
    if(substr($hora,0,2)<13){
        $periodo = 1;
    } else {
        $periodo =2;
    }
}
$sts = 7;
// Verificar se é agendamento de recuperação
isset($dt->rec) && $dt->rec ? $sts=32 : $sts= 7;

          //coloca status a zero antes de fazer um novo agendamento 
           mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",$dt->lead->id,$dt->userId));
          //Agendar
           mysqli_query($con,sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
                   . " VALUES(%s,%s,'%s','%s',%s,2,1)",$dt->lead->id,$dt->userId,$data1, $hora,$periodo));
           //atualizar o status da lead
           //Alterar o status da lead para Agendada
            $query = sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW(),user=%s WHERE id=%s", $sts, $dt->userId,$dt->lead->id);
            mysqli_query($con,$query);
//          Registar no contacto
            $query0 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
                . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
                $dt->lead->id,$dt->userId,$dt->lead->id,$dt->userId,0,6);
            mysqli_query($con,$query0);
            
// Guardar os dados do processo que tenham sido preenchidos num fx temporario e em formato json, Apenas quando é preenchida a linha da profissão
           
//    if(isset($dt->lead->profissao) && isset($dt->lead->tipocontrato) && isset($dt->leadanoinicio)){
        $query =sprintf("INSERT INTO cad_agendatemp(lead,processo) VALUES(%s,'%s')",$dt->lead->id, json_encode($dt->lead));
        $result = mysqli_query($con, $query);
        if(!$result){
            echo $query;
        }
//    }
            
            
            
   echo "  agendado para ".$data1.'  hora '.$hora;
