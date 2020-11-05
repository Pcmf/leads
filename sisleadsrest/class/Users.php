<?php
require_once 'db/DB.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Users
 *
 * @author pedro
 */
class Users {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
   /*
    * Retorna os utilizadores que twnham mural e estejam ativos
    */ 
    public function getMuralUsers() {
        return $this->db->query("SELECT id, nome, tipo FROM cad_utilizadores WHERE mural=1 AND ativo=1 AND tipo='Analista'");
    }
}
