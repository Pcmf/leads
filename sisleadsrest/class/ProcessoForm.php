<?php
require_once 'db/DB.php';
require_once 'Leads.php';
require_once 'Processo.php';
/**
 * Description of ProcessoForm
 *
 * @author pedro
 */
class ProcessoForm {
    private $db;
    private $lead;
    private $processo;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    public function getOne($lead) {
        $resp = array();
        $resp['process'] = $this->db->query("SELECT P.vencimento, P.valorpretendido, P.tipocredito, P.prazopretendido, "
                . " P.prestacaopretendida, P.finalidade, P.outrainfo AS info, P.vencimento2,  F.* "
                . " FROM arq_processo P "
                . " INNER JOIN arq_process_form F ON F.lead=P.lead WHERE P.lead=:lead", [':lead'=>$lead])[0];
        $resp['oc'] = $this->db->query("SELECT * FROM cad_outroscreditos WHERE lead=:lead", [':lead'=>$lead]);
        $resp['or'] = $this->db->query("SELECT * FROM cad_outrosrendimentos WHERE lead=:lead", [':lead'=>$lead]);
        return $resp;
    }
    
    /**
     * 
     * @param type $param
     * @return type
     */
    public function create($param) {
        $obj = $param->form;
        //Corregir datas: incluir o '-'
        $obj->datanascimento = $this->corrigeData($obj->datanascimento);
        $obj->validade = $this->corrigeData($obj->validade);
        isset($obj->datanascimento2) ? $obj->datanascimento2 = $this->corrigeData($obj->datanascimento2) : null;
        isset($obj->validade2) ? $obj->validade2 = $this->corrigeData($obj->validade2) : null;
        !isset($obj->idade) ? $obj->idade = $this->calcularIdade($obj->datanascimento) : null;
        !isset($obj->anoiniciohabitacao) ? $obj->anoiniciohabitacao=0 : null;
        !isset($obj->info) ? $obj->info="" : null;
        !isset($obj->diapagamento) ? $obj->diapagamento=1 : null;
        !isset($obj->prazopretendido) ? $obj->prazopretendido=12 : null;
        !isset($obj->prestacaopretendida) ? $obj->prestacaopretendida=0 : null;
        !isset($obj->finalidade) ? $obj->finalidade="" : null;
        ($obj->segundoproponente && !isset($obj->idade2)) ? $obj->idade2 = $this->calcularIdade($obj->datanascimento2) : null;
        
        // Criar lead e obter o ID - arq_leads
        $this->lead = new Leads();
        $leadId = $this->lead->setLead($obj->user, $param);
        
        //Validar os campos do formulario - TO DO
                !isset($obj->filhos) ? $obj->filhos=0 : null;
        (!isset($obj->segundoproponente) || !$obj->segundoproponente) ? $obj->segundoproponente=0 : null;
        //:morada2, :localidade2, :cp2, :tipohabitacao2, :anoiniciohabitacao2
        if(isset($obj->mesmahabitacao2) && $obj->mesmahabitacao2){
            !isset($obj->morada2) ? $obj->morada2='' : null;
            !isset($obj->localidade2) ? $obj->localidade2='' : null;
            !isset($obj->cpostal2) ? $obj->cpostal2='' : null;
            !isset($obj->tipohabitacao2) ? $obj->tipohabitacao2=0 : null;
            !isset($obj->anoiniciohabitacao2) ? $obj->anoiniciohabitacao2=0 : null;
        }
        !isset($obj->info) ? $obj->info="" : null;
        // Regista no arq_processo-form
        if($obj->segundoproponente){
            //Com 2º titular
            $result = $this->db->query("INSERT INTO arq_process_form(lead, nome, datanascimento, tipodoc, numdocumento,"
                . "validade, nacionalidade, estadocivil, filhos, nif, segundoproponente, "
                . "nome2, datanascimento2, tipodoc2, numdocumento2, validade2, nacionalidade2, nif2, relacaofamiliar,"
                . "morada, localidade, cp, tipohabitacao, anoiniciohabitacao, telefone, email, mesmahabitacao,"
                . "morada2, localidade2, cp2, tipohabitacao2, anoiniciohabitacao2, telefone2, email2,"
                . "sector, tipocontrato, desde, desdemes, nomeempresa, nifempresa, telefoneempresa,"
                . "sector2, tipocontrato2, desde2, desdemes2, nomeempresa2, nifempresa2, telefoneempresa2,"
                . "iban, ibandesde, diaprestacao )"
                . " VALUES(:lead, :nome, :datanascimento, :tipodoc, :numdocumento,"
                . ":validade, :nacionalidade, :estadocivil, :filhos, :nif, :segundoproponente, "
                . ":nome2, :datanascimento2, :tipodoc2, :numdocumento2, :validade2, :nacionalidade2, :nif2, :relacaofamiliar,"
                . ":morada, :localidade, :cp, :tipohabitacao, :anoiniciohabitacao, :telefone, :email, :mesmahabitacao,"
                . ":morada2, :localidade2, :cp2, :tipohabitacao2, :anoiniciohabitacao2, :telefone2, :email2,"
                . ":sector, :tipocontrato, :desde, :desdemes, :nomeempresa, :nifempresa, :telefoneempresa,"
                . ":sector2, :tipocontrato2, :desde2, :desdemes2, :nomeempresa2, :nifempresa2, :telefoneempresa2,"
                . ":iban, :ibandesde, :diaprestacao )",
                [':lead'=>$leadId, ':nome'=>$obj->nome, ':datanascimento'=>$obj->datanascimento, 
                ':tipodoc'=>$obj->tipodoc, ':numdocumento'=>$obj->numdocumento, ':validade'=>$obj->validade,
                ':nacionalidade'=>$obj->nacionalidade, ':estadocivil'=>$obj->estadocivil, ':filhos'=>$obj->filhos,
                ':nif'=>$obj->nif, ':segundoproponente'=>$obj->segundoproponente,
                    ':nome2'=>$obj->nome2, ':datanascimento2'=>$obj->datanascimento2, 
                ':tipodoc2'=>$obj->tipodoc2, ':numdocumento2'=>$obj->numdocumento2, ':validade2'=>$obj->validade2,
                ':nacionalidade2'=>$obj->nacionalidade2, ':nif2'=>$obj->nif2,
                    ':relacaofamiliar'=>$obj->relacaofamiliar, ':morada'=>$obj->morada, ':localidade'=>$obj->localidade,
                    ':cp'=>$obj->cpostal, ':tipohabitacao'=>$obj->tipohabitacao, ':anoiniciohabitacao'=>$obj->anoiniciohabitacao,
                    ':telefone'=>$obj->telefone, ':email'=>$obj->email, ':mesmahabitacao'=>$obj->mesmahabitacao2, 
                ':morada2'=>$obj->morada2, ':localidade2'=>$obj->localidade2, ':cp2'=>$obj->cpostal2, 
                    ':tipohabitacao2'=>$obj->tipohabitacao2, ':anoiniciohabitacao2'=>$obj->anoiniciohabitacao2,
                    ':telefone2'=>$obj->telefone2, ':email2'=>$obj->email2,
                    ':sector'=>$obj->profissao, ':tipocontrato'=>$obj->tipocontrato, ':desde'=>$obj->anoinicio,
                    ':desdemes'=>$obj->mesinicio, ':nomeempresa'=>$obj->nomeempresa, 'nifempresa'=>$obj->nifempresa,
                    ':telefoneempresa'=>$obj->telefoneempresa,
                ':sector2'=>$obj->profissao2, ':tipocontrato2'=>$obj->tipocontrato2, ':desde2'=>$obj->anoinicio2,
                    ':desdemes2'=>$obj->mesinicio2, ':nomeempresa2'=>$obj->nomeempresa2, 'nifempresa2'=>$obj->nifempresa2,
                    ':telefoneempresa2'=>$obj->telefoneempresa2,
                ':iban'=>$obj->iban, ':ibandesde'=>$obj->desdeiban, ':diaprestacao'=>$obj->diapagamento ]);
        } else {
            //Apenas 1º titular
             $result = $this->db->query("INSERT INTO arq_process_form(lead, nome, datanascimento, tipodoc, numdocumento,"
                . "validade, nacionalidade, estadocivil, filhos, nif, segundoproponente, "
                . "morada, localidade, cp, tipohabitacao, anoiniciohabitacao, telefone, email,"
                . "sector, tipocontrato, desde, desdemes, nomeempresa, nifempresa, telefoneempresa,"
                . "iban, ibandesde, diaprestacao )"
                . " VALUES(:lead, :nome, :datanascimento, :tipodoc, :numdocumento,"
                . ":validade, :nacionalidade, :estadocivil, :filhos, :nif, :segundoproponente, "
                . ":morada, :localidade, :cp, :tipohabitacao, :anoiniciohabitacao, :telefone, :email,"
                . ":sector, :tipocontrato, :desde, :desdemes, :nomeempresa, :nifempresa, :telefoneempresa,"
                . ":iban, :ibandesde, :diaprestacao )",
                [':lead'=>$leadId, ':nome'=>$obj->nome, ':datanascimento'=>$obj->datanascimento, 
                ':tipodoc'=>$obj->tipodoc, ':numdocumento'=>$obj->numdocumento, ':validade'=>$obj->validade,
                ':nacionalidade'=>$obj->nacionalidade, ':estadocivil'=>$obj->estadocivil, ':filhos'=>$obj->filhos,
                ':nif'=>$obj->nif, ':segundoproponente'=>$obj->segundoproponente,
                ':morada'=>$obj->morada, ':localidade'=>$obj->localidade,
                ':cp'=>$obj->cpostal, ':tipohabitacao'=>$obj->tipohabitacao, ':anoiniciohabitacao'=>$obj->anoiniciohabitacao,
                ':telefone'=>$obj->telefone, ':email'=>$obj->email, 
                ':sector'=>$obj->profissao, ':tipocontrato'=>$obj->tipocontrato, ':desde'=>$obj->anoinicio,
                ':desdemes'=>$obj->mesinicio, ':nomeempresa'=>$obj->nomeempresa, 'nifempresa'=>$obj->nifempresa,
                ':telefoneempresa'=>$obj->telefoneempresa,
                ':iban'=>$obj->iban, ':ibandesde'=>$obj->desdeiban, ':diaprestacao'=>$obj->diapagamento ]);            
        }
        return $leadId;
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    private function corrigeData($data) {
        return substr($data, 0,2).'-'.substr($data, 2,2).'-'.substr($data, 4,4);
    }
    
    /**
     * 
     * @param date $datanascimento
     * @return int
     */
    private function calcularIdade($datanascimento) {
        $data = $datanascimento;
        // separando yyyy, mm, ddd
        list($dia, $mes, $ano ) = explode('-', $data);
        // data atual
        $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        // Descobre a unix timestamp da data de nascimento do fulano
        $nascimento = mktime( 0, 0, 0, $mes, $dia, $ano);
        // cálculo
        $idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 364);
        return $idade;
    }
    
}
