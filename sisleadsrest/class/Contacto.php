<?php
require_once 'db/DB.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contacto
 *
 * @author pedro
 */
class Contacto {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    /**
     * 
     * @param type $user
     * @param type $obj
     * @return type
     */
    public function setContacto($user, $lead, $obj) {
        $obj = $this->checkObj($obj);
        return $this->db->query("INSERT INTO arq_cofidiscontacto( lead, tipodoc, numid, validade, emissor, iban, empregador, "
                . "nifempregador, morada, codigopostal, tipodoc2, numid2,validade2, emissor2, empregador2, nifempregador2, data, user) "
                . "VALUES( :lead, :tipodoc, :numid, :validade, :emissor, :iban, :empregador, "
                . ":nifempregador, :morada, :codigopostal, :tipodoc2, :numid2, :validade2, :emissor2, :empregador2, :nifempregador2, NOW(), :user) "
                , array( ':lead'=>$lead, ':tipodoc'=>$obj->tipoDoc, ':numid'=>$obj->numId, ':validade'=>$obj->validade, ':emissor'=>$obj->emissor
                , ':iban'=>$obj->iban, ':empregador'=>$obj->empregador, ':nifempregador'=>$obj->nifEmpregador, ':morada'=>$obj->morada, ':codigopostal'=>$obj->codigoPostal
                , ':tipodoc2'=>$obj->tipoDoc2, ':numid2'=>$obj->numId2, ':validade2'=>$obj->validade2, ':emissor2'=>$obj->emissor2, ':empregador2'=>$obj->empregador2
                , ':nifempregador2'=>$obj->nifEmpregador, ':user'=>$user)); 
    }
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getOne($lead) {
        return $this->db->query("SELECT * FROM arq_cofidiscontacto WHERE lead=:lead" , [':lead'=>$lead]);
    }
    
    /**
     * 
     * @param type $lead
     * @param type $obj
     * @return type
     */
    public function update($lead, $obj) {
        $obj = $this->checkObj($obj);
        return $this->db->query("UPDATE arq_cofidiscontacto SET tipodoc=:tipodoc, numid=:numid, validade=:validade, emissor=:emissor, iban=:iban, empregador=:empregador, "
                . " nifempregador=:nifempregador, morada=:morada, codigopostal=:codigopostal, tipodoc2=:tipodoc2, numid2=:numid2, validade2=:validade2, emissor2=:emissor2, "
                . " empregador2=:empregador2, nifempregador2=:nifempregador2, data=NOW() "
                . " WHERE lead=:lead "
                    , array( ':lead'=>$obj->lead, ':tipodoc'=>$obj->tipoDoc, ':numid'=>$obj->numId, ':validade'=>$obj->validade, ':emissor'=>$obj->emissor
                , ':iban'=>$obj->iban, ':empregador'=>$obj->empregador, ':nifempregador'=>$obj->nifEmpregador, ':morada'=>$obj->morada, ':codigopostal'=>$obj->codigoPostal
                , ':tipodoc2'=>$obj->tipoDoc2, ':numid2'=>$obj->numId2, ':validade2'=>$obj->validade2, ':emissor2'=>$obj->emissor2, ':empregador2'=>$obj->empregador2
                , ':nifempregador2'=>$obj->nifEmpregador2, ':user'=>$user)); 
                
    }
    
    
    private function checkObj($obj) {
        !isset($obj->tipoDoc) ? $obj->tipoDoc=null : null;
        !isset($obj->numId) ? $obj->numId=null : null;
        !isset($obj->validade) ? $obj->validade=null : null;
        !isset($obj->emissor) ? $obj->emissor=null : null;
        !isset($obj->iban) ? $obj->iban=null : null;
        !isset($obj->empregador) ? $obj->empregador=null : null;
        !isset($obj->nifEmpregador) ? $obj->nifEmpregador=null : null;
        !isset($obj->morada) ? $obj->morada=null : null;
        !isset($obj->codigoPostal) ? $obj->codigoPostal=null : null;
        !isset($obj->tipoDoc2) ? $obj->tipoDoc2=null : null;
        !isset($obj->numId2) ? $obj->numId2=null : null;
        !isset($obj->validade2) ? $obj->validade2=null : null;
        !isset($obj->emissor2) ? $obj->emissor2=null : null;   
        !isset($obj->empregador2) ? $obj->empregador2=null : null;
        !isset($obj->nifEmpregador2) ? $obj->nifEmpregador2=null : null;        
        return $obj;
    }
}
