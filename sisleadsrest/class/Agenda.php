<?php
require_once 'db/DB.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Agenda
 * Serve para agendar contactos ou previsões de receção de documentos
 * @author pedro
 */
class Agenda {
    private $db;
    
    
    public function __construct() {
        $this->db = new DB();
    }
    
   /**
    * 
    * @param type $lead
    * @param type $user
    * @param type $data
    * @param type $hora
    * @param type $tipoagenda
    * @return type
    */
    public function agendar($lead, $user, $data, $hora, $tipoagenda) {
        $this->limparAgenda($lead);
        $hora>='14:00:00' ? $periodo=2 : $periodo=1;
        return $this->db->query("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, agendaperiodo, tipoagenda, status) "
                . " VALUES(:lead, :user, :agendadata, :agendahora, :periodo ,:tipoagenda, 1)",
                array(':lead'=>$lead, ':user'=>$user, ':agendadata'=>$data, ':agendahora'=>$hora, ':periodo'=>$periodo, ':tipoagenda'=>$tipoagenda));
    }
    
    /**
     * 
     * @param type $user
     * @param type $lead
     * @return type
     */
    public function agendaAutomatica($user, $lead) {
       $data = $this->getNextUtilDay();
       $hora = $this->getHoraAgenda($user, $data);
        return $this->agendar($lead, $user, $data, $hora, 5);
    }
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getAgenda($lead) {
        return $this->db->query("SELECT * FROM cad_agenda WHERE lead=:lead AND status=1 ", [':lead'=>$lead]);
    }
    
    public function limparAgenda($lead){
        $this->db->query("UPDATE cad_agenda SET status=0 WHERE lead=:lead", [':lead'=>$lead]);
    }
    /**
     * 
     * @param type $user
     * @return type
     */
    public function checkAgenda($user) {
        //Agendamentos manuais
        if($lead = $this->checkAgendaTipo($user, 6)) {
                return $lead[0]['lead'];
        } elseif($lead = $this->checkAgendaTipo($user, 5)) {
                return $lead[0]['lead'];
        }

    }
    /**
     * 
     * @param type $user
     * @param type $tipo
     * @return type
     */
    private function checkAgendaTipo($user, $tipo) {
        return $this->db->query("SELECT lead FROM cad_agenda "
                . " WHERE user=:user AND tipoagenda=:tipo AND status=1 AND (agendadata < DATE(NOW()) OR ( agendadata=DATE(NOW()) AND HOUR(agendahora) <= HOUR(NOW()))) LIMIT 1"
                , [':user'=>$user, ':tipo'=>$tipo]);
    }
    
 
    
    /**
     * Dia util seguinte
     * @return date
     */
    private  function getNextUtilDay(){
        if( date('w', strtotime(date('Y-m-d'))) == 5){
            $dataAg = date('Y-m-d', strtotime("+3 days"));
        } else {
           $dataAg = date('Y-m-d', strtotime("+1 days")); 
        }
        return $dataAg;
    }
    
    /**
     * 
     * @param int $user
     * @param date $data
     * @return string 
     */
    private function getHoraAgenda($user, $data){
        $periodo=1;
        $hora = '09:00:00';
        $horaAtual = date('H');
        if($horaAtual/14 >= 1){
            $periodo = 2;
            $hora='14:00:00';
        }
        $result = $this->db->query("SELECT  AddTime(MAX(agendahora) , '00:10:00') AS agendahora FROM cad_agenda WHERE agendadata=:data AND agendaperiodo=:periodo AND status=1 AND user=:user",
                array(':data'=>$data, ':periodo'=>$periodo, ':user'=>$user));
        if($result && $result[0]['agendahora']){
            return $result[0]['agendahora'];
        } else {
            return $hora;
        }
        
        
    }
    
}
