<?php

/* 
 * Obter as mensagens do mural 
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

//MURAL - Conversas

$query = sprintf("SELECT M.*, U.nome AS userorigem, U1.nome AS userdestino FROM mural M "
        . " INNER JOIN cad_utilizadores U ON U.id=M.origem "
        . " INNER JOIN cad_utilizadores U1 ON U1.id=M.destino "
        . " WHERE  (M.destino=%s OR M.origem=%s) AND (DATEDIFF(DATE(NOW()),DATE(M.dataenvio))<5) ORDER BY M.dataenvio ", $dt->user, $dt->user);
$temp = array();
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        if($row['origem'] == $dt->user){
            $row['sentido'] = 'msg-out';
        } else {
           $row['sentido'] = 'msg-in';
        }
        array_push($temp, $row);
    }
    $resp['conversas'] = $temp;
}

echo json_encode($resp);