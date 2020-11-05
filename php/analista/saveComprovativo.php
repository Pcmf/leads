<?php
/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

if(!isset($dt->novonome)) {
    $dt->novonome = $dt->file->filename;
} else {
    $dt->novonome = $dt->novonome.'.pdf';
}

//Inserir os documentos anexados em base64
$query = sprintf("UPDATE cad_comprovativos  SET nomedoc='%s', documento='%s', tipodoc='%s', status=1 WHERE lead=%s AND linha=%s  "
        ,$dt->novonome,$dt->file->base64,substr($dt->file->filetype,strpos($dt->file->filetype,"/")+1), $dt->lead,$dt->linha );
$result = mysqli_query($con,$query);
if($result){
    

    echo 'OK';
} else {
    echo $query;
    echo "Erro sADA26! Não foi possivél fazer o upload do ficheiro. Entre em contacto com suporte!"; 
}
