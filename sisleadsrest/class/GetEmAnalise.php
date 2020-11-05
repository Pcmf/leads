<?php

require_once '../../class/sendEmail.php';
require_once '../../php/openCon.php';
require_once '../db/DB.php';
$ob = new GetEmAnalise($con);





/**
 * Description of GetEmAnalise
 * Vai obter lista de processos que tenham entrado para analise há mais de 48horas
 *
 * @author pedro
 */
class GetEmAnalise {
    private  $db;
    private $assunto = " Estado do seu pedido de crédito.";
    private $msg = "<p>O seu pedido de crédito continua em análise, estamos a procurar a melhor proposta para si.</p>"
                            ."<p>Por favor aguardar de uma resposta da nossa parte.</p>";


    public function __construct($con) {
        
        $this->db = new DB();
        forEach($this->getListEmAnalise() AS $ln ){
            $assunto = "Ref ". $ln['id']. $this->assunto;
            $msg = "<p>Exmo Sr(a) ".$ln['nome']."</p>".$this->msg;
            new sendEmail($con, $ln['user'], $ln['emailOrigem'], $ln['email'], $assunto, $msg, null);
            sleep(30);
        }
    }
    
    private function getListEmAnalise(){
        return $this->db->query("SELECT L.id, L.nome, L.email, L.telefone, L.user, U.email AS emailOrigem "
                . " FROM arq_leads L "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                . " WHERE status IN(10,11,12,13) and datastatus < (curdate() - INTERVAL 2 day)");
    }
}
