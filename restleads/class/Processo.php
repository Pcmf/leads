<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Processo
 *
 * @author pedro
 */
class Processo {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    //email, telefone, idade, nif, montante, rendimento1, outroscreditos, situacao, info, gcid, dataentrada, status
    public function insert($lead, $nome, $nif, $email, $telefone, $idade, $vencimento, $valorpretendido, $prazopretendido, $prestacaopretendida, $tipocredito) {
        return $this->db->query("INSERT INTO arq_processo(lead, user, nome, nif, email, telefone, idade, vencimento,"
                . " valorpretendido, prazopretendido, prestacaopretendida, tipocredito) "
                . " VALUES(:lead, 0, :nome, :nif, :email, :telefone, :idade, :vencimento, :valorpretendido, :prazopretendido, :prestacaopretendida, :tipocredito)", 
                [':lead'=>$lead, ':nome'=>$nome, ':nif'=>$nif, ':email'=>$email, ':telefone'=>$telefone, ':idade'=>$idade,
                    ':vencimento'=>$vencimento, ':valorpretendido'=>$valorpretendido, ':prazopretendido'=>$prazopretendido, 
                    ':prestacaopretendida'=>$prestacaopretendida, ':tipocredito'=>$tipocredito]);
    }
}
