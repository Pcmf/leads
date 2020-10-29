<?php
require_once '../openCon.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);


//Alterar o status da lead
$query = sprintf("UPDATE arq_leads SET status=%s, datastatus=NOW(), user=%s WHERE id=%s",$dt->status,$dt->user->id,$dt->lead);
$result = mysqli_query($con,$query);
if($result){
    echo 'OK';
} else {
    echo $query;
}
