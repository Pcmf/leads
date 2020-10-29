<?php
require_once 'passwordHash.php';
require_once 'db/DB.php';
/**
 * Description of User
 *
 * @author pedro
 */
class User {
    private $db;
    private $valido;
    private $token;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    
    
    /**
     * 
     * @param type $username
     * @param type $password
     * @return boolean
     */
    public function checkuser1($username, $password) {
        //Verificar se utilizador existe
//         if ($resp = $this->db->query("SELECT * FROM cad_utilizadores WHERE username=:user AND tipo='GExterno' AND fornecedor IS NOT NULL ", array(':user' => $username))) {
       if ($resp = $this->db->query("SELECT * FROM cad_utilizadores WHERE username=:user AND (tipo='COFD' OR tipo='GExterno' OR tipo='Report') ", array(':user' => $username))) {
            //verificar se a password e utilizador correspondem
            $this->valido = false;

            foreach ($resp AS $r) {
          //      if ($r['password'] == $password) {
                if (passwordHash::check_password($r['password'], $password)) {
                    $this->token = generateToken($r);
                    $this->db->query("UPDATE cad_utilizadores SET token=:token WHERE id=:id ", array(':token'=> $this->token, ':id'=>$r['id']));
                    $this->valido = true;
                    break;    
                }
            }
            if($this->valido){
                return $this->token;
            } else {
                return FALSE;
            }
        }
    }
    
    /**
     * 
     * @param int $user
     * @return array
     */
    public function getUserData($user) {
        return $this->db->query("SELECT id, nome, tipo, telefone, email, deviceId, mural, ativo FROM cad_utilizadores WHERE id=:user ", [':user'=>$user]);
    }
    
    
}

            /**
            * Check token and return user ID or false
            */
           function generateToken($resp) {
               //Chave para a encriptação
               $key = 'klEpFG93';

               //Configuração do JWT
               $header = [
                   'alg' => 'HS256',
                   'typ' => 'JWT'
               ];

               $header = json_encode($header);
               $header = base64_encode($header);
               
               //Obter o nome do fornecedor
               
               //Dados 
               $payload = [
                   'iss' => 'GESTLEADS',
                   'id' => $resp['id'],
                   'nome' => $resp['nome'],
                   'username' => $resp['username'],
                   'tipo'=> $resp['tipo'],
                   'fornecedor'=> $resp['fornecedor'],
                   'deviceId'=> $resp['deviceid']
               ];

               $payload = json_encode($payload);
               $payload = base64_encode($payload);

               //Signature

               $signature = hash_hmac('sha256', "$header.$payload", $key, true);
               $signature = base64_encode($signature);
               // echo $header.$payload.$signature;

               return "$header.$payload.$signature";
           }
