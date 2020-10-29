<?php

/**
 * Description of Error: Cria o objeto erro com os parametros recebidos
 * @todo Guardar na base de dados no registo de erros de entrada
 * @author pedro
 */
class Result {
    private $error;
    /**
     * 
     * @param type $isError
     * @param type $message
     * @param type $idleadorig
     * @return type
     */
    public function set($isError, $message, $idleadorig) {
        $isError ? $this->log($message, $idleadorig) : null;
        $this->error = new stdClass();
        $this->error->result = !$isError;
        $this->error->message = $message;
        $idleadorig>0 ? $this->error->lead = $idleadorig: null;
        return $this->error;
    }
    
    /**
     * 
     * @param type $message
     * @param type $id
     * @param type $obj
     */
    private function log($message, $id) {
        $db = new DB();
        $text = "ID: ".$id."; MSG: ".$message;
        $db->query("INSERT INTO arq_logerroapi(query) VALUES(:query)", 
                [':query'=>$text]);
    }
}
