<?php

/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);



//Inserir os documentos anexados em base64
//$query = sprintf("INSERT INTO arq_documentacao(lead,linha,nomefx,tipo,fx64) "
//        . " VALUES(%s,%s,'%s','%s','%s')",$dt->lead,$dt->docAnx->linha,$dt->novonome,substr($dt->file->filetype,strpos($dt->file->filetype,"/")+1),$dt->file->base64);
//$result = mysqli_query($con,$query);
//if($result){
    //Registar o documento anexado
    $query0 = sprintf("UPDATE cad_docpedida SET recebido=1,datarecebido=NOW() "
        . " WHERE lead=%s AND tipodoc=%s AND linha=%s",$dt->lead,$dt->docAnx->tipodoc,$dt->docAnx->linha );
    mysqli_query($con,$query0);

    echo 'OK';
//} else {
//    echo "Erro sADA26! Não foi possivél fazer o upload do ficheiro. Entre em contacto com suporte!"; 
//}




