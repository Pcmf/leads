<?php
// require_once 'db/DB.php';
require_once  'class/Leads.php';
require_once  'class/Agenda.php';
require_once  'class/Processo.php';
require_once  'class/Contacto.php';
require_once  'class/Registo.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of COFID
 *
 * @author pedro
 */
class COFID {
    private $db;
    private $leads;
    private $user;
    private $agenda;
    private $processo;
    private $contacto;
    private $registo;
    private $email;
    
    public function __construct() {
        $this->db = new DB();
        $this->user = new User();
        $this->leads = new Leads();
        $this->agenda = new Agenda();
        $this->processo = new Processo();
        $this->contacto = new Contacto();
        $this->registo = new Registo();
    }
    /**
     * 
     * @param type $type
     * @param type $data
     * @return type
     */
    public function getAllSerch($type, $data) {
       return $this->db->query("SELECT  P.lead, P.nome, P.telefone, P.email, P.nif, S.nome AS statusnome "
               . " FROM arq_processo P "
               . " INNER JOIN arq_leads L ON L.id=P.lead "
               . " INNER JOIN cnf_statuslead S ON S.id=L.status "
               . " WHERE P.".$type." LIKE :parm ", array(':parm'=>$data."%"));
    }
    
    /**
     * 
     * @return array  
     */
    public function getProcessos($user){
        $resp = array();
        //Verificar se há lead de recuperação agendada para o momento
        if($lead = $this->agenda->checkAgenda($user)){
        //    return $lead;
            return $this->processo->getProcessoByLead($lead);
        }
        //Obter lead que esteja ativa
        $result = $this->db->query("SELECT lead FROM arq_histrecuperacao WHERE user=:user AND status=1 LIMIT 1", [':user'=>$user]);
        if($result){
            return $this->processo->getProcessoByLead($result[0]['lead']);
        }
        // Obter leads com status 9 e que sejam novas
        return $this->processo->getProcesso($user);
    }
    /**
     * 
     * @param type $user
     * @param type $lead
     * @return type
     */
    public function getProcessoByLead( $lead) {
        return $this->processo->getProcessoByLead( $lead);
    }
    
    /**
     * 
     * @param int $lead
     * @param array $obj
     * @return array
     */
    public function setCOFD( $obj) {
        //atualizar o status da lead com o status 28 ou 29
        $this->db->query("UPDATE arq_leads SET status=:status, datastatus=NOW() WHERE id=:lead ", array(':status'=>$obj->status, ':lead'=>$obj->lead));
        //Regista o contacto
        $this->registo->setRegistoContacto($obj->lead, $obj->user, 14, 0, 0);
        
        //insert no cofidisdireto
        if($obj->status != 29){
        return $this->db->query("INSERT INTO arq_cofidisdirecto(lead, processo, status, datastatus, user, data) VALUES(:lead, :processo, :status, NOW(), :user, NOW()) " ,
                array(':lead'=>$obj->lead, ':processo'=>$obj->processoCofidis, ':status'=>$obj->status, ':user'=>$obj->user));
        } else {
            return;
        }
    }
    
    public function getCofidis($user) {
        $resp = array();
        
        $result =  $this->db->query("SELECT C.*, P.*, L.id AS leadId,  L.status, L.datastatus, L.dataentrada, SP.nome AS tipocontratonome,"
                                                . "  SP2.nome AS tipocontratonome2, SF.nome AS estadocivilnome, H.nome AS habitacaonome, H2.nome AS habitacao2 "
                                                . " FROM `arq_cofidiscontacto` C "
                                                ." INNER JOIN arq_processo P ON P.lead=C.lead "
                                                ." INNER JOIN arq_leads L ON L.id=C.lead "
                                                . " INNER JOIN cnf_sitprofissional SP ON SP.id=P.tipocontrato "
                                                . " LEFT JOIN cnf_sitprofissional SP2 ON SP2.id=P.tipocontrato2 "
                                                . " INNER JOIN cnf_sitfamiliar SF ON SF.id=P.estadocivil "
                                                . " INNER JOIN cnf_tipohabitacao H ON H.id=P.tipohabitacao "
                                                . " LEFT JOIN cnf_tipohabitacao H2 ON H2.id=P.tipohabitacao2 "                
                                                ." WHERE L.status = 34 ORDER BY C.data LIMIT 1");
        
        $resp['dados'] = $result[0];
        
        $lead = $result[0]['leadId'];

            if($oc = $this->db->query("SELECT * FROM cad_outroscreditos WHERE lead=:lead", array(':lead'=>$lead ))) {
                $resp['ocArr'] = $oc;
            }
            if($or = $this->db->query("SELECT * FROM cad_outrosrendimentos WHERE lead=:lead", array(':lead'=>$lead))){
                $resp['orArr'] = $or;
            }
        return $resp;
    }
    
