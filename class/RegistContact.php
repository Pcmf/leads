<?php
/**
 * Registar os contactos ou tentativas de contacto, tlf, email, sms, efectuados
 * 
 * @author pedro
 */
require_once 'DB.php';

class RegistContact {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
    /**
     * 
     * @param type $lead
     * @param type $user
     * @param type $motivocontacto
     * @param type $envsms
     * @param type $envemail
     */
    public function registContact($lead, $user, $motivocontacto, $envsms=0, $envemail=0) {
       //echo "NUM CONTACTO: ".$this->getNextContactNum($lead);
        $this->db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, dtcontacto, envsms, envemail, motivocontacto)"
                . " VALUES(:lead, :user, :contactonum, NOW(), :envsms, :envemail, :motivocontacto)",
                [':lead'=>$lead, ':user'=>$user, ':contactonum'=>$this->getNextContactNum($lead), ':envsms'=>$envsms,
                    ':envemail'=>$envemail, ':motivocontacto'=>$motivocontacto]);
    }
    /**
     * 
     * @param type $lead
     * @param type $motivo
     * @return type
     */
    public function getNumContactsByMotivo($lead, $motivo) {
      return  $this->db->query("SELECT count(*) FROM cad_registocontacto WHERE lead=:lead AND motivocontacto=:motivo",
              [':lead'=>$lead, ':motivo'=>$motivo]);
    }
    
    /**
     * 
     * @param int $lead
     * @return int
     */
    private function getNextContactNum($lead) {
      $num = $this->db->query("SELECT max(contactonum)+1 AS num FROM cad_registocontacto WHERE lead=:lead ", [':lead'=>$lead])[0]['num'];
      !isset($num)? $num=1 : null;
      return $num;
      
    }
    
    
}
