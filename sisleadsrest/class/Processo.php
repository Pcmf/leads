<?php

require_once 'db/DB.php';
/*
 * Dados do processo
 */

/**
 * Description of Processo
 *
 * @author pedro
 */
class Processo {
    private $db;
    /**
     * 
     */
    public function __construct() {
        $this->db = new DB();
        
    }
    
    public function exist($lead) {
        $qty = $this->db->query("SELECT count(*) AS qty FROM arq_processo WHERE lead=:lead ", [':lead'=>$lead]);
        if($qty[0]['qty'] > 0){
            return true;
        } else {
            return false;
        }
    }
    
    public function getCLTprocesso($lead) {
        return $this->db->query("SELECT L.id, L.status, L.datastatus, L.dataentrada, L.user, P.* "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " WHERE L.id=:lead", [':lead'=>$lead]);
    }
    
    
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getBasicProcesso($lead){
        return $this->db->query("SELECT * FROM arq_processo WHERE lead=:lead", [':lead'=>$lead]);
    }

    /**
     * 
     * @param type $user
     * @return array
     */
    public function getProcesso($user) {
        $resp = array();
        // Lead nova
        $result = $this->db->query("SELECT L.idleadorig, L.nomelead, L.fornecedor, L.status, L.datastatus, P.*, TC.nome AS tipocontratonome, EC.nome AS estadocivilnome, H.nome AS habitacaonome,"
                . " TC2.nome AS tipocontratonome2, H2.nome AS habitacao2, DATEDIFF( DATE(NOW()), DATE(L.datastatus)) AS dias, U.nome AS gestor "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " INNER JOIN cnf_sitprofissional TC ON TC.id=P.tipocontrato "
                . " INNER JOIN cnf_sitfamiliar EC ON EC.id=P.estadocivil "
                . " INNER JOIN cnf_tipohabitacao H ON H.id=P.tipohabitacao "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user"
                . " LEFT JOIN cnf_sitprofissional TC2 ON TC2.id=P.tipocontrato2 "
                . " LEFT JOIN cnf_tipohabitacao H2 ON H2.id=P.tipohabitacao2 "
                . " LEFT JOIN arq_histrecuperacao R ON R.lead = L.id "                
                . " WHERE R.lead IS NULL AND L.status=9 AND DATEDIFF( DATE(NOW()), DATE(L.dataentrada) ) < 60  LIMIT 1 "); 
        if($result){
            foreach ($result as $ln) {
                $temp = array();
                $temp['lead'] = $ln;
                //Inserir no arq_histrecuperacao
                $this->db->query("INSERT INTO arq_histrecuperacao(lead,data,status, user) VALUES(:lead, NOW(), 1, :user)", [':lead'=>$ln['lead'], ':user'=>$user]);
                //Obter outros creditos e outros rendimentos
                if($oc = $this->db->query("SELECT * FROM cad_outroscreditos WHERE lead=:lead", array(':lead'=>$ln['lead']))){
                    $temp['oc'] = $oc;
                }
                if($or = $this->db->query("SELECT * FROM cad_outrosrendimentos WHERE lead=:lead", array(':lead'=>$ln['lead']))){
                    $temp['or'] = $or;
                }
                if($hist= $this->db->query("SELECT R.*, S.nome AS recupstatus FROM arq_histrecuperacao R "
                        . " INNER JOIN cnf_recupstatus S ON S.id=R.status "
                        . " WHERE R.lead=:lead", [':lead'=>$ln['lead']])){
                     $temp['histrecup'] = $hist;
                }
                
                array_push($resp,$temp);
            }
        }
        return $resp;
    }        
    

    
/**
     * 
     * @param type $lead
     * @return array
     */
    public function getProcessoByLead($lead) {
           $resp = array();
        $result = $this->db->query("SELECT L.id,L.idleadorig, L.nomelead, L.fornecedor, L.status, S.nome AS statusnome, L.datastatus, P.*, "
                . " TC.nome AS tipocontratonome, EC.nome AS estadocivilnome, H.nome AS habitacaonome, "
                . " TC2.nome AS tipocontratonome2, H2.nome AS habitacao2, DATEDIFF( DATE(NOW()), DATE(L.datastatus)) AS dias, U.nome AS gestor "
                . " FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " LEFT JOIN cnf_sitprofissional TC ON TC.id=P.tipocontrato "
                . " LEFT JOIN cnf_sitfamiliar EC ON EC.id=P.estadocivil "
                . " LEFT JOIN cnf_tipohabitacao H ON H.id=P.tipohabitacao "
                . " INNER JOIN cad_utilizadores U ON U.id=L.user"
                . " INNER JOIN cnf_statuslead S ON S.id=L.status "
                . " LEFT JOIN cnf_sitprofissional TC2 ON TC2.id=P.tipocontrato2 "
                . " LEFT JOIN cnf_tipohabitacao H2 ON H2.id=P.tipohabitacao2 "
                . " WHERE L.id=:lead ", [':lead'=>$lead]);    
        if($result){
            foreach ($result as $ln) {
                $temp = array();
                $temp['lead'] = $ln;
                if($oc = $this->db->query("SELECT * FROM cad_outroscreditos WHERE lead=:lead", array(':lead'=>$lead))){
                    $temp['oc'] = $oc;
                }
                if($or = $this->db->query("SELECT * FROM cad_outrosrendimentos WHERE lead=:lead", array(':lead'=>$lead))){
                    $temp['or'] = $or;
                }
                if($hist = $this->db->query("SELECT R.*, S.nome AS recupstatus FROM arq_histrecuperacao R "
                        . " INNER JOIN cnf_recupstatus S ON S.id=R.status "
                        . " WHERE R.lead=:lead", [':lead'=>$ln['lead']])){
                     $temp['histrecup'] = $hist;
                }
                array_push($resp,$temp);
            }
        }
        return $resp;
        
    }
    /**
     * 
     * @param type $lead
     * @param type $obj
     * @return type
     */
    public function setProcesso($lead, $userId, $obj) {
                        //validadção de dados
                if( isset($obj->segundoproponente) && $obj->segundoproponente) {
                    if((isset($obj->isConjugue) && $obj->isConjugue) || (isset($obj->relacaofamiliar) && $obj->relacaofamiliar==1)) {
                        $parentesco = 'Conjugue';
                    } else {
                        isset($obj->parentesco) ? $parentesco = $obj->parentesco : $parentesco = null;
                    }
                } else {
                    $parentesco = null;
                }
                
                $obj = $this->validateFields($obj);
                
        
        //Inserir no processo
                
        $result = $this->db->query("INSERT INTO arq_processo( lead, user, nome, nif, email, telefone, idade, profissao, vencimento, tipocontrato, mesinicio, anoinicio, estadocivil, filhos, parentesco2, telefone2, "
                . " nif2, idade2, profissao2, vencimento2, tipocontrato2, mesinicio2, anoinicio2, irs, tipohabitacao, valorhabitacao, declarada, anoiniciohabitacao, tipohabitacao2, valorhabitacao2, declarada2,"
                . "  anoiniciohabitacao2, mesmahabitacao, valorpretendido, tipocredito, prazopretendido, prestacaopretendida, finalidade, outrainfo, nome2, relacaofamiliar, segundoproponente ) "
                . " VALUES(:lead, :user, :nome, :nif, :email, :telefone, :idade, :profissao, :vencimento, :tipocontrato, :mesinicio, :anoinicio, :estadocivil, :filhos, :parentesco2, :telefone2, "
                . " :nif2, :idade2, :profissao2, :vencimento2, :tipocontrato2, :mesinicio2, :anoinicio2, :irs, :tipohabitacao, :valorhabitacao, :declarada, :anoiniciohabitacao, :tipohabitacao2, :valorhabitacao2, :declarada2,"
                . "  :anoiniciohabitacao2, :mesmahabitacao, :valorpretendido, :tipocredito, :prazopretendido, :prestacaopretendida, :finalidade, :outrainfo, :nome2, :relacaofamiliar , :segundoproponente ) "
                , array(':lead'=>$lead, ':user'=>$userId, ':nome'=>$obj->nome, ':nif'=>$obj->nif, ':email'=>$obj->email, ':telefone'=>$obj->telefone, ':idade'=>$obj->idade, ':profissao'=>$obj->profissao,
                    ':vencimento'=>$obj->vencimento, ':tipocontrato'=>$obj->tipocontrato, ':mesinicio'=>$obj->mesinicio, ':anoinicio'=>$obj->anoinicio, ':estadocivil'=>$obj->estadocivil, ':filhos'=>$obj->filhos ,
                     ':parentesco2'=>$parentesco, ':telefone2'=>$obj->telefone2, ':nif2'=>$obj->nif2, ':idade2'=>$obj->idade2, ':profissao2'=>$obj->profissao2, ':vencimento2'=>$obj->vencimento2,
                     ':tipocontrato2'=>$obj->tipocontrato2, ':mesinicio2'=>$obj->mesinicio2,  ':anoinicio2'=>$obj->anoinicio2, ':irs'=>$obj->irs, ':tipohabitacao'=>$obj->tipohabitacao, ':valorhabitacao'=>$obj->valorhabitacao,
                    ':declarada'=>$obj->declarada, ':anoiniciohabitacao'=>$obj->anoiniciohabitacao, ':tipohabitacao2'=>$obj->habitacao2, ':valorhabitacao2'=>$obj->valorhabitacao2,
                    ':declarada2'=>$obj->declarada2, ':anoiniciohabitacao2'=>$obj->anohabitacao2, ':mesmahabitacao'=>$obj->mesmahabitacao2, ':valorpretendido'=>$obj->valorpretendido,
                    ':tipocredito'=>$obj->tipocredito, ':prazopretendido'=>$obj->prazopretendido, ':prestacaopretendida'=>$obj->prestacaopretendida, ':finalidade'=>$obj->finalidade, ':outrainfo'=>$obj->info
                    ,':nome2'=>$obj->nome2, ':relacaofamiliar'=>$obj->relacaofamiliar, ':segundoproponente'=>$obj->segundoproponente  ));
        
        return $result;
    }


