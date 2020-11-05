<?php

/* 
 * Faz o agendamento 
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$hora = substr($dt->hora, 11, 5);
$hora = (substr($hora,0,2)+1).':'.substr($hora,-2).':00';

//Limpa agendamentos anteriores
mysqli_query($con , sprintf("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=%s", $dt->lead));

$query = sprintf("INSERT cad_agenda( lead, user, agendadata, agendahora, tipoagenda, status) VALUES(%s, %s, '%s', '%s', 2, 1)" ,
        $dt->lead, $dt->user, $dt->data, $hora);

$result = mysqli_query($con, $query);
if($result){
    mysqli_query($con, sprintf("UPDATE arq_leads SET status=107, datastatus=NOW() WHERE id=%s", $dt->lead));
    
    //faz o registo do contacto
    mysqli_query($con, sprintf("INSERT INTO cad_registocontacto( lead, user, contactonum, motivocontacto) "
            . " VALUES(%s, %s, (SELECT max(contactonum)+1 FROM cad_registocontacto A WHERE A.lead=%s), 6)",
            $dt->lead, $dt->user, $dt->lead));
}


echo json_encode($result);

