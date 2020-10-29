<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

require_once 'passwordHash.php';
require_once 'db/DB.php';
require_once 'Leads.php';
require_once 'Processo.php';
require_once 'Docs.php';
require_once 'Email.php';

/**
 * Description of Client
 *
 * @author pedro
 */
class Client {

    private $db;
    private $Lead;
    private $Processo;
    private $Docs;

    public function __construct() {
        $this->db = new DB();
        $this->Lead = new Leads();
        $this->Processo = new Processo();
        $this->Docs = new Docs();
    }

    /**
     * 
     * @param type $obj
     * @return boolean
     */
    public function login($obj) {
        $res = array();
        //Verificar se utilizador existe
        if ($resp = $this->db->query("SELECT * FROM cad_clientes WHERE email=:email AND ativo=1 ", array(':email' => $obj->username))) {
            //verificar se a password e utilizador correspondem
            $this->valido = false;

            foreach ($resp AS $r) {
                //      if ($r['password'] == $password) {
                if (passwordHash::check_password($r['password'], $obj->password)) {
                    $this->token = $this->generateToken($r);
                    $this->db->query("UPDATE cad_clientes SET token=:token, ultimoacesso=NOW(), numacessos=numacessos+1 WHERE id=:id ", array(':token' => $this->token, ':id' => $r['id']));
                    $this->valido = true;
                    break;
                }
            }
            if ($this->valido) {
                return $res['resp']= $this->token;
            } else {
                return $res['resp']= false;
            }
        }
    }
    /**
     * 
     * @param type $lead
     * @param type $status
     * @return type
     */
    public function updateLeadStatus($lead, $status) {
        return $this->Lead->changeLeadStatus($lead, $status);
    }

    /**
     * 
     * @param type $lead
     * @return string
     */
    public function cltcr($lead) {
        return $this->Lead->getOne(null, $lead);
    }
    

    
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getLead($lead) {
        if($this->Processo->exist($lead)){
            return $this->Processo->getCLTprocesso( $lead);
        } else {
            return $this->db->query("SELECT id, nome, telefone, email, idade, nif, montante, prazopretendido, "
            ." rendimento1 AS vencimento, status FROM arq_leads WHERE id=:lead ", [':lead'=>$lead]);
        }
    }
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getProcessoByLead($lead) {
        if($this->Processo->exist($lead)){
            return $this->Processo->getProcessoByLead($lead);
        } else {
            return $this->getLead($lead);
        }
    }

    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getDocs($lead) {
        return $this->Docs->getDocPedida($lead);
    }
    /**
     * Selecionar o documento pedido
     * @param type $lead
     * @param type $linha
     * @return type
     */
    public function getDocPedido($lead, $linha) {
        return $this->Docs->getOneDocPedido($lead, $linha);
    }
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getDadosGestor($lead) {
        return $this->db->query("SELECT U.* FROM cad_utilizadores U INNER JOIN arq_leads L ON L.user=U.id WHERE L.id=:lead", [':lead'=>$lead]);
    }
    