    /**
     * 
     * @return array
     */
    public function getDashData ($user) {
        $resp = array();
        $ativos = $this->db->query("SELECT count(*) AS qty FROM arq_histrecuperacao WHERE user=:user AND status=1 ",  [':user'=>$user]);
        $agendados = $this->db->query("SELECT count(*) AS qty FROM arq_histrecuperacao R "
                . " INNER JOIN cad_agenda A ON A.lead=R.lead WHERE R.status IN(2,3,6) AND R.user=:user "
                . "  AND A.status=1 AND (A.agendadata < DATE(NOW()) OR ( A.agendadata=DATE(NOW()) AND HOUR(A.agendahora) <= HOUR(NOW())))", [':user'=>$user]);
        
        $news = $this->db->query("SELECT count(*) AS qty FROM arq_leads L "
                . " LEFT JOIN arq_histrecuperacao R ON R.lead=L.id "
                . " WHERE R.lead IS NULL AND L.status= 9  AND  DATEDIFF( DATE(NOW()), DATE(L.dataentrada) ) < 60 ");
        
        
        $rejected = $this->db->query("SELECT count(*) AS qty FROM arq_leads WHERE status=29 AND YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus) = MONTH(NOW())");

        $recup = $this->db->query("SELECT count(*) AS qty FROM arq_histrecuperacao WHERE status=4  AND user=:user ", [':user'=>$user]);
        

        $total = $ativos[0]['qty'] + $agendados[0]['qty']+$news[0]['qty'] ;
        $resp['total'] = $total;
        $resp['rejeitados']= $rejected[0]['qty'];
        $resp['recuperados'] = $recup[0]['qty'];
        return $resp;
    }
    
    public function getDashDirData($user) {
        $resp = array();
        $resp['inseridos'] = $this->db->query("SELECT count(*) AS qty FROM arq_leads WHERE status=28 AND YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus) = MONTH(NOW())")[0];
        
        $resp['rejeitados'] = $this->db->query("SELECT count(*) AS qty FROM arq_leads WHERE status=29 AND YEAR(datastatus)=YEAR(NOW()) AND MONTH(datastatus) = MONTH(NOW())")[0];
        $resp['novas'] = $this->db->query("SELECT count(*) AS qty FROM arq_leads WHERE status=34")[0];
        return $resp;
    }
    
    /**
     * 
     * @param type $obj
     * @return type
     */
    public function agendaAutomatica($obj){
        //se status recuperação for 3 - agendada automaticamente vai anular
        if($obj->recupstatus == 3){
            //cancelar lead - limpar agenda
            $this->leads->changeLeadStatus($obj->lead, 29);
            // registar motivo de rejeição
            $this->db->query("INSERT INTO cad_rejeicoes(lead, motivo) VALUES(:lead, 'Não atende')", [':lead'=>$obj->lead]);
            $this->agenda->limparAgenda($obj->lead);
            //  e atualizar o arq_histrecuperacao
            $this->db->query("UPDATE arq_histrecuperacao SET status=10, data=NOW() WHERE lead=:lead", [':lead'=>$obj->lead]);
            //regista contacto
            $this->registo->setRegistoContacto($obj->lead, $obj->user, 25, 0, 0);
            return;
        }
        //Agendar para outro dia
        $this->agenda->agendaAutomatica($obj->user, $obj->lead);
        //Atualiza o histrecuperacao
        $this->db->query("UPDATE arq_histrecuperacao SET status=3, data=NOW() WHERE lead=:lead", [':lead'=>$obj->lead]);  
        //regista contacto
        $this->registo->setRegistoContacto($obj->lead, $obj->user, 19, 0, 0);
        return;
    }
    
