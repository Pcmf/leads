<?php
require_once 'db/DB.php';
require_once 'Processo.php';
/**
 * Description of Leads
 *
 * @author pedro
 */
class Leads {
    private $db;
    private $processo;
    public function __construct() {
        $this->db = new DB();
        $this->processo = new Processo();
    }
    /**
     * 
     * @param int $user
     * @return array
     */
    public function getAll($user) {
                return  $this->db->query("SELECT L.id, L.status as sts, L.dataentrada, SL.nome AS status, L.datastatus, P.* FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cnf_statuslead SL ON SL.id=L.status "
                . " LEFT JOIN cad_utilizadores U ON U.id=L.analista "
                . " WHERE L.user=:user AND rgpd=0", array(':user'=>$user));

    }
    /**
     * 
     * @param type $lead
     * @return array [status, datastatus]
     */
    public function getLeadStatus($lead) {
        return $this->db->query("SELECT status, datastatus FROM arq_leads WHERE id=:lead ", [':lead'=>$lead])[0];
    }
    /**
     * 
     * @param type $user
     * @param type $id
     * @return obj all info
     */
    public function getOne($user, $id) {
        $resp = array();
        if($user){
        $result = $this->db->query("SELECT L.id, L.status AS sts, SL.nome AS status, L.datastatus, L.dataentrada, L.user, L.analista, "
                . " L.montante, L.prazopretendido AS prazo, P.*, U1.nome AS gestor, U.nome AS nomeanalista, U.telefone AS telefoneAnalista "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cnf_statuslead SL ON SL.id=L.status "
                . " LEFT JOIN cad_utilizadores U1 ON U1.id=L.user "
                . " LEFT JOIN cad_utilizadores U ON U.id=L.analista "
                . " WHERE L.user=:user AND L.id=:id ", array(':user'=>$user, ':id'=>$id));
        } else {
        $result = $this->db->query("SELECT L.id, L.status AS sts, SL.nome AS status, L.datastatus, L.dataentrada, L.user, L.analista, "
                . " L.montante, L.prazopretendido AS prazo, P.*, U1.nome AS gestor,"
                . " U1.telefone AS telefonegestor, U1.email AS emailgestor, U.nome AS nomeanalista, U.telefone AS telefoneAnalista, R.motivo, R.outro "
                . " FROM arq_leads L "
                . " LEFT JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cnf_statuslead SL ON SL.id=L.status "
                . " LEFT JOIN cad_utilizadores U1 ON U1.id=L.user "
                . " LEFT JOIN cad_utilizadores U ON U.id=L.analista"
                . " LEFT JOIN cad_rejeicoes R ON R.lead=L.id "
                . " WHERE  L.id=:id ", array(':id'=>$id));            
        }
        
        $resp['lead'] = $result[0];
        
        foreach ($result as $ln) {
            $temp = array();
            $temp['lead'] = $ln;
            if($oc = $this->db->query("SELECT * FROM cad_outroscreditos WHERE lead=:lead", array(':lead'=>$ln['lead']))){
                $resp['oc'] = $oc;
            }
            if($or = $this->db->query("SELECT * FROM cad_outrosrendimentos WHERE lead=:lead", array(':lead'=>$ln['lead']))){
                $resp['or'] = $or;
            }
        }
        
        
        $resp['submissoes'] = $this->db->query("SELECT F.*, S.status AS nomeStatus, P.nome   FROM cad_financiamentos F "
                . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
                . " INNER JOIn cnf_stsfinanciamentos S ON S.id=F.status "
                . " WHERE lead=:lead" , array(':lead'=>$id));
        
        $resp['rejeicoes'] = $this->db->query("SELECT * FROM cad_rejeicoes WHERE lead=:lead", array(':lead'=>$id));
        
        return $resp;
    }
    
    /**
     * 
     * @param type $leadId
     * @return type
     */
    public function getLeadClientData($leadId) {
        return $this->db->query("SELECT nome, idade, telefone, email FROM arq_processo WHERE lead=:lead ", [':lead'=>$leadId]);
    }
    /**
     * 
     * @param type $lead
     * @param type $status
     * @return type
     */
    public function changeLeadStatus($lead, $status) {
        // Se o novo status é 36 vai verificar se tem status 21. se sim atualiza com 22
        $status == 36 && $this->getLeadStatus($lead)['status'] == 21 ?   $status = 22: null;
        return $this->db->query("UPDATE arq_leads SET status=:status, datastatus=NOW() WHERE id=:lead", [':lead'=>$lead, ':status'=>$status]);
    }
    
