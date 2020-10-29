<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

require_once __DIR__ . '/vendor/autoload.php';
require_once 'db/DB.php';
require_once 'Email.php';
require_once 'Agenda.php';
require_once 'User.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Docs
 *
 * @author pedro
 */
class Docs {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
        
    }
    /**
     * 
     * @return type
     */
    public function getAll (){
        return $this->db->query("SELECT * FROM cnf_docnecessaria");
    }
    /**
     * 
     * @param type $lead
     * @param type $linha
     * @return obj Documento Pedido
     */
    public function getOneDocPedido($lead, $linha) {
        return $this->db->query("SELECT D.*, N.sigla, N.nomedoc FROM cad_docpedida D "
                . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc "
                . " WHERE D.lead=:lead AND D.linha=:linha ", [':lead'=>$lead, ':linha'=>$linha]);
    }
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getDocs($lead) {
        return  $this->db->query("SELECT N.*, D.recebido, D.linha, F.tipo, F.nomefx "
         ." FROM  cnf_docnecessaria N "
         ." LEFT JOIN cad_docpedida D ON N.id =D.tipodoc AND D.lead=:lead "
         ." LEFT JOIN arq_documentacao F ON F.lead= D.lead AND F.linha=D.linha AND D.recebido=1", array(':lead'=>$lead));

    }
    /**
     * 
     * @param type $lead
     * @return docpedida
     */
    public function getDocPedida($lead) {
        return $this->db->query("SELECT D.lead, D.linha, D.recebido, D.tipodoc, D.datarecebido, N.nomedoc, N.sigla, N.descricao, L.status "
                . " FROM cad_docpedida D "
                . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc "
                . " INNER JOIN arq_leads L ON L.id=D.lead "
                . " WHERE D.lead=:lead ORDER BY D.tipodoc, D.linha", [':lead'=>$lead]);        
    }
    /**
     * Se toda a documentação foi recebida
     * @param type $lead
     * @return boolean
     */
    public function isAllReceived($lead) {
        $result = $this->db->query("SELECT count(*) AS qty FROM cad_docpedida WHERE recebido=0 AND lead=:lead ", [':lead'=>$lead]);
        if($result[0]['qty']>=0){
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param type $lead
     * @param type $linha
     * @return type
     */
    public function getDoc($lead, $linha ) {
        return $this->db->query("SELECT A.*, N.nomedoc "
                . " FROM arq_documentacao A "
                . " INNER JOIN cad_docpedida D ON D.lead=A.lead AND D.linha=A.linha "
                . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc "
                . " WHERE A.lead=:lead AND A.linha=:linha LIMIT 1 ", array(':lead'=>$lead, ':linha'=>$linha)); 
    }
    /**
     * 
     * @param type $lead
     * @param type $obj
     */
    public function saveDocs($lead, $obj) {
            $resp ='';
            $result = $this->db->query("SELECT linha FROM cad_docpedida WHERE lead=:lead AND tipodoc=:tipodoc AND recebido=0 ", array(':lead'=>$lead,  ':tipodoc'=>$obj->id));
            if($result){
                $this->db->query("UPDATE cad_docpedida SET recebido=1 WHERE lead=:lead AND tipodoc=:tipodoc ", array(':lead'=>$lead,  ':tipodoc'=>$obj->id));
               $resp = $this->db->query("INSERT INTO arq_documentacao( lead, linha, tipo, nomefx, fx64) VALUES( :lead, :linha, 'pdf', :nomefx, :fx64)" , 
                    array(':lead'=>$lead, ':linha'=>$result[0]['linha'], ':nomefx'=>$obj->nomefx, ':fx64'=> substr($obj->base64, 28) ));
            } else {
                $result = $this->db->query("SELECT max(linha)+1 AS ln FROM cad_docpedida WHERE lead=:lead " , array(':lead'=>$lead));
                if($result[0]['ln']>0){
                    $linha = $result[0]['ln'];
                } else {
                    $linha =1;
                }
                $this->db->query("INSERT INTO cad_docpedida(lead, linha, numpedido, tipodoc, recebido, datarecebido) VALUES(:lead, :linha, 1, :tipodoc, 1, NOW()) ",
                        array(':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$obj->id));
            
                $resp= $this->db->query("INSERT INTO arq_documentacao( lead, linha, tipo, nomefx, fx64) VALUES( :lead, :linha, 'pdf', :nomefx, :fx64)" , 
                        array(':lead'=>$lead, ':linha'=>$linha, ':nomefx'=>$obj->nomefx, ':fx64'=> substr($obj->base64, 28) ));
            }
            return $resp;
    }
    
    public function deleteDocs($lead) {
        $this->db->query("DELETE FROM cad_docpedida WHERE lead=:lead", [':lead'=>$lead]);
        return $this->db->query("DELETE FROM arq_documentacao WHERE lead=:lead ", [':lead'=>$lead]);
    }
    /**
     * 
     * @param type $lead
     * @param type $tipoDoc
     * @return type
     */
    public function deleteById($lead, $linha) {
        // apagar linha com documento
        $this->db->query("DELETE FROM arq_documentacao WHERE lead=:lead AND linha=:linha ",
                array(':lead'=>$lead, ':linha'=>$linha));
        // atualizar como documento não recebido
        return $this->db->query("UPDATE cad_docpedida SET recebido=0 WHERE lead=:lead AND linha=:linha AND recebido=1 ",
                array(':lead'=>$lead, ':linha'=>$linha));
    }    
    /**
     * 
     * @param type $lead
     * @param type $tipoDoc
     * @return type
     */
    public function delete($lead, $tipoDoc) {
        //obter o numero de linha 
        $result = $this->db->query("SELECT linha FROM cad_docpedida WHERE lead=:lead AND tipodoc=:tipodoc AND recebido=1 ", array(':lead'=>$lead, ':tipodoc'=>$tipoDoc));
        // apagar linha com documento
        $this->db->query("DELETE FROM arq_documentacao WHERE lead=:lead AND linha=:linha ", array(':lead'=>$lead, ':linha'=>$result[0][0]));
        // atualizar como documento não recebido
        return $this->db->query("UPDATE cad_docpedida SET recebido=0 WHERE lead=:lead AND tipodoc=:tipodoc AND recebido=1 ", array(':lead'=>$lead, ':tipodoc'=>$tipoDoc));
    }
    /**
     * 
     * @param type $lead
     * @param type $doc
     * @param type $nomeFx
     * @param type $fxBase64
     * @return string
     */    
    public function anexaDoc($lead, $doc, $nomeFx, $fxBase64, $tipofx) {
        if($tipofx=='pdf'){
            $fx64 = substr($fxBase64, 28);
        } else {
//            $fx64 = substr($fxBase64, 22);
            //Call function to convert to pdf. Returns base64 pdf
            try {
              $fx64 = $this->convToPdf($fxBase64); 
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
            //alterar o nome do fx
            $nomeFx = substr($nomeFx,0, strpos($nomeFx,'.')).'.pdf';
        }
            $resp ='';
            $result = $this->db->query("SELECT linha FROM cad_docpedida WHERE lead=:lead AND tipodoc=:tipodoc AND recebido=0 ", array(':lead'=>$lead,  ':tipodoc'=>$doc->tipodoc));
            if($result && $result[0]['linha']){
                $this->db->query("UPDATE cad_docpedida SET recebido=1, datarecebido=NOW() WHERE lead=:lead AND tipodoc=:tipodoc ", array(':lead'=>$lead,  ':tipodoc'=>$doc->tipodoc));
               $resp = $this->db->query("INSERT INTO arq_documentacao( lead, linha, tipo, nomefx, fx64) VALUES( :lead, :linha, :tipofx, :nomefx, :fx64)" , 
                    array(':lead'=>$lead, ':linha'=>$result[0]['linha'], ':nomefx'=>$nomeFx, ':fx64'=>$fx64 , ':tipofx'=>'pdf' ));
            } else {
                $result = $this->db->query("SELECT max(linha)+1 AS ln FROM cad_docpedida WHERE lead=:lead " , array(':lead'=>$lead));
                if($result[0]['ln']>0){
                    $linha = $result[0]['ln'];
                } else {
                    $linha =1;
                }
                $this->db->query("INSERT INTO cad_docpedida(lead, linha, numpedido, tipodoc, recebido, datarecebido) VALUES(:lead, :linha, 1, :tipodoc, 1, NOW()) ",
                        array(':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$doc->tipodoc));
            
                $resp = $this->db->query("INSERT INTO arq_documentacao( lead, linha, tipo, nomefx, fx64) VALUES( :lead, :linha, :tipofx, :nomefx, :fx64)" , 
                        array(':lead'=>$lead, ':linha'=>$linha, ':nomefx'=>$nomeFx, ':fx64'=>$fx64, ':tipofx'=>'pdf'  ));
            }
            return $resp;
    }

    public function updateDoc($lead, $linha, $fx64) {
        return $this->db->query("UPDATE arq_documentacao SET fx64=:fx64 WHERE lead=:lead AND linha=:linha"
                ,[':lead'=>$lead, ':linha'=>$linha, ':fx64'=>$fx64]);
    }
    /**
     * @ atualizar a lista de documentos necessários
     * @param type $obj
     */
    public function upDocs($obj) {
        // apaga todos os documentos pedidos para a lead
        $this->db->query("DELETE FROM cad_docpedida WHERE lead=:lead", [':lead'=>$obj->lead]);
        $linha=0;
        //Insere nova lista de documentos pedidos
        foreach ($obj->listDocs AS $doc){
            !isset($doc->tipodoc) ? $doc->tipodoc= $doc->id : null;
            !isset($doc->recebido) ? $doc->recebido= 0 : null;
            !isset($doc->datarecebido) ? $doc->datarecebido= null : null;

            $linha++;
            $this->db->query("INSERT INTO cad_docpedida(lead, linha, numpedido, tipodoc, recebido, datarecebido)"
                    . " VALUES(:lead, :linha, 2, :tipodoc, :recebido, :datarecebido)  ",
                    array( ':lead'=>$obj->lead, ':linha'=>$linha, ':tipodoc'=>$doc->tipodoc, ':recebido'=>$doc->recebido, ':datarecebido'=>$doc->datarecebido));
            
            }
        
        //Atualizar o status da lead se enviar email
        if($obj->sendEmail){
            //atualizar o status do arq_histrecuperacao
            $this->db->query("UPDATE arq_histrecuperacao SET status=4, data=NOW() WHERE lead=:lead ",  [':lead'=>$obj->lead]);
            //atualizar o status da lead
            $this->db->query("UPDATE arq_leads SET status=8, datastatus=NOW(), user=:user, userrec=:user WHERE id=:lead ", 
                    array(':user'=>$obj->gestor, ':lead'=>$obj->lead));
            
//            Regista no cad_agenda nova data prevista para receber documentação
            $agenda = new Agenda();
            $agenda->agendar($obj->lead, $obj->gestor, $obj->dataAgenda, $obj->horaAgenda, $obj->tipoAgenda);

            
            //Enviar email se sendEmail for verdadeiro
            // Envia com o nome do gestor responsavel pela lead
            $assunto = "(R)Ref: ".$obj->lead." - Pedido de documentação.";
            $msg = "Texto com a lista de documentação necessária";

           
            
            if( $resp = new Email($obj->lead, $obj->user, $assunto, $msg) ){
                  return "Email enviado! ". json_encode($resp);
            } else {
                return "Erro no envio do email!"; 
            }
          
        } else {
            return "sem enviar email";
        }
    }
    
        /**
     * Cria e devolve a lista dos documentos necessários para a lead
     * @param type $lead
     * @return type
     */
    public function getDocsNeeds($lead){
        //verificar se já tem documentação
        $result = $this->db->query("SELECT * FROM arq_documentacao WHERE lead=:lead", [':lead'=>$lead]);
        if($result && sizeof($result)>0){
            return $this->getDocPedida($lead); 
        } else {
          return $this->selectDocsToLead($lead); 
        }
        
    }
    
    public function getAllDocsList() {
        return $this->db->query("SELECT * FROM cnf_docnecessaria");
    }
    
         /**
     * Função para selecionar e adicionar a lista dos documentos 
     * necessários para a lead
     * @param type $lead
     * @return void
     */
    private function selectDocsToLead($lead) {
            //Selecionar a documentação a pedir
            
            $result = $this->db->query("SELECT tipocredito, segundoproponente FROM arq_processo WHERE lead=:lead", [':lead'=>$lead]);
            // Limpar a documentação existente e a lista pedida
            $this->deleteDocs($lead);
            $docs = $this->getAll();
            $linha=1;
            forEach($docs AS $d){
                if($result[0]['segundoproponente'] && $d['titular']<=2){
                    if ($d['tipocredito']=='T' || ($d['tipocredito']=='C' && $result[0]['tipocredito']=='CC')){
                        $this->db->query("INSERT INTO cad_docpedida( lead, linha, tipodoc) VALUES( :lead, :linha, :tipodoc) ",
                                [':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$d['id'] ]);
                        $linha++;
                    }
                } 
                  if(!$result[0]['segundoproponente'] && $d['titular']==1){
                    if ($d['tipocredito']=='T' || ($d['tipocredito']=='C' && $result[0]['tipocredito']=='CC')){  
                        $this->db->query("INSERT INTO cad_docpedida( lead, linha, tipodoc) VALUES( :lead, :linha, :tipodoc) ",
                                [':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$d['id'] ]);
                        $linha++;
                    }
                } 
            }
            return $this->getDocPedida($lead);
    }
    
    
    public function convToPdf($fx) {  //return base64 pdf
        $stamp = time();
        $filename = 'temp_'.$stamp;
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML('<img src="'.$fx.'" style="width: 210mm; height: 297mm; margin: 0;" />');
        $mpdf->showImageErrors = true;
        
        $mpdf->Output($filename, \Mpdf\Output\Destination::FILE);
        $b64Doc = chunk_split(base64_encode(file_get_contents($filename)));
        //remover o fx
        unlink($filename);
        return $b64Doc;
    }
}
