<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Encrypt_Decrypt.php';
/**
 * Description of EncryptProcesso
 *
 * @author pedro
 */
class EncryptProcesso {
    
    public function __construct($con,$action, $lead, $key) {
        
        $result = mysqli_query($con, sprintf("SELECT * FROM arq_processo WHERE lead=%s", $lead));
        if($result){
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
             $enome = new  encrypt_decrypt($action, $row['nome'], $key);
             $etelefone = new  encrypt_decrypt($action,$row['telefone'], $key);
             $enif = new  encrypt_decrypt($action,$row['nif'], $key);
             $eemail = new  encrypt_decrypt($action,$row['email'], $key);
             $emoradarua = new  encrypt_decrypt($action,$row['moradarua'], $key);
             
             $query =sprintf("UPDATE arq_processo SET nome='%s', telefone='%s', nif='%s', email='%s', moradarua='%s' WHERE lead=%s "
                     , $enome->getReturnString(), $etelefone->getReturnString(), $enif->getReturnString(), $eemail->getReturnString(), $emoradarua->getReturnString(), $lead);
             mysqli_query($con, $query);
        }
        
    }
}