    /**
     * 
     * @param type $user
     * @return array
     */
    public function getDashInfo($user) {
        $resp = array();
        $temp = array();
        $temp = $this->db->query("SELECT count(*) AS submetidos FROM arq_leads WHERE user=:user ", array(':user'=>$user));
        array_push($resp, $temp[0]);
        $temp= array();
        $temp = $this->db->query("SELECT count(*) AS pendentes FROM arq_leads WHERE user=:user AND status IN (10,12,13,20,22)", array(':user'=>$user));
        array_push($resp, $temp[0]);
        $temp= array();
        $temp = $this->db->query("SELECT count(*) AS aprovados FROM arq_leads WHERE user=:user AND status IN(16,23) ", array(':user'=>$user));
        array_push($resp, $temp[0]);
        $temp= array();
        $temp = $this->db->query("SELECT count(*) AS financiados FROM arq_leads WHERE user=:user AND status IN(17,24)", array(':user'=>$user));
        array_push($resp, $temp[0]);
        $temp= array();
        $temp = $this->db->query("SELECT count(*) AS recusados FROM arq_leads WHERE user=:user  AND status IN(14,15,18,19,25)", array(':user'=>$user));
        array_push($resp, $temp[0]);
        return $resp;
    }
  
    /**
     * @param type $userId
     * @param type $obj
     */
    public function setLead($userId, $obj){
        $sts = $obj->status;
        $motivo = $obj->motivocontacto;
        
        //verifica campos que possam estar undefined
        $obj = $this->checkFields($obj->form);
        
        $result=  $this->db->query("INSERT INTO arq_leads(idleadorig, nomelead, fornecedor, tipo, nome, email, telefone, idade, nif, montante, rendimento1, "
                . " tipocontrato, info, dataentrada, status, datastatus, user) "
                . " VALUES( 0, ' ', :fornecedor, :tipo, :nome, :email, :telefone, :idade, :nif, :montante, :rendimento, "
                . ":tipocontrato, :info, NOW(), :status, NOW(), :user) ",
                array( ':fornecedor'=>$obj->fornecedor ,':tipo'=>$obj->tipocredito, ':nome'=>$obj->nome, ':email'=>$obj->email,
                    ':telefone'=>$obj->telefone, ':idade'=>$obj->idade, ':nif'=>$obj->nif, ':montante'=>$obj->valorpretendido, ':rendimento'=>$obj->vencimento, ':tipocontrato'=>$obj->tipocontrato,
                    ':info'=>$obj->info, ':status'=>$sts, 'user'=>$userId));
        $lead =  $this->db->lastInsertId() ;    
        //Inserir no processo
        if($lead>0){
            
            $this->processo->setProcesso($lead, $userId, $obj);
            
        //Inserir no registo de contactos
            $this->db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, dtcontacto, motivocontacto)  VALUES(:lead, :user, 1, NOW(), :motivocontacto) ",
                  array(':lead'=>$lead, ':user'=>$userId, ':motivocontacto'=>$motivo));
        
        
        //Inserir Outros créditos e
           $arrayO = array();
           for($k=0; $k<10; $k++ ){
                     $temp = new stdClass();
                    if(isset($obj->{'ocCredito'.$k}) && $obj->{'ocCredito'.$k}!="" ) {
                        $temp->tipocredito = $obj->{'ocCredito'.$k} ;
                        (isset($obj->{'ocValor'.$k}) && $obj->{'ocValor'.$k}!="" ) ? $temp->valorcredito = $obj->{'ocValor'.$k} : null;
                        (isset($obj->{'ocPrestacao'.$k}) && $obj->{'ocPrestacao'.$k}!="" ) ? $temp->prestacao = $obj->{'ocPrestacao'.$k} : null;
                        array_push($arrayO, $temp);
                    }
           }
           for($k=0; $k<sizeof($arrayO); $k++){
               $result2 = $this->db->query("INSERT INTO cad_outroscreditos(lead, linha, tipocredito, valorcredito, prestacao) VALUES(:lead, :linha, :tipocredito, :valorcredito, :prestacao) ",
                       array('lead'=>$lead, 'linha'=>$k+1, 'tipocredito'=>$arrayO[$k]->tipocredito, 'valorcredito'=>$arrayO[$k]->valorcredito, 'prestacao'=>$arrayO[$k]->prestacao));
           }
           
