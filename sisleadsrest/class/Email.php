<?php

/*
 * Enviar email. Recebe numero de lead, userId, assunto e mensagem.
 * 
 * Obtem os dados necessários a partir do nº cliente
 * 
 * regista e retorna o resultado do envio de email
 */
require_once 'db/DB.php';
require_once 'User.php';
require_once 'Leads.php';
require_once 'EmailLog.php';
require_once '../class/configs.php';
require_once '../class/class.phpmailer.php';
require_once '../class/class.smtp.php';
/**
 * Description of Email
 *
 * @author pedro
 */
class Email {
    private $db;
    private $user;
    private $client;
    
    /**
     * 
     * @param type $lead
     * @param type $userId
     * @param type $assunto
     * @param type $msg
     */
    public function __construct($lead, $userId, $assunto, $msg) {
        $this->db = new DB();

        $this->user = (new User())->getUserData($userId)[0];
        $this->client = (new Leads())->getLeadClientData($lead)[0];
            
        
            $footer = $this->getFooter($this->user);
            
            $msg = $msg.$footer;
             //Enviar o email

            $mail = new PHPMailer();

//            $mail->DKIM_domain ="gestlifes.com";
//            $mail->DKIM_private ='8d57af3432d77e6._domainkey.gestlifes.com';
//            $mail->DKIM_selector='14400.gestlifes.com';
//            $mail->DKIM_passphrase='';
//            $mail->DKIM_identity=$this->user['email'];


            //$mail->SMTPDebug=4;
            $mail->isSMTP();
            $mail->Host = HOST;
            $mail->Port = PORT;
            $mail->SMTPSecure = SMTPSECURE;
            $mail->SMTPAuth = SMTPAUTH;
            $mail->Username = $this->user['email'];
            $mail->Password = PASSWORD;
            $mail->setFrom($this->user['email'],"GESTLIFES");
            $mail->addAddress($this->client['email']);
            //
            if(isset($anexo) && $anexo!=''){
                $mail->addAttachment($anexo);
            }

            $mail->Subject = utf8_decode($assunto);
            $mail->isHTML(TRUE);
            $mail->Body = utf8_decode($msg);
             //$mail->addStringEmbeddedImage($image,'logo_email_xs','logo_email_xs.png');
            $mail->WordWrap = 50;
            //LOG
            if(!$mail->send()){
                //regista erro
                new EmailLog($userId, $this->client['email'], $assunto, $mail->ErrorInfo);
                return FALSE;
            } else { 
                new EmailLog($userId, $this->client['email'], $assunto, null);
                return TRUE;
            }    
    }
    
    
    
        /**
         * 
         * @param type $user
         * @return string
         */
        private function getFooter($user) {
        
             $footer = "<p>Atenciosamente,</p>"
            . "<p><strong>".$user['nome']."</strong><br/>"
            . "Tlm: +351 ".$user['telefone']."<br/>"
            . "Email: ".$user['email']."<br/>"
            . "Rua de Camões, nº111,2ºandar sala11<br/>"
            . "4000-144 Porto<br/>"
            . "www.gestlifes.com</p>"
            ."<small>"
            . "<p>A GESTLIFES, FINANCES SOLUCTIONS FOR YOUR LIFE, é uma marca regista da JPCOM, Unipessoal. Lda, com o NIF: 513 476 121 e com sede na Rua de Camões n.111 Sala 11 2ºandar, 4000-144 Porto."
            ."<br/>Encontra-se registada e autorizada pelo Banco de Portugal, como <u>Intermediário de crédito vinculado nº 0001409</u>, seguro de responsabilidade civil assegurado pela Hiscox, com a apólice nº 2510343 válido até 18-06-2019."
            ."<br/>A GESTLIFES no âmbito das suas funções devidamente autorizadas pelo Banco de Portugal, desdobra-se nas seguintes formas:</p>"
            ."<ul>"
                 ."<li>Serviços de comunicação e promoção de produtos de crédito ao consumidor;</li>"
                ."<li>Serviços pré-contratuais (apresentação ou propostas de contratos de crédito a consumidores, assistência a consumidores, mediante a realização de atos preparatórios ou outros trabalhos de gestão pré-contratual relativamente a contratos de crédito que tenham sido por si apresentados ou propostos);</li>"
                ."<li>Serviços de contratação, traduzidos na celebração de contratos de crédito com consumidores, em nome das entidades que representa;</li>"
                ."<li>Serviços de Consultoria relativamente aos contratos de crédito ao consumidor;</li>"
                ."<li>Tipo de crédito que pode oferecer aos consumidores: Crédito Pessoal - Crédito Consolidado - Crédito Automóvel - Cartão de Crédito.</li>"
            ."</ul>"
            ."<p>A GESTLIFES, representa no mercado as seguintes entidades de crédito: COFIDIS - CREDIBOM - CETELEM - UNICRE. "
            ."<br/><u>A GESTLIFES, não pode receber ou entregar quaisquer valores relacionados com a formação, a execução e o cumprimento antecipado dos contratos de crédito.</u>"
            ."<br/>Em caso de litígios, a GESTLIFES dispõe dos seguintes meios aos consumidores:</p>"
            ."<ul>"
                ."<li>Apresentar reclamação junto do Banco de Portugal;</li>"
                ."<li>No Centro Nacional de Informação e Arbitragem de Conflitos de Consumo (CNIACC), www.cniacct.pt / geral@cniacc.pt / +351 253 619 107;</li>"
                ."<li>No Centro de Informação de Consumo e Arbitragem de Porto (CICAP),www.cicap.pt / cicap@cicap.pt / +351 225 029 791.</li>"
            ."</ul>"
            ."</small>"
            . "<p><small>Este correio eletrónico é propriedade da GESTLIFES, deve ser considerado confidencial e dirigido unicamente aos seus destinatários."
            ." O acesso, cópia ou utilização desta informação por qualquer outra pessoa ou entidade não é autorizado."
             ."  Se recebeu este documento por erro por favor notifique o remetente imediatamente.</small></p>";
        return $footer;
    }
}


