<?php
require_once './db/DB.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author pedro
 */
class Data {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    public function getAll($tabela) {
        return $this->db->query("SELECT * FROM ".$tabela);
    }
}