            //Outros rendimentos
           $arrayO = array();
           for($k=0; $k<10; $k++ ){
                     $temp = new stdClass();
                    if(isset($obj->{'orRendimento'.$k}) && $obj->{'orRendimento'.$k}!="" ) {
                        $temp->tiporendimento = $obj->{'orRendimento'.$k} ;
                        (isset($obj->{'orValor'.$k}) && $obj->{'orValor'.$k}!="" ) ? $temp->valorrendimento = $obj->{'orValor'.$k} : null;
                        (isset($obj->{'orPeriocidade'.$k}) && $obj->{'orPeriocidade'.$k}!="" ) ? $temp->periocidade = $obj->{'orPeriocidade'.$k} : null;
                        array_push($arrayO, $temp);
                    }
           }
           for($k=0; $k<sizeof($arrayO); $k++){
               $result2 = $this->db->query("INSERT INTO cad_outrosrendimentos(lead, linha, tiporendimento, valorrendimento, periocidade) VALUES(:lead, :linha, :tiporendimento, :valorrendimento, :periocidade) ",
                       array('lead'=>$lead, 'linha'=>$k+1, 'tiporendimento'=>$arrayO[$k]->tiporendimento, 'valorrendimento'=>$arrayO[$k]->valorrendimento, 'periocidade'=>$arrayO[$k]->periocidade));
           }           
           
