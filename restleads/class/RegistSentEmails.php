<?php
//require_once './../sisleadsrest/DB.php';
/**
 * Description of RegistSentEmails
 *
 * @author pedro
 */
class RegistSentEmails {
    private $user;
    private $destino;
    private $assunto;
    private $db;
    //put your code here
    public function __construct($u,$d,$a) {
        $this->db = new DB();
        $this->user = $u;
        $this->destino = $d;
        $this->assunto = $a;

      
    }
    
    function registOk() {
        //insere como com sucesso
        $this->db->query("INSERT INTO arq_logemail(user,destino,assunto) VALUES(:user, :destino, :assunto)",
               [':user'=>$this->user, ':destino'=>$this->destino, ':assunto'=>$this->assunto]);
    }
    function registErro($erro) {
        //insere como com erro
        $this->db->query("INSERT INTO arq_logemail(user,destino,assunto, erro) VALUES(:user, :destino, :assunto, :erro)",
               [':user'=>$this->user, ':destino'=>$this->destino, ':assunto'=>$this->assunto, ':erro'=>$erro]);
    }
}
