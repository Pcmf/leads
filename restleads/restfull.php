<?php

/* 
 * REST to receive all leads from any supplier
 * With token authentication
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

//require_once '../sisleadsrest/db/DB.php';
require_once "../php/passwordHash.php";
require_once './class/Lead.php';
require_once './class/Result.php';

//getInfo(token, numTelcliente) ->lead, nome
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postBody = json_decode(file_get_contents("php://input"));
    if(!isset($postBody->fornecedor) || !isset($postBody->password)){
        echo '{"error":true, "message":"Falta o fornecedor ou a password"}';
        http_response_code(401);        
    }
    $supplier = checkPass($postBody->fornecedor, $postBody->password);
    $log = new Result();
    $log->set(true, json_encode($postBody), 0);
    if ($supplier &&  $_GET['url'] == "leads") {
        $ob = new Lead();
        echo json_encode($ob->saveLead($postBody, $supplier));
        http_response_code(200);
    } else {
        echo '{"error":true, "message":"Erro no codigo de fornecedor ou na password"}';
        http_response_code(401);
    }

}

//Functions
//Check password and return user ID or false
function checkPass($id, $pass) {
    $db = new DB();
    $result = $db->query("SELECT empresa, password, encrypt, privKey FROM cad_fornecedorleads WHERE id=:id ", [':id' =>$id]);
    if (passwordHash::check_password( $result[0]['password'], $pass)) {
        return $result[0];
    } else {
        $error = new Result();
        $error->set(true, "Acesso não autorizado: ".$id.' - '.$pass,0);
        return false;
    }
}