    /**
     * 
     * @param type $obj
     * @return type
     */
    public function agendaManual($obj) {
            //regista contacto
            $this->registo->setRegistoContacto($obj->lead, $obj->user, 20, 0, 0);    
        //se o histStatus =2  - agendamento para um segundo contacto 
        if($obj->histStatus==2){
            $this->agenda->agendar($obj->lead, $obj->user, $obj->dataAgenda, $obj->horaAgenda, 6);
            $this->db->query("UPDATE arq_histrecuperacao SET status=2, data=NOW() WHERE lead=:lead", [':lead'=>$obj->lead]);

            return;
        }
        // se o tipo de agendamento for 6 - agendamento Cofidis - guarda informação do formulario
            $this->agenda->agendar($obj->lead, $obj->user, $obj->dataAgenda, $obj->horaAgenda, 6);
            $this->db->query("UPDATE arq_histrecuperacao SET status=6, data=NOW() WHERE lead=:lead", [':lead'=>$obj->lead]);  
            if($obj->formContacto){
                return $this->contacto->setContacto($obj->user, $obj->lead, $obj->formContacto);
            }
            return;
    }
    
    /**
     * 
     * @param type $obj
     */
    public function cofidisContacto($obj) {
        //Guarda os dados do formulario
        $this->contacto->setContacto($obj->user, $obj->lead, $obj->formContacto);
        //Atualizar o status da lead para 34 - lista cofidis
        $this->leads->changeLeadStatus($obj->lead, 34);
        //Atualizar o status no arq_histrecuperacao
        $this->db->query("UPDATE arq_histrecuperacao SET status=5, data=NOW() WHERE lead=:lead", [':lead'=>$obj->lead]);
        //regista contacto
        $this->registo->setRegistoContacto($obj->lead, $obj->user, 22, 0, 0);          
    }
    /**
     * Criar email para pedir documentação
     * @param type $user
     * @param type $lead
     * @param type $obj
     */
    public function askDocs($userId, $lead, $obj) {
        //Registar a documentação a pedir
        $linha=0;
        //Insere nova lista de documentos pedidos
        foreach ($obj->docsList AS $doc){
            $linha++;
            $this->db->query("INSERT INTO cad_docpedida(lead, linha, tipodoc)"
                    . " VALUES(:lead, :linha, :tipodoc)  ",
                    array( ':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$doc->id));  //$doc->id (tipodoc)
        }
        //Agendar a data prevista para receber a documentação
        $this->agenda->agendar($lead, $userId, $obj->dataAgenda, '08:00:00', 3);
        
        //Obter dados  para compor o assunto e o texto da msg
        $user = $this->user->getUserData($userId)[0];
      // $cliente = $this->leads->getLeadClientData($lead);
        $p = $this->processo->getBasicProcesso($lead)[0];
        $tc = '';
        $pz='.';
           //Tipo de credito
            switch ($p['tipocredito']) {
                case 'CP':
                    $tc = "Crédito Pessoal - " . $p['valorpretendido'] . " Euros";
                    $pz = " pelo prazo de " . $p['prazopretendido'] . " meses.";
                    break;
                case 'CC':
                    $tc = "Crédito Consolidado - " . $p['valorpretendido']  . " Euros";
                    $pz = " pelo prazo de " . $p['prazopretendido']. " meses.";
                    break;
                case 'CT':
                    $tc = 'Cartão de Crédito';
                    $pz = ".";
                    break; 
            }
        // Assunto
        $assunto = "(R) Ref ".$lead.": Documentação para ".$tc;
            //Lista dos documentos pedidos
            $lista = "<ul>";
            foreach ($obj->docsList AS $d) {
                $lista .= "<li><u>" . $d->nomedoc . ".</u> <span>" . $d->descricao . "</span></li>";
            }
            $lista .= "</ul>";
            
            $simulacao = "<p><h4><strong>Valor de simulação -</strong> &nbsp;&nbsp;&nbsp;<em>Valor pretendido: <strong>".$p['valorpretendido']  ." Euros</strong></em>"
                    ." &nbsp;&nbsp;&nbsp;&nbsp;<em>Prazo: <strong>". $p['prazopretendido']." Meses</strong></em>"
                    ." &nbsp;&nbsp;&nbsp;&nbsp;<em>Prestação: <strong>".$p['prestacaopretendida']." Euros</strong></em></h4></p></br></br>";    
            
            //Mensagem
            $msg = "<p>Exmo(a). Sr(a) " . $p['nome'] . "</p>"
                    . "<p>Agradeço desde já a sua disponibilidade, e confiança, para trabalharmos o seu pedido de "
                    . $tc.$pz . "</p>"
                    . "<p>A GestLifes procura a solução de financiamento adaptada às necessidades dos seus clientes,"
                    . " neste sentido pretendemos esclarecer alguns pontos importantes para a análise do processo,"
                    . "<u><b> sem nenhum tipo de custo face à nova lei de intermediários financeiros</b></u>.</p>"
                    .$simulacao
                    . "<p>No seguimento do contacto telefónico, venho por este meio solicitar o envio da seguinte documentação afim de viabilizar a proposta:<p>"
                    . "<p>" . $lista . "</p>"
                    . "<p>Caso o seu pedido de crédito tenha dois ou mais titulares, será necessário o envio da documentação acima mencionada, referente a todos os titulares.</p>"
                    . "<p>Poderá enviar a documentação, anexada, respondendo a este email, ou usando este endereço: " . $user['email'] . " </p>"
                    . "<p>Se tiver preferência, poderá remeter toda a documentação via  CTT com a seguinte informação:</p>"
                    . "<p>GestLifes<br/>Ac. " . $user['nome'] . "<br/>Rua de Camões nº111, 2º andar sala 11<br/>4000-144 Porto</p>"
                //    . "<p>Aceitar termos RGPD: " . (new postRgpd($lead))->button() . "</p>" 
                    . "<p>Grato(a) pela atenção dispensada.</p>";   
            
            // Enviar o Email
            return $this->email = new Email($lead, $userId, $assunto, $msg);

    }
   
    /**
     * 
     * @param type $user
     * @param type $tipo
     * @return array
     */
    public function getList($user, $tipo) {
        if($tipo == 4) {
            return $this->db->query("SELECT P.lead, P.nome, P.telefone, P.email, P.nif, L.datastatus, S.nome AS statusnome "
                    . " FROM arq_histrecuperacao R INNER JOIN arq_leads L ON L.id=R.lead "
                    . " INNER JOIN arq_processo P ON P.lead=R.lead "
                    . " INNER JOIN cnf_statuslead S ON S.id=L.status "
                    . " WHERE R.status=4 AND R.user=:user AND YEAR(L.datastatus)=YEAR(NOW()) AND MONTH(L.datastatus)=MONTH(NOW()) ", [':user'=>$user]);  //
        }
        
        return  $this->db->query("SELECT P.lead, P.nome, P.telefone, P.email, P.nif, L.datastatus, S.nome AS statusnome "
                    . " FROM arq_leads L "
                    . " INNER JOIN arq_processo P ON P.lead=L.id "
                    . " INNER JOIN cnf_statuslead S ON S.id=L.status "
                    . " WHERE L.status=:tipo AND YEAR(L.datastatus)=YEAR(NOW()) AND MONTH(L.datastatus)=MONTH(NOW()) ", [':tipo'=>$tipo]);
                
    }
    
    /**
     * 
     * @param type $user
     * @param type $lead
     * @param type $ob
     * @return type
     */
    public function anularPeloGR($user, $lead, $obj) {
        // Alterar o status da lead para anulado pelo GR
        $this->leads->changeLeadStatus($lead, 29);
        
        // inserir justificação da rejeição
        $this->db->query("INSERT INTO cad_rejeicoes(lead, motivo) VALUES(:lead, :motivo) " , [':lead'=>$lead, ':motivo'=>$obj->motivo]);
        
        // Altera o status no arq_histrecuperacao
        $this->db->query("UPDATE arq_histrecuperacao SET status=9, data=NOW() WHERE lead=:lead", [':lead'=>$lead]);
        
        // limpar agenda
        $this->agenda->limparAgenda($lead);
        
        return;
        
    }
}
