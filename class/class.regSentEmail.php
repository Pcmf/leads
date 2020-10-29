<?php
/**
 * Description of regSentEmail
 *
 * @author pedro
 */
class regSentEmail {
    private $user;
    private $destino;
    private $assunto;
    private $con;
    //put your code here
    public function __construct($con,$u,$d,$a) {
        $this->user = $u;
        $this->destino = $d;
        $this->assunto = $a;
        $this->con = $con;
      
    }
    
    function registOk() {
        //insere como com sucesso
        mysqli_query($this->con,sprintf("INSERT INTO arq_logemail(user,destino,assunto) VALUES(%s,'%s','%s')",
                $this->user, $this->destino, $this->assunto));
    }
    function registErro($erro) {
        //insere como com sucesso
        mysqli_query($this->con,sprintf("INSERT INTO arq_logemail(user,destino,assunto,erro) VALUES(%s,'%s','%s','%s')",
                $this->user, $this->destino, $this->assunto,$erro));
    }
}
