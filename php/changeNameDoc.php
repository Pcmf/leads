<?php

/* 
 * Trocar a designação de um documento
 */

require_once 'openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);
$docOrig = $dt->docOrig;
//alterar a designação do documento no cad_docpedida e no arq_documentacao
$query = sprintf("UPDATE arq_documentacao SET nomefx='%s' WHERE lead=%s AND linha=%s ",
        $dt->docNew[0]->sigla.'.'.$docOrig->tipo,$docOrig->lead,$docOrig->linha);

$query1 = sprintf("UPDATE cad_docpedida SET tipodoc=%s WHERE lead=%s AND linha=%s ",
        $dt->docNew[0]->id,$docOrig->lead,$docOrig->linha);

mysqli_query($con, $query);
mysqli_query($con, $query1);

echo $query.'  |  '.$query1;