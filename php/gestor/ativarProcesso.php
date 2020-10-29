<?php

/* 
 * Altera o status de um processo com documentação pedida que estava anulado para Aguarda Documentação
 * Ativa a agenda para a data atual
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt= json_decode($json);

//atualizar o status
$result = mysqli_query($con, sprintf("UPDATE arq_leads SET status=8 WHERE id=%s", $dt->lead->id));
if($result){
    //criar agendamento
    mysqli_query($con, sprintf("INSERT INTO cad_agenda(lead,user,agendadata,tipoagenda,status) "
            . " VALUES(%s,%s,NOW(),3,1)", $dt->lead->id,$dt->user->id));
    
    //Verificar se existe processo. Se não existe cria 
    $result0 = mysqli_query($con, sprintf("SELECT * from arq_processo WHERE lead=%s", $dt->lead->id));
    if(!$result0->num_rows){
       $result1= mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s" , $dt->lead->id));
       if($result1){
           $row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC);
           $query = sprintf("INSERT INTO arq_processo(lead, user, nome,"
                   . " nif, email, telefone, idade, vencimento, tipocredito, valorpretendido) "
                   . " VALUES(%s, %s, '%s', %s, '%s', '%s', %s, %s, '%s', %s)",
                   $row1['id'], $row1['user'], $row1['nome'], $row1['nif'], $row1['email'],
                   $row1['telefone'], $row1['idade'], $row1['rendimento1'], $row1['tipo'],$row1['montante']);
           $result2 =  mysqli_query($con,$query );
           if(!$result){
               mysqli_query($con, sprintf("UPDATE arq_processo SET user=%s, nome='%s', nif='%s', email='%s', telefone='%s', "
                       . "idade=%s, vencimento=%s, tipocredito='%s', valorpretendido=%s WHERE lead=%s ", 
                      $row1['user'], $row1['nome'], $row1['nif'], $row1['email'],
                   $row1['telefone'], $row1['idade'], $row1['rendimento1'], $row1['tipo'], $row1['montante'], $row1['id'] ));
           }
       }
    }
} else {
    echo 'Erro na ativação. Contacte o suporte!';
}