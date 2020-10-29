<?php
require_once '../sisleadsrest/db/DB.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Anexar documentação
 * Validar formatos
 * 
 * Adicionar linha á cad_docpedida com o tipo de documento correspondente, como recebido
 * Inserir o BASE64 na arq_documentacao
 * 
 * @author pedro
 */
class Doc {
    private $db;
    
    
    private $lead;
    private $linha;
    // arq_documentacao
    
    private $tipo;
    private $nomefx;
    private $fx64;
    
    //cad_docpedida
    private $tipodoc;
    // Tipos de documentos aceites
   private $tipodedocs =  array( 'upload-id'=> 1 , 'upload-address'=> 3, 'upload-iban'=> 4, 'upload-income'=> 5, 'upload-irs'=> 6, 'upload-map'=> 11);
    // Tipos de formatos de ficheiros aceites
   private $formatos = array("image/jpg"=>"jpg", "image/jpeg"=>"jpeg", "application/pdf"=>"pdf");

    public function __construct() {
        $this->db = new DB();

        
    }
    
    public function anexaDoc($lead, $obj) {
        if( $this->tipodoc = $this->getTipodoc($obj->tipodoc)) {
            //filtrar pelos formatos
             if(array_key_exists($obj->formato, $this->formatos)){
                 
                //Associar o documento a um tipo de documento
                $this->tipo = $this->formatos[$obj->formato];
                
                // Inserir linha na cad_docpedida
                $this->linha = $this->getLinha($lead, $this->tipodoc); 
                
                //tenta atualizar
                $result = $this->db->query("UPDATE cad_docpedida SET recebido=1, datarecebido=NOW() WHERE lead=:lead AND linha=:linha AND recebido=0 ",
                        [':lead'=>$lead, ':linha'=> $this->linha]);
                if(!$this->db->upSuccess()){
                    try {
                        $result = $this->db->query("INSERT INTO cad_docpedida(lead, linha, tipodoc, recebido, datarecebido) VALUES(:lead, :linha, :tipodoc, 1, NOW()) ",
                            [':lead'=>$lead, ':linha'=> $this->linha, ':tipodoc'=> $this->tipodoc] );
                    } catch (Exception $exc) {
                        return "Erro ao inserir na doc pedida L59";
                    }
                }
                if(!$result){
                    try {
                         $resultD = $this->db->query("INSERT INTO arq_documentacao(lead, linha, tipo, nomefx, fx64) VALUES(:lead, :linha, :tipo, :nomefx, :fx64) ",
                            [':lead'=>$lead, ':linha'=> $this->linha, ':tipo'=> $this->tipo, ':nomefx'=>$obj->nomefx, ':fx64'=>$obj->fx64]);
                    } catch (Exception $exc) {
                        return "Erro ao inserir documentação L67";
                    }

                    if(!$resultD){
                        if($this->isAllReceived($lead)){
                            //Obter utilizador
                            $user = $this->getLeadUser($lead);
                            $fornecedor = $this->getNewFornecedor($lead);
                            //Criar processo, se necessário
                       //     $this->createProcess($lead);
                            //Alterar o status da lead para documentação recebida (39 -doc recebida) e atribui a um utilizador caso não tenha.
                            $this->db->query("UPDATE arq_leads SET fornecedor=:fornecedor, status=39, datastatus=NOW(), user=:user WHERE id=:lead ", [':lead'=>$lead, ':user'=>$user, ':fornecedor'=>$fornecedor]);
                            //Remover do agendamento caso esteja ajendada
                            $this->db->query("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=:lead" , [':lead'=>$lead]);
                           } //else {
                            $this->changeToBPSDocs($lead);
                       // }
                        return "Inserido";
                    } else {
                        return "Erro! Não inseriu doc. ";
                    }
                } 
                return "Erro. Não inserido na doc pedida";
            } else {
                return "Erro. Formato não aceite";
            }   
        } else {
            return "Erro. Tipo de documento não reconhecido";
        }  

     
    }
    /**
     * 
     * @param type $lead
     * @return int
     */
    private function getLinha( $lead, $tipodoc) {
        //Verificar se existe linha para este tipo de doc que ainda não foi recebida
        $result = $this->db->query("SELECT linha FROM cad_docpedida WHERE lead=:lead AND tipodoc=:tipodoc AND recebido=0 ", [':lead'=>$lead, ':tipodoc'=>$tipodoc]);
        if($result){
            return $result[0]['linha'];
        }
        $result = $this->db->query("SELECT max(linha) AS linha FROM cad_docpedida WHERE lead=:lead", [':lead'=>$lead]);
        if($result){
            return $result[0]['linha'] + 1;
        }
        return 1;
    }
    /**
     * 
     * @param type $param
     * @return boolean
     */
    private function getTipodoc($param) {
        if(array_key_exists($param , $this->tipodedocs)){
            $r = $this->tipodedocs[$param];
            return $r;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param type $lead
     */
    private function changeToBPSDocs($lead) {
            $fornecedor = $this->getNewFornecedor($lead);
            if($fornecedor){
                $this->db->query("UPDATE arq_leads SET fornecedor=:fornecedor WHERE id=:lead ", [':lead'=>$lead, ':fornecedor'=>$fornecedor]);
            }
    }
    /**
     * 
     * @param type $lead
     * @return int
     */
    private function getNewFornecedor($lead) {
        $result = $this->db->query("SELECT fornecedor FROM arq_leads WHERE id=:lead ", [':lead'=>$lead]);
        if($result[0]['fornecedor'] == 6){
            return 20;
        } elseif ($result[0]['fornecedor'] == 5) {
            return 23;
        }
        return $result[0]['fornecedor'];
    }
    /**
     * Se toda a documentação foi recebida
     * @param type $lead
     * @return boolean
     */
    private function isAllReceived($lead) {
        $result = $this->db->query("SELECT count(*) AS qty FROM cad_docpedida WHERE recebido=1 AND lead=:lead ", [':lead'=>$lead]); 
        if($result[0]['qty']>=5){
            $result = $this->db->query("SELECT count(*) AS qty FROM cad_docpedida WHERE recebido=0 AND lead=:lead ", [':lead'=>$lead]);
            if($result[0]['qty']>0){
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * 
     * @param type $lead
     * @return type
     */
    private function getLeadUser($lead) {
        $result = $this->db->query("SELECT user FROM arq_leads WHERE id=:lead ", [':lead'=>$lead]);
        if($result[0]['user']){
            return $result[0]['user'];
        } else {
            return $this->getRandomUser();
        }
    }
    /**
     * 
     * @return int
     */
    private function getRandomUser() {
            $gestores = [24,1017];
          /*   $gestores = $this->db->query("SELECT id FROM cad_utilizadores WHERE tipo='Gestor' AND presenca=1 AND ativo=1"); */
            $r = rand(0, sizeof($gestores));
            return $gestores[$r];
    }
    
    private function createProcess($lead) {
        $result = $this->db->query("SELECT * FROM arq_leads WHERE id=:lead ", [':lead'=>$lead])[0];
        if($result['status']<8){
            //Verificar que não tem processo para criar
            $resultP = $this->db->query("SELECT * FROM arq_processo WHERE lead=:lead ", [':lead'=>$lead]);
            if(!$resultP){
                //Preparar dados para o processo
                if($result['proprietario']==0 && $result['creditohabitacao']==0){
                    $tipohabitacao = 2;
                } elseif ($result['proprietario']==1 && $result['creditohabitacao']==0) {
                    $tipohabitacao = 4;
                } else {
                    $tipohabitacao = 3;
                }
                // INSERIR Processo
                $resultP = $this->db->query("INSERT INTO arq_processo(lead, user, nome, nif, email, telefone, idade, vencimento, tipohabitacao, valorpretendido, tipocredito) "
                        . " VALUES( :lead, :user, :nome, :nif, :email, :telefone, :idade, :vencimento, :tipohabitacao, :valorpretendido, :tipocredito) "
                        , [':lead'=>$lead, ':user'=>$result['user'], ':nome'=>$result['nome'], ':nif'=>$result['nif'], ':email'=>$result['email'], ':telefone'=>$result['telefone'], 
                        ':idade'=>$result['idade'], ':vencimento'=>$result['rendimento1'], ':tipohabitacao'=>$tipohabitacao, ':valorpretendido'=>$result['montante'], ':tipocredito'=>$result['tipo']]);
                //Inserir em outros creditos caso existam
                $linha=1;
                if($result['valorcreditohabitacao'] > 0){
                    $this->db->query("INSERT INTO cad_outroscreditos(lead, linha, tipocredito, valorcredito, prestacao, liquidar) VALUES(:lead, :linha, 'Crédito Habitação', :valorcredito, 0, 0) ",
                            [':lead'=>$lead, ':linha'=>$linha, ':valorcredito'=>$result['valorcreditohabitacao']]);
                            $linha++;
                }
                if($result['outroscreditos']>0){
                    $this->db->query("INSERT INTO cad_outroscreditos(lead, linha, tipocredito, valorcredito, prestacao, liquidar) VALUES(:lead, :linha, 'Outros', :valorcredito, 0, 1) ",
                            [':lead'=>$lead, ':linha'=>$linha, ':valorcredito'=>$result['outroscreditos']]);                    
                }
                return;
            }
            return $resultP[0];
        }
    }
}
