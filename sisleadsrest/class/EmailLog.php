<?php
require_once 'db/DB.php';
/*
 * Log do envio de email
 */

/**
 * Description of EmailLog
 *
 * @author pedro
 */
class EmailLog {
    private $db;
    
    public function __construct($user, $emailDestino, $assunto, $erro) {
        $this->db = new DB();
        return $this->db->query("INSERT INTO arq_logemail(user,destino,assunto, erro) VALUES(:user,:destino,:assunto, :erro) "
                , array(':user'=>$user, ':destino'=>$emailDestino, ':assunto'=>$assunto, ':erro'=>$erro));
    }
    
}