            return $lead;
        } else {
            return "Erro";
        }
    }
    /**
     * 
     * @param type $lead
     * @param type $obj
     */
    public function upLead($lead, $obj) {
//        //atualizar o arq_leads
        (!isset($obj->valorpretendido) || !$obj->valorpretendido ) ? $obj->valorpretendido=0 : null;
        $result=  $this->db->query("UPDATE arq_leads  SET  nome=:nome, email=:email, telefone=:telefone, idade=:idade, nif=:nif, montante=:montante, rendimento1=:rendimento1, "
                . " tipocontrato=:tipocontrato, info=:info, datastatus=NOW() WHERE id=:id ",
                array( ':nome'=>$obj->nome, ':email'=>$obj->email, ':telefone'=>$obj->telefone, ':idade'=>$obj->idade, ':nif'=>$obj->nif, ':montante'=>$obj->valorpretendido, 
                    ':rendimento1'=>$obj->vencimento, ':tipocontrato'=>$obj->tipocontrato, ':info'=>$obj->info, ':id'=>$lead));
        
        //Atualiza o processo
        $this->processo->update($lead, $obj);
        
  
                //Inserir no registo de contactos
            $this->db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, dtcontacto, motivocontacto)  VALUES(:lead, :user,"
                    . " (SELECT max(B.contactonum)+1 FROM cad_registocontacto B WHERE B.lead=:lead AND user=:user), NOW(), 12) ",
                  array(':lead'=>$lead, ':user'=>$obj->user));
            

               //Inserir Outros créditos e
           $arrayO = array();
           for($k=0; $k<10; $k++ ){
                     $temp = new stdClass();
                    if(isset($obj->{'ocCredito'.$k}) && $obj->{'ocCredito'.$k}!="" ) {
                        $temp->tipocredito = $obj->{'ocCredito'.$k} ;
                        (isset($obj->{'ocValor'.$k}) && $obj->{'ocValor'.$k}!="" ) ? $temp->valorcredito = $obj->{'ocValor'.$k} : null;
                        (isset($obj->{'ocPrestacao'.$k}) && $obj->{'ocPrestacao'.$k}!="" ) ? $temp->prestacao = $obj->{'ocPrestacao'.$k} : null;
                        array_push($arrayO, $temp);
                    }
           }
           //Apagar todos os registos de Outros Creditos antes de inserir novos
           $this->db->query("DELETE FROM cad_outroscreditos WHERE lead=:lead ", array(':lead'=>$lead));           
           for($k=0; $k<sizeof($arrayO); $k++){
               $result = $this->db->query("INSERT INTO cad_outroscreditos(lead, linha, tipocredito, valorcredito, prestacao) VALUES(:lead, :linha, :tipocredito, :valorcredito, :prestacao) ",
                       array('lead'=>$lead, 'linha'=>$k+1, 'tipocredito'=>$arrayO[$k]->tipocredito, 'valorcredito'=>$arrayO[$k]->valorcredito, 'prestacao'=>$arrayO[$k]->prestacao));
           }
           
            //Outros rendimentos
           $arrayO = array();
           for($k=0; $k<10; $k++ ){
                     $temp = new stdClass();
                    if(isset($obj->{'orRendimento'.$k}) && $obj->{'orRendimento'.$k}!="" ) {
                        $temp->tiporendimento = $obj->{'orRendimento'.$k} ;
                        (isset($obj->{'orValor'.$k}) && $obj->{'orValor'.$k}!="" ) ? $temp->valorrendimento = $obj->{'orValor'.$k} : null;
                        (isset($obj->{'orPeriocidade'.$k}) && $obj->{'orPeriocidade'.$k}!="" ) ? $temp->periocidade = $obj->{'orPeriocidade'.$k} : null;
                        array_push($arrayO, $temp);
                    }
           }
           //Apagar todos os registos de Outros Rendimentos antes de inserir novos
           $this->db->query("DELETE FROM cad_outrosrendimentos WHERE lead=:lead ", array(':lead'=>$lead));           
           for($k=0; $k<sizeof($arrayO); $k++){
               $result = $this->db->query("INSERT INTO cad_outrosrendimentos(lead, linha, tiporendimento, valorrendimento, periocidade) VALUES(:lead, :linha, :tiporendimento, :valorrendimento, :periocidade) ",
                       array('lead'=>$lead, 'linha'=>$k+1, 'tiporendimento'=>$arrayO[$k]->tiporendimento, 'valorrendimento'=>$arrayO[$k]->valorrendimento, 'periocidade'=>$arrayO[$k]->periocidade));
           } 
           
           return $lead;
           
    }
    
    /**
     * @name Agendar
     * @param type $param
     */
    public function saveNotes($param) {
        return $this->db->query("UPDATE arq_processo SET outrainfo = CONCAT(outrainfo, '\nNotas de Recuperação:  ', :notas, '  - [',  NOW(), ' ]') WHERE lead=:lead ",
                array(':notas'=>$param->notas, ':lead'=>$param->lead));
    }
    
    
    public function agendar($param) {
        //Limpar a agenda para esta lead
        $this->db->query("UPDATE cad_agenda SET status=0 WHERE lead=:lead ", array(':lead'=>$param->lead));
        //Inserir novo agendamento
        $this->db->query("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, tipoagenda, status) VALUES(:lead, :user, :agendadata, :agendahora, :tipoagenda, 1)",
                array(':lead'=>$param->lead, ':user'=>$param->user, ':agendadata'=>$param->dataAgenda, ':agendahora'=>$param->horaAgenda, ':tipoagenda'=>$param->tipoagenda));
        //Atualizar o status da lead para agendada pelo Gestor de Recuperação
        $this->db->query("UPDATE arq_leads SET status=32, datastatus=NOW(), userrec=:user WHERE id=:lead ", 
                array(':user'=>$param->user,':lead'=>$param->lead ));
        //Atualizar o status do arq_histrecuperacao
        $this->db->query("UPDATE arq_histrecuperacao SET status=2, data=NOW() WHERE lead=:lead", [':lead'=>$param->lead]);
        
        // registo do contacto
        return $this->db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, dtcontacto, motivocontacto) VALUES(:lead, :user,"
                . "  (SELECT max(B.contactonum)+1 FROM cad_registocontacto B WHERE B.lead=:lead), NOW(), 6)",
                array(':lead'=>$param->lead, ':user'=>$param->user));
        
        
    }
    /**
     * 
     * @param type $obj
     * @return obj
     */
    private function checkFields($obj) {
        !isset($obj->nomelead) ? $obj->nomelead='' : null;
        !isset($obj->fornecedor) ? $obj->fornecedor=0 : null;
        !isset($obj->tipocredito) ? $obj->tipocredito='CP' : null;

        return $obj;
    }
    
    
    
}
        