    /**
     * 
     * @param type $lead
     * @param type $obj
     * @return type
     */
    public function update($lead, $obj) {
         
                        //validadção de dados
                if( isset($obj->segundoproponente) && $obj->segundoproponente) {
                    if((isset($obj->isConjugue) && $obj->isConjugue) || (isset($obj->relacaofamiliar) && $obj->relacaofamiliar==1)) {
                        $parentesco = 'Conjugue';
                    } else {
                        $parentesco = $obj->parentesco;
                    }
                } else {
                    $parentesco = null;
                }

            $obj = $this->validateFields($obj);    
        
        //atualiza o processo
        $result = $this->db->query("UPDATE arq_processo SET  nome=:nome, nif=:nif, email=:email, telefone=:telefone, idade=:idade, profissao=:profissao, vencimento=:vencimento,"
                . " tipocontrato=:tipocontrato, mesinicio=:mesinicio, anoinicio=:anoinicio, estadocivil=:estadocivil, filhos=:filhos, parentesco2=:parentesco2, telefone2=:telefone2, nif2=:nif2, idade2=:idade2, "
                . " profissao2=:profissao2, vencimento2=:vencimento2, tipocontrato2=:tipocontrato2, mesinicio2=:mesinicio2, anoinicio2=:anoinicio2, irs=:irs, tipohabitacao=:tipohabitacao, valorhabitacao=:valorhabitacao,"
                . " declarada=:declarada, anoiniciohabitacao=:anoiniciohabitacao, tipohabitacao2=:tipohabitacao2, valorhabitacao2=:valorhabitacao2, declarada2=:declarada2,"
                . "  anoiniciohabitacao2=:anoiniciohabitacao2, mesmahabitacao=:mesmahabitacao, valorpretendido=:valorpretendido, tipocredito=:tipocredito, prazopretendido=:prazopretendido,"
                . " prestacaopretendida=:prestacaopretendida, finalidade=:finalidade, outrainfo=:outrainfo, nome2=:nome2, relacaofamiliar=:relacaofamiliar, segundoproponente=:segundoproponente  WHERE lead=:lead"
                , array('lead'=>$lead, 'nome'=>$obj->nome, 'nif'=>$obj->nif, 'email'=>$obj->email, 'telefone'=>$obj->telefone, 'idade'=>$obj->idade, 'profissao'=>$obj->profissao,
                    'vencimento'=>$obj->vencimento, 'tipocontrato'=>$obj->tipocontrato, 'mesinicio'=>$obj->mesinicio, 'anoinicio'=>$obj->anoinicio, 'estadocivil'=>$obj->estadocivil, 'filhos'=>$obj->filhos ,
                     'parentesco2'=>$parentesco, 'telefone2'=>$obj->telefone2, 'nif2'=>$obj->nif2, 'idade2'=>$obj->idade2, 'profissao2'=>$obj->profissao2, 'vencimento2'=>$obj->vencimento2,
                     'tipocontrato2'=>$obj->tipocontrato2, 'mesinicio2'=>$obj->mesinicio2, 'anoinicio2'=>$obj->anoinicio2, 'irs'=>$obj->irs, 'tipohabitacao'=>$obj->tipohabitacao, 'valorhabitacao'=>$obj->valorhabitacao,
                    'declarada'=>$obj->declarada, 'anoiniciohabitacao'=>$obj->anoiniciohabitacao, 'tipohabitacao2'=>$obj->habitacao2, 'valorhabitacao2'=>$obj->valorhabitacao2,
                    'declarada2'=>$obj->declarada2, 'anoiniciohabitacao2'=>$obj->anohabitacao2, 'mesmahabitacao'=>$obj->mesmahabitacao2, 'valorpretendido'=>$obj->valorpretendido,
                    'tipocredito'=>$obj->tipocredito, 'prazopretendido'=>$obj->prazopretendido, 'prestacaopretendida'=>$obj->prestacaopretendida, 'finalidade'=>$obj->finalidade, 'outrainfo'=>$obj->info
                     ,':nome2'=>$obj->nome2, ':relacaofamiliar'=>$obj->relacaofamiliar, ':segundoproponente'=>$obj->segundoproponente ));
        
        return $result;
    }
    
