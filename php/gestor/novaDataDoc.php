<?php

/* 
 * Alterar a data prevista para receber a documentação
 * 
 * no cad_agenda muda o status para 0 na atual e insere uma nova com o status a 1 e tipo 3
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

//Desativa a anterior
mysqli_query($con, sprintf("UPDATE cad_agenda SET status =0 WHERE lead=%s AND status=1", $dt->lead));

//Insere novo registo
mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead,user,agendadata, tipoagenda,status) VALUES(%s,%s,'%s',3,1)" , $dt->lead, $dt->user, $dt->data));

return;

