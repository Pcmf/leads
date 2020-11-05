<?php
/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$result = mysqli_query($con, sprintf("INSERT INTO cad_hipotecario(lead, garantia, valordivida, valorpretendido) VALUES(%s, %s, %s, %s)",
        $dt->lead, $dt->garantia, $dt->valordivida, $dt->valorpretendido));
echo json_encode($result);
if(!$result) {
    mysqli_query($con, sprintf("UPDATE cad_hipotecario SET garantia=%s, valordivida=%s, valorpretendido=%s WHERE lead=%s",
           $dt->garantia, $dt->valordivida, $dt->valorpretendido, $dt->lead));
}