    /**
     * 
     * @param type $lead
     * @param type $obj
     * @return type
     */
    public function saveForm($lead, $obj) {
        //Obter o gestor da lead
        $gestor = $this->db->query("SELECT user FROM arq_leads WHERE id=:lead", [':lead'=>$lead]);
        if(!$gestor[0]['user']){
            $gestores = [24, 26, 1001];
            $r = rand(0, sizeof($gestores)-1);
            $gestor = $gestores[$r];
          
        } else {
            $gestor = $gestor[0]['user'];
        }
   //     return $gestor;
        //Atualizar arq_leads
        $this->db->query("UPDATE arq_leads SET nome=:nome, telefone=:telefone, idade=:idade, email=:email, nif=:nif, status=:status, datastatus=NOW(), user=:user  "
                . " WHERE id=:lead ", 
                array(':nome'=>$obj->nome, ':telefone'=>$obj->telefone, ':idade'=>$obj->idade, ':email'=>$obj->email, ':nif'=>$obj->nif, 
                    ':lead'=>$lead, ':status'=>$obj->status , ':user'=>$gestor ));
        //Verificar se o processo já foi criado
       //return $this->Processo->exist($lead);
        if($this->Processo->exist($lead)){
            //atualizar processo
            $this->Processo->update($lead, $obj);
        } else {
        //inserir no arq_processo
            $this->Processo->setProcesso($lead,0, $obj);
        }
        //Alterar o agendamento
        $this->db->query("UPDATE cad_agenda SET status=0 WHERE status=1 AND lead=:lead", [':lead'=>$lead]);
        $this->db->query("INSERT INTO cad_agenda(lead, user, agendadata, agendahora, tipoagenda, status) "
                . " VALUES(:lead, :user, DATE(NOW()), CURTIME(), 3, 1) ",
                [':lead'=>$lead, ':user'=>$gestor]);
        //inserir no outros créditos
        if(isset($obj->ocValor) && $obj->ocValor>0){
            $this->db->query("DELETE FROM cad_outroscreditos WHERE lead=:lead AND linha=1", [':lead'=>$lead]);
            $this->db->query("INSERT INTO cad_outroscreditos(lead, linha, tipocredito, valorcredito, prestacao) VALUES(:lead, 1, 'Diversos', :valorcredito, :prestacao) ",
                [':lead'=>$lead, ':valorcredito'=>$obj->ocValor, ':prestacao'=>$obj->ocPrestacao]);    
        }
        //registar cad_registocontactos - novo codigo
        $cnt = $this->db->query("SELECT max(A.contactonum) AS cntn FROM cad_registocontacto A WHERE A.lead=:lead", [':lead'=>$lead]);
        $cnt[0]['cntn']>0 ? $cntn=$cnt[0]['cntn'] + 1 : $cntn=1;
        $this->db->query("INSERT INTO cad_registocontacto(lead, user, contactonum, motivocontacto)"
                . " VALUES(:lead, 0, :contactonum, 27) ",
                [':lead'=>$lead, ':contactonum'=>$cntn]);
        return 'OK';
    }
    /**
     * Só para o portal
     * @param type $lead
     * @param type $obj
     * @return type
     */
    public function updateForm($lead, $obj) {
        !isset($obj->prestacaopretendida) ? $obj->prestacaopretendida=0 : null;
        !isset($obj->valorpretendido) ? $obj->valorpretendido=0 : null;
        !isset($obj->finalidade) ? $obj->finalidade='' : null;
        !isset($obj->outrainfo) ? $obj->outrainfo='' : null;        
        
        $this->db->query("UPDATE arq_processo SET tipocredito=:tipocredito, valorpretendido=:valor, prazopretendido=:prazo, prestacaopretendida=:prestacao,"
                . " finalidade=:finalidade, outrainfo=:outrainfo"
                . " WHERE lead=:lead", [':tipocredito'=>$obj->tipocredito, ':valor'=>$obj->valorpretendido, ':prazo'=>$obj->prazopretendido, 
                    ':prestacao'=>$obj->prestacaopretendida, ':lead'=>$lead, ':finalidade'=>$obj->finalidade, ':outrainfo'=>$obj->outrainfo ]);
        
        
        $sts = $this->Lead->getLeadStatus($lead);
   //     return $this->Docs->isAllReceived($lead);
        if($sts['status']==37){
            $this->selectDocsToLead($lead,$obj);
            $this->Lead->changeLeadStatus($lead, 38);
        } 
        return "OK";
    }

