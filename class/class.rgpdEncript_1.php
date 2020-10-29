<?php
/**
 * Description of rgpdEncript
 *  Esta class recebe um nif, email ou um telefone e vai procurar todas as LEADS
 * que contenham pelo menos um destes parametros e vai encriptar todos 
 * que tenham dados pessoais desse cliente.
 * A chave de encriptação é apenas conhecida pelo Administrador e poderá ser usada para inverter o processo.
 * @author pedro
 */
require_once 'php/openCon.php';
require_once 'EncryptProcesso.php';
require_once 'EncryptLeads.php';
require_once 'DeleteRegistoChamadas.php.php';
require_once 'DeleteLogEmail.php.php';




class rgpdEncript {

    //put your code here
    public function __construct($nif, $email, $telefone) {
        //obter rgpdkey
        $result = mysqli_query($con, "SELECT rgpdkey FROM cad_utilizadores WHERE rgpdkey IS NOT NULL");
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $rgpdkey = $row['rgpdkey'];


            //arq_leads  - encriptar
            $query = sprintf("SELECT id, nif, email, telefone FROM arq_leads WHERE nif=%s OR email LIKE '%s' OR telefone LIKE '%s' ", $nif, $email, $data);
            $result = mysqli_query($con, $query);
            if ($result) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    new EncryptProcesso($con, $row['id'], $rgpdkey);
                    new DeleteRegistoChamadas($con, $row['telefone']);
                    new DeleteLogEmail($con, $row['email']);
                    new EncryptLeads($con, $row['id'], $rgpdkey);
                }
            } else {
                echo "Não existem dados para essa procura";
                return;
            }
        } else {
            echo "Não é possivél obter chave RGPD";
            return;
        }
    }

}
