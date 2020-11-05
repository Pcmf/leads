<?php

/* 
 * Envia email para cliente e guarda na BD
 * @param: lead, e (assunto e texto)
 * @return: array historico de comunicações
 */

require_once './openCon.php';
require_once '../class/sendEmail.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

$resp = array();

//Obter dados do cliente e do gestor
if($dt->tipo=='G'){
    $query = sprintf("SELECT L.nome, L.user, L.email AS emailDestino, U.email AS emailOrigem"
        . " FROM arq_leads L "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.id=%s" , $dt->lead);
} else {
     $query = sprintf("SELECT L.nome, L.analista AS user, L.email AS emailDestino, U.email AS emailOrigem"
        . " FROM arq_leads L "
        . " INNER JOIN cad_utilizadores U ON U.id=L.analista "
        . " WHERE L.id=%s" , $dt->lead);   
}


$result = mysqli_query($con, $query);
if($result){
         $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
         if($row['user']){
         $assunto = "Ref ".$dt->lead.": ".$dt->e->assunto;
         
         $msg = "<p>Exmo(a). Sr(a) ".$row['nome']."</p>"
                 .nl2br($dt->e->texto);

        $send = new sendEmail($con, $row['user'], $row['emailOrigem'], $row['emailDestino'], $assunto, $msg , '', 4, $dt->lead);

        if($send){
            //guardar no historico
            mysqli_query($con, sprintf("INSERT INTO arq_comunicacoes(lead, assunto, texto, user) VALUES(%s, '%s', '%s', %s) ",
                    $dt->lead, $dt->e->assunto, $dt->e->texto, $row['user']));
            //Obter o novo historico
            $temp = array();
            $resultHist = mysqli_query($con, sprintf("SELECT * FROM arq_comunicacoes WHERE lead=%s", $dt->lead));
            if($resultHist){
                while ($row1 = mysqli_fetch_array($resultHist)) {
                    array_push($temp, $row1);
                }
                $resp['comunicacoes'] = $temp;
                $resp['msg']="Enviado";
            } else {
                $resp['msg'] = "Erro no envio! \n".$send;      
            }

        }
         } else {
             $resp['msg'] = "Erro! Utilizador não está defenido.\n  Contactar suporte!"; 
         }
} else {
    $resp['msg'] = "Erro! \n Contactar suporte!";  
} 

echo json_encode($resp);