     /**
     * Função para selecionar e adicionar a lista dos documentos 
     * necessários para a lead
     * @param type $lead
     * @return void
     */
    private function selectDocsToLead($lead, $obj) {
            //Selecionar a documentação a pedir
            $docs = $this->Docs->getAll();
            $result = $this->db->query("SELECT segundoproponente FROM arq_processo WHERE lead=:lead ", [':lead'=>$lead]);
            // Limpar a documentação existente e a lista pedida
            $this->Docs->deleteDocs($lead);
            
            $linha=1;
            forEach($docs AS $d){
                if($result[0]['segundoproponente'] && $d['titular']<=2){
                    if ($d['tipocredito']=='T' || ($d['tipocredito']=='C' && $obj->tipocredito=='CC')){
                        $this->db->query("INSERT INTO cad_docpedida( lead, linha, tipodoc) VALUES( :lead, :linha, :tipodoc) ",
                                [':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$d['id'] ]);
                        $linha++;
                    }
                } 
                  if(!$result[0]['segundoproponente'] && $d['titular']==1){
                    if ($d['tipocredito']=='T' || ($d['tipocredito']=='C' && $obj->tipocredito=='CC')){  
                        $this->db->query("INSERT INTO cad_docpedida( lead, linha, tipodoc) VALUES( :lead, :linha, :tipodoc) ",
                                [':lead'=>$lead, ':linha'=>$linha, ':tipodoc'=>$d['id'] ]);
                        $linha++;
                    }
                } 
            }
            return;
    }
    /**
     * 
     * @param type $obj
     * @return type
     */
    public function anexaDoc($obj) {
        if(!isset($obj->op)){
            $this->Docs->anexaDoc($obj->lead, $obj->doc, $obj->nomeFx, $obj->fxBase64, $obj->type);
        } else {
            $this->mergeDoc($obj);
        }
        //Verificar se a documentação está completa
        return $this->checkDocumentacao($obj->lead);
    }
    /**
     * 
     * @param type $obj
     * @return type
     */
    public function anexaFotoAsDoc($obj) {
            $this->Docs->anexaDoc($obj->doc->lead, $obj->doc, $obj->doc->sigla.".", $obj->imagem, 'jpg');
        
        //Verificar se a documentação está completa
        return $this->checkDocumentacao($obj->doc->lead);    
    }
    
    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getOneComp($lead, $linha) {
        return $this->db->query("SELECT * FROM cad_comprovativos WHERE lead=:lead AND linha=:linha ", [':lead' => $lead, ':linha' => $linha]);
    }

    /**
     * 
     * @param type $lead
     * @return type
     */
    public function getComp($lead) {
        return $this->db->query("SELECT * FROM cad_comprovativos WHERE lead=:lead ", [':lead' => $lead]);
    }

    /**
     * 
     * @param type $obj
     * @return type
     */
    public function anexaComp($obj) {
        $this->db->query("UPDATE cad_comprovativos SET nomedoc=:nomedoc, tipodoc='pdf', documento=:documento, status=1"
                . " WHERE lead=:lead AND linha=:linha "
                , array(':nomedoc' => $obj->nomeFx, ':documento' => substr($obj->fxBase64, 28), ':lead' => $obj->lead, ':linha' => $obj->doc->linha));
        //Verificar se está completo
        return $this->checkComprovativos($obj->lead);
    }
    /**
     * 
     * @param type $obj
     * @return type
     */
    public function anexaCompImg($obj) {
        $this->db->query("UPDATE cad_comprovativos SET nomedoc=:nomedoc, tipodoc='jpg', documento=:documento, status=1"
                . " WHERE lead=:lead AND linha=:linha "
                , array(':nomedoc' => $obj->doc->instituicao, ':documento' => substr($obj->imagem, 23), ':lead' => $obj->doc->lead, ':linha' => $obj->doc->linha));
        //Verificar se está completo
        return $this->checkComprovativos($obj->doc->lead);
    }
    /**
     * 
     * @param type $type
     * @param type $obj
     * @return string
     */
    public function pedirPass($type, $obj) {
        if ($type == 'N') { //verificar pelo nif de cliente
            $result = $this->db->query("SELECT C.*, L.user FROM cad_clientes C INNER JOIN arq_leads L ON L.id=C.lead WHERE C.nif=:nif ", [':nif' => $obj->controlo]);
        } elseif ($type == 'E') {
            $result = $this->db->query("SELECT C.*, L.user FROM cad_clientes C INNER JOIN arq_leads L ON L.id=C.lead WHERE C.email=:email  AND C.ativo=1", [':email' => $obj->controlo]);
        } else {
            return "Não é possivel validar estes dados! ";
        }
        
        if($result) {
            $result = $result[0];
            $pass = $this->gerarPassword(6);
            $this->db->query("UPDATE cad_clientes SET password=:pass WHERE lead=:lead ", array(':lead'=>$result['lead'], ':pass'=> passwordHash::hash($pass)));
        
            // enviar o email com nova pass
            $assunto = "Portal do Cliente Gestlifes";
            $msg = "<p>Exmo(a) " . $result['nome'] . "</p>"
                    . "<p>Esta é a nova senha para o acesso no portal do cliente e deverá ser usada com este email.</p>"
                    . "<p> <strong> ".$pass."</strong></p>"
                    . "<p>Por questões de segurança aconselhavel alterar a senha após aceder ao site. Poderá fazer isso clicando no seu nome que aparece na barra do menu!"
                    
                    . "<p>Poderá aceder usando este endereço: https://sisleads.gestlifes.com/GestLifesClient</p>"
                    . "<br/><br/>";

           new Email($result['lead'], $result['user'], $assunto, $msg);
           
           return "Um novo acesso foi enviado para ".$result['email'];
            
        } else {
           $type=='N' ? $msg = "O NIF inserido não é válido!"  : $msg= "O email inserido não é válido!";
           return $msg;
        }
    }
    /**
     * 
     * @param type $lead
     * @param type $obj
     * @return type
     */
    public function changePass($lead, $obj) {
            $pass = $obj->pass;
            return $this->db->query("UPDATE cad_clientes SET password=:pass WHERE lead=:lead ", array(':lead'=>$lead, ':pass'=> passwordHash::hash($pass)));
            
    }
    
    public function registaCCPortal($obj) {
        //Verifica se já existe registo se existir incrementa visita ou download
        $result = $this->db->query("SELECT vezes FROM cad_cc_portal WHERE lead=:lead AND tipo=:tipo",  [':lead'=>$obj->lead, ':tipo'=>$obj->tipo]);
        if($result){
          //  return "insere";
            return $this->db->query("UPDATE cad_cc_portal SET vezes=vezes+1, ultimadata=NOW() WHERE lead=:lead AND tipo=:tipo",  [':lead'=>$obj->lead, ':tipo'=>$obj->tipo]);
        } else {
            return $this->db->query("INSERT INTO cad_cc_portal(lead, tipo) VALUES(:lead, :tipo) ", [':lead'=>$obj->lead, ':tipo'=>$obj->tipo]);
        }
    }
    
    
    private function mergeDoc($obj) {
        // Obter o Documento existente
        $doc1 = $this->Docs->getDoc($obj->lead, $obj->doc->linha);
        $data = (object)array();
        $data->cover = $doc1[0]['fx64'];
        if($obj->type != 'pdf') {
            $data->report = $this->Docs->convToPdf($obj->fxBase64);
        } else {
            $data->report = substr($obj->fxBase64, 28);
        }
        $fx64 = $this->sisPost($data);
        $this->Docs->updateDoc($obj->lead, $obj->doc->linha, $fx64);
    }
    
    // Para juntar PDFs em um unico PDF
	private function sisPost($data){      
                 $url = 'https://sisleads.gestlifes.com/MergeBase64PDF_api/mergeB64PDF_api.php'; 
     //   $url = 'http://localhost/MergeBase64PDF_api/mergeB64PDF_api.php';
		// Setup cURL
		$fx64 = curl_init($url);
		curl_setopt_array($fx64, array(
		    CURLOPT_POST => TRUE,
		    CURLOPT_RETURNTRANSFER => TRUE,
		    CURLOPT_HTTPHEADER => array(
		        'Content-Type: application/json'
		    ),
		    CURLOPT_POSTFIELDS => json_encode($data)
		));
		// Send the request
		return curl_exec($fx64);
	}    
    
    /**
     * 
     * @param type $num_caracteres
     * @return password
     */
    private function gerarPassword($num_caracteres = 8) {

        $password = "";

        // variável para definir quais o caractéres possíveis para a password

        $possiveis = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

        // para verificar quantos caractéres diferentes existem para gerar uma password

        $max = strlen($possiveis);

        // a password não pode ser ter mais caractéres do que os que foram predefinidos para $possiveis    

        if ($num_caracteres > $max) {

            $num_caracteres = $max;
        }

        // variável de incrementação para saber quantos caratéres já tem a password enquanto está a ser gerada

        $i = 0;

        // adiciona caracteres a $password até $num_caracteres estar completo    

        while ($i < $num_caracteres) {

            // escolhe um caracter dos possiveis

            $char = substr($possiveis, mt_rand(0, $max - 1), 1);

            // verificar se o caracter escolhido já está na $password?

            if (!strstr($password, $char)) {

                // se não estiver incluido adiciona o novo caracter...         

                $password .= $char;

                // ... e incrementa a variável $i        

                $i++;
            }
        }

        return $password;
    }

    /**
     * Check token and return user ID or false
     */
    private function generateToken($resp) {
        //Chave para a encriptação
        $key = 'klEpFGcl2019';
        
        //obter o status da lead
        $sts = $this->db->query("SELECT status FROM arq_leads WHERE id=:lead", [':lead'=>$resp['lead']])[0];

        //Configuração do JWT
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $header = json_encode($header);
        $header = base64_encode($header);

        //Obter o nome do fornecedor
        //Dados 
        $payload = [
            'iss' => 'GESTLIFES',
            'id' => $resp['id'],
            'nome' => $resp['nome'],
            'email' => $resp['email'],
            'lead' => $resp['lead'],
            'sts' => $sts['status']
        ];

        $payload = json_encode($payload);
        $payload = base64_encode($payload);

        //Signature

        $signature = hash_hmac('sha256', "$header.$payload", $key, true);
        $signature = base64_encode($signature);
        // echo $header.$payload.$signature;

        return "$header.$payload.$signature";
    }

    private function checkToken($token) {
        return $this->db->query("SELECT count(*) FROM cad_clientes WHERE token=:token", [':token' => $token]);
    }

    /**
     * Verifica comprovativos
     */
    private function checkComprovativos($lead) {
        $total = $this->db->query("SELECT count(*) AS total FROM cad_comprovativos A WHERE lead=:lead", [':lead' => $lead])[0];
        $recebidos = $this->db->query("SELECT count(*) AS recebidos FROM cad_comprovativos A WHERE lead=:lead AND status=1 ", [':lead' => $lead])[0];
        $dif = $total['total'] - $recebidos['recebidos'];
        if ($dif == 0) {
            //Atualizar o status para Comprovativos Recebidos
            $this->db->query("UPDATE arq_leads SET status=35, datastatus=NOW() WHERE id=:lead ", [':lead' => $lead]);
        }
        return $dif;
    }

    /**
     * verifica a documentação
     */
    private function checkDocumentacao($lead) {
        $total = $this->db->query("SELECT count(*) AS total FROM cad_docpedida A WHERE lead=:lead", [':lead' => $lead])[0];
        $recebidos = $this->db->query("SELECT count(*) AS recebidos FROM cad_docpedida A WHERE lead=:lead AND recebido=1 ", [':lead' => $lead])[0];
        $dif = $total['total'] - $recebidos['recebidos'];
        if ($dif == 0) {
           // $sts=36;
            // Verificar se foi pedida documentação a partir da analise 21. Se sim coloca o status a 22
            if($this->db->query("SELECT count(*) AS qty from arq_histprocess WHERE lead=:lead AND status=21 ", [':lead'=>$lead])[0]['qty'] > 0){
                $sts = 22;
            }
            //Atualizar o status para Doc Recebidos
            //$this->db->query("UPDATE arq_leads SET status=:sts, datastatus=NOW() WHERE id=:lead ", [':lead' => $lead, ':sts'=>$sts]);
        }
        return $dif;
    }

}
