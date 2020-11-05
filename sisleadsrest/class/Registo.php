<?php
require_once 'db/DB.php';

/**
 * Description of Registo
 * Registar contacto e Rejeições
 *
 * @author pedro
 */
class Registo {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    /**
     * 
     * @param type $lead
     * @param type $user
     * @param type $motivo
     * @param type $envsms
     * @param type $envemail
     * @return type
     */
    public function setRegistoContacto($lead,$user, $motivo, $envsms, $envemail) {
        return $this->db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, envsms, envemail, motivocontacto)"
                . " VALUES(:lead, :user, (SELECT MAX(A.contactonum)+1 FROM cad_registocontacto A WHERE A.lead=:lead), :envsms, :envemail, :motivo) "
                , array(':lead'=>$lead, ':user'=>$user, ':envsms'=>$envsms, ':envemail'=>$envemail, ':motivo'=>$motivo));
    }
    
    /**
     * 
     * @param type $lead
     * @param type $motivo
     * @return type
     */
    public function registaRejeicao($lead, $motivo ) {
        return $this->db->query("INSERT INTO cad_rejeicoes(lead, motivo) VALUES(:lead, :motivo) "
                , array(':lead'=>$lead, ':motivo'=>$motivo));
    }
}
