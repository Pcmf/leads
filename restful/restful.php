<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
require_once '../php/passwordHash.php';
require_once 'DB.php';
$db = new DB();

//getInfo(token, numTelcliente) ->lead, nome
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['url'] == "info") {
        if (checkToken($_GET['id'])) {
            //Regista a entrada de uma chamada
            $db->query("INSERT INTO cad_registochamadas(lead,horainicio,duracao,telefone,sentido) VALUES(:lead,:horainicio,:duracao,:telefone,:sentido)"
                    , array(':lead' => 0, ':horainicio' => 0, ':duracao' => 0, ':telefone' => $_GET['telefone'], ':sentido' => 'IN'));
            //devolve numero e nome do cliente 
            $result = $db->query("SELECT lead,nome FROM arq_processo WHERE telefone=:telefone ORDER BY lead DESC LIMIT 1"
                    , array(':telefone' => $_GET['telefone']));
            if (!($result)) {
                $result = $db->query("SELECT id AS lead,nome FROM arq_leads WHERE telefone=:telefone ORDER BY id DESC LIMIT 1"
                        , array(':telefone' => $_GET['telefone']));
            }
            echo json_encode($result);

            http_response_code(200);
        } else {
            http_response_code(401);
        }
    } else {
        http_response_code(405);
    }


//POSTS   
//LOGIN
} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
    //LOG IN
    if ($_GET['url'] == "auth") {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);
        $user = $postBody->user;
        $pass = $postBody->pass;
        //Verificar se utilizador existe
        if ($resp = $db->query("SELECT id,nome,tipo,password FROM cad_utilizadores WHERE username=:user", array(':user' => $user))) {
            //verificar se a password e utilizador correspondem
            $found = false;
            foreach ($resp AS $r) {
                if (passwordHash::check_password($r['password'], $pass)) {
                    //Gera um ID
                    $id = passwordHash::hash($r['password']);
                    //guarda na Na tabela dos utilizadores
                    $db->query("UPDATE cad_utilizadores SET appid=:appid WHERE id=:id", array(':appid' => $id, ':id' => $r['id']));
                    echo $id;
                    $found = true;
                }
            }
            if (!$found) {
                echo "Erro na password";
                http_response_code(200);
            }
        } else {
            echo "Utilizador inexistente!!";
            http_response_code(200);
        }
        
        
        //Guardar os dados da chamada de um telefone/lead   //putCallInfo(id,numTel,tempo,hora)
    } elseif ($_GET['url'] == "info") {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        $token = $postBody->token;
        $lead = $postBody->lead;
        $telefone = $postBody->telefone;
        $horainicio = $postBody->horainicio;
        $duracao = $postBody->duracao;
        $sentido = $postBody->sentido;
        if ($user = checkToken($token)) {
            $db->query("INSERT INTO cad_registochamadas(user,lead,horainicio,duracao,telefone,sentido) VALUES(:user,:lead,:horainicio,:duracao,:telefone,:sentido)"
                    , array(':user'=>$user,':lead' => $lead, ':horainicio' => $horainicio, ':duracao' => $duracao, ':telefone' => $telefone, ':sentido' => $sentido));
            http_response_code(200);
        } else {
            http_response_code(401);
        }
    } else {
        http_response_code(405);
    }
    
     //Atualizar o Device ID
} elseif ($_SERVER['REQUEST_METHOD'] == "PUT") {   
   
    if ($_GET['url'] == "auth") {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        $token = $postBody->token;
        $deviceId = $postBody->deviceId;

        if (checkToken($token)) {
            $db->query("UPDATE cad_utilizadores SET deviceid=:deviceId WHERE appid=:token "
                    , array(':deviceId' => $deviceId, ':token' => $token));
            http_response_code(200);
        } else {
            http_response_code(401);
        }
    } else {//Fim dos metodos 
        http_response_code(405);
    } 
   //LOGOUT
} elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {   //Fazer Logout

    if ($_GET['url'] == "auth") {
        $token = $_GET['token'];

        if (checkToken($token)) {
            $db->query("UPDATE cad_utilizadores SET appid='' WHERE appid=:token "
                    , array(':token' => $token));
            http_response_code(200);
        } else {
            http_response_code(401);
        }
    } else {//Fim dos metodos 
        http_response_code(405);
    }
} else {//Fim dos metodos 
    $db->query("INSERT INTO arq_log(log,user,tipo) VALUES(:log,99,'R')", array(':log' => $_SERVER['REQUEST_METHOD']));
    http_response_code(405);
}

//Functions
//Check token and return user ID or false
function checkToken($token) {
    $db = new DB();
    //Get user id
    $result = $db->query("SELECT id FROM cad_utilizadores WHERE appid=:appid LIMIT 1", array(':appid' => $token));
    if ($result) {
        return $result[0][0];
    } else {
        return false;
    }
}


