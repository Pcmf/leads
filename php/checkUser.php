<?php session_start();

require_once 'openCon.php';
require_once 'passwordHash.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);
$user = json_decode($dt->params);

$resp = array();

$result = mysqli_query($con,sprintf("SELECT * FROM cad_utilizadores WHERE username='%s'",$user->userName));

if($result){
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    if($row['id']>0){
        //Validar password
        if(passwordHash::check_password($row['password'],$user->pwd)){
            $_SESSION['valid_ID'] = true;
            $_SESSION['user'] = $row['id'];
            $resp['aviso'] = "";
            $resp['id']= $row['id'];
            $resp['nome']= $row['nome'];
            $resp['email']= $row['email'];
            $resp['tipo']= $row['tipo'];
            $resp['telefone']= $row['telefone'];
            $resp['deviceId']= $row['deviceid'];
            $resp['presenca']= $row['presenca'];
            
        } else{
            $resp['aviso']  = 'Erro na password! Verifique e tente outra vez.';
        }
    } else {
    $resp['aviso']  = 'Erro no utilizador ou na password! Verifique e tente outra vez.';
    }
}

echo json_encode($resp);