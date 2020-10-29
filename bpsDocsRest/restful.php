<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
require_once '../sisleadsrest/db/DB.php';
require_once 'Doc.php';

$db = new DB();
$token = "$2a$10$2a020a03b2f627ebab5dcOQLblV9rSsT8qAl0tDO7I.p4bZ2f/CuK";

//Verificar que se trata de um POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $postBody = file_get_contents("php://input");
    $dt = json_decode($postBody);
    
    // Validar o Token
    if ( $dt->token == $token) {
    
        // Verificar se existe e obter o numero da lead
        $result = $db->query("SELECT * FROM arq_leads WHERE idleadorig=:idleadorig LIMIT 1", [':idleadorig'=>$dt->idleadorig]);
        if($result && ($result[0]["status"]<10 || $result[0]["status"]==39 || $result[0]["status"]==21)){
            //Criar processo se não existir
//            try{
//                !isset($result[0]["tipo"]) || $result[0]["tipo"]="" ? $result[0]["tipo"]="CP" : null; 
//                $db->query("INSERT INTO arq_processo(lead, user, nome, idade, nif, email, telefone, vencimento, valorpretendido, tipocredito) "
//                        . " VALUES(:lead, :user, :nome, :idade, :nif, :email, :telefone, :vencimento, :valorpretendido, :tipocredito)",
//                        [':lead'=>$result[0]["id"], ':user'=>$result[0]["user"], ':nome'=>$result[0]["nome"], ':idade'=>$result[0]["idade"], ':nif'=>$result[0]["nif"],
//                            ':email'=>$result[0]["email"], ':telefone'=>$result[0]["telefone"], ':vencimento'=>$result[0]["rendimento1"], 
//                            ':valorpretendido'=>$result[0]["montante"], ':tipocredito'=>$result[0]["tipo"]]);
//            } catch (Exception $ex) {
//                echo '{"result": false, "message":"Não inserido m34"}';
//            }
            
            $ob = new Doc();
             $resp = $ob->anexaDoc($result[0]['id'], $dt);
             if($resp== "Inserido"){
                    echo '{"result": true, "message":"Inserido"}';
                 http_response_code(200);
             } else {
                    echo '{"result": false, "message":"Não inserido m44"}';
                http_response_code(400);
             }
            
        } else {
            echo '{"result": false, "message":"Id de lead não existe nas leads recebidas OU o status da lead não permite receber mais documentação."}';
            http_response_code(400);
        }

    } else {
        echo '{"result": false, "message":"Acesso recusado. Verifique token."}';
        http_response_code(401);
    }

} else {
    echo '{"result": false, "message":"Erro! Não é POST"}';
    http_response_code(401);
}