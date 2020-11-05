<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

require_once '../sisleadsrest/db/DB.php';
require_once '../php/openCon.php';
require_once "../php/passwordHash.php";
require_once '../class/PortalAccessEmail.php';

$db = new DB();

//Verificar que se trata de um POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $postBody = file_get_contents("php://input");
    $dt = json_decode($postBody);
    toLog($postBody);
    $fornecedor = 24;
    $nomelead = "CR-Consolidado"; 
    //Limpar os dados
    $idleadorig = strip_tags($dt->id);
    $nome = ucwords(strtolower($dt->nome));
    if(substr($dt->numero, 0,1)=='+'){
        $telefone = substr($dt->numero, -9);
    } else {  
        $telefone = strip_tags($dt->numero);
    }
    $email = strip_tags($dt->email);
    
    
    $query = "INSERT INTO arq_leads(idleadorig, nomelead, fornecedor, tipo, nome, email, telefone, nif, idade, montante, status) "
            . " VALUES(:idleadorig, :nomelead, :fornecedor, 'CC', :nome, :email, :telefone, 0, 0, 0, 1)  ";
    $result = $db->queryInsert($query, [':idleadorig'=>$idleadorig, ':nomelead'=>$nomelead, ':fornecedor'=>$fornecedor,  ':nome'=>$nome, ':email'=>$email, ':telefone'=>$telefone]);
    
    if(!$result) {
        $lead = $db->lastInsertId();
        toLog($idleadorig." Inserida");
        //Criar processo
        $db->query("INSERT INTO arq_processo(lead, user, nome, nif, email, telefone, idade) "
                . " VALUES(:lead, 0, :nome, 0, :email, :telefone, 0)",
                [':lead'=>$lead, ':nome'=>$nome, ':email'=>$email, ':telefone'=>$telefone]);
        //Gerador de acessos ao portal
        new PortalAccessEmail($con, $lead, $nome, $email);
        echo "ok";
        http_response_code(200);
    } else {
        toLog($idleadorig." Erro");
        echo "erro";
        http_response_code(203);
    }
    
    
}


function toLog($param) {
    
    $time = date('Y-m-d H:i:s');
    
    $txt = "Recebido: ".$time."  Log: ".$param;
    $newLine = PHP_EOL;
    file_put_contents('./log.txt',$txt.$newLine,FILE_APPEND);
    return;
}
