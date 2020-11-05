<?php

/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once './openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);
$lead = $dt->items->lead;
$linha = $dt->items->linha;

if ($dt->state == 1) {
  $result =  mysqli_query($con, sprintf("UPDATE cad_docpedida SET aproved = 1, dataaproved=NOW(), notok=null, problem=null, dataproblem=null"
          . " WHERE lead=%s AND linha=%s",
            $lead, $linha));
} else {
  $result =  mysqli_query($con, sprintf("UPDATE cad_docpedida SET aproved = 0, dataaproved=null, notok=1, problem='%s', dataproblem=NOW()"
          . " WHERE lead=%s AND linha=%s",
            $dt->motivoBadDoc, $lead, $linha));
}

echo json_encode($result);