    /**
     * 
     * @param type $obj
     * @return type
     */
    private function validateFields($obj) {
                        !isset($obj->nome2) ? $obj->nome2=null : null;   
                !isset($obj->segundoproponente) ? $obj->segundoproponente=null : null;
                !isset($obj->filhos) ? $obj->filhos=0 : null;   
                !isset($obj->telefone2) ? $obj->telefone2=null : null;   
                !isset($obj->nif2) ? $obj->nif2=0 : null;   
                !isset($obj->idade2) ? $obj->idade2=null : null;   
                !isset($obj->profissao) ? $obj->profissao='' : null;   
                !isset($obj->profissao2) ? $obj->profissao2=null : null;   
                !isset($obj->estadocivil) ? $obj->estadocivil=null : null;   
                !isset($obj->irs) ? $obj->irs=null : null;   
                !isset($obj->vencimento2) ? $obj->vencimento2=null : null;   
                !isset($obj->tipocontrato2) ? $obj->tipocontrato2=0 : null;   
                !isset($obj->relacaofamiliar) ? $obj->relacaofamiliar=null : null;   
                !isset($obj->anoinicio) ? $obj->anoinicio=null : null;   
                !isset($obj->mesinicio) ? $obj->mesinicio=null : null;   
                !isset($obj->anoinicio2) ? $obj->anoinicio2=null : null;   
                !isset($obj->mesinicio2) ? $obj->mesinicio2=null : null;   
                !isset($obj->valorhabitacao) ? $obj->valorhabitacao=null : null;   
                !isset($obj->declarada) ? $obj->declarada=null : null;    
                (isset($obj->mesmahabitacao2) && $obj->mesmahabitacao2==1) ? $obj->mesmahabitacao2='Sim' : $obj->mesmahabitacao2='Nao';   
                !isset($obj->mesmahabitacao2) ? $obj->mesmahabitacao2=null : null;   
                !isset($obj->habitacao2) ? $obj->habitacao2=null : null;   
                !isset($obj->valorhabitacao2) ? $obj->valorhabitacao2=null : null;   
                !isset($obj->declarada2) ? $obj->declarada2=null : null;   
                !isset($obj->anohabitacao2) ? $obj->anohabitacao2=null : null;   
                !isset($obj->prazopretendido) ? $obj->prazopretendido=null : null;            
                !isset($obj->prestacaopretendida) ? $obj->prestacaopretendida=null : null;   
                !isset($obj->finalidade) ? $obj->finalidade=null : null;   
                !isset($obj->info) ? $obj->info=null : null;  
                !isset($obj->tipocredito) ? $obj->tipocredito=null : null;  
                !isset($obj->valorpretendido) ? $obj->valorpretendido=null : null;  
                
                return $obj;
    }
    
}
