<?php
date_default_timezone_set('Europe/Lisbon');
/* 
 * Agendamento para o proximo dia 22, do mes atual ou seguinte
 */
require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);


$dia = date('j');
$mes = date('n');
$ano = date('Y');

if($dia >= 21){
    if($mes == 12) {
        $mes=1; $ano++;
    } else {
        $mes++;
    }
}

$data1= $ano.'-'.$mes.'-22';
$hora = '10:00:00';
$periodo = 1;
          //coloca status a zero antes de fazer um novo agendamento 
           mysqli_query($con,sprintf("UPDATE cad_agenda SET status=0 WHERE lead=%s AND user=%s",$dt->lead->id,$dt->userId));
          //Agendar
           mysqli_query($con,sprintf("INSERT INTO cad_agenda(lead,user,agendadata,agendahora,agendaperiodo,tipoagenda,status) "
                   . " VALUES(%s,%s,'%s','%s',%s,4,1)",$dt->lead->id,$dt->userId,$data1, $hora,$periodo));
           //atualizar o status da lead
           //Alterar o status da lead para Agendada
            $query = sprintf("UPDATE arq_leads SET status=7,datastatus=NOW(),user=%s WHERE id=%s",$dt->userId,$dt->lead->id);
            mysqli_query($con,$query);
//          Registar no contacto
            $query0 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envemail,motivocontacto) "
                . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s,%s)",
                $dt->lead->id,$dt->userId,$dt->lead->id,$dt->userId,0,6);
            mysqli_query($con,$query0);
            
// Guardar os dados do processo que tenham sido preenchidos num fx temporario e em formato json, Apenas quando é preenchida a linha da profissão
           
        $query =sprintf("INSERT INTO cad_agendatemp(lead,processo) VALUES(%s,'%s')",$dt->lead->id, json_encode($dt->lead));
        $result = mysqli_query($con, $query);
        if(!$result){
            echo $query;
        }
            
            
            
   echo "  agendado para ".$data1.'  hora '.$hora;

