<?php

/**
 * Description of classAgendamento
 *
 * @author PCF
 */
class classAgendamento {
    //put your code here
    private $data1 ;
    private $diaSemana1;
    private $dataAtual;
    private $diaSemanaAtual;
    
    
    public function __construct($data1) {
        $this->data1 = $data1;
        $this->dataAtual = date('Y-m-d');
        $this->diaSemanaAtual = date('w', strtotime(date('Y-m-d')));
        $this->diaSemana1 = date('w', strtotime($data1));
    }

    
    //Função que retorna a data de agendamento caso a LEAD tenha mennos de 4 dias desde o primeiro agendamento
    public function getDataAgenda($differenceFormat = '%a' ){
            $resp = array();
            $datetime1 = date_create($this->data1);
            $datetime2 = date_create($this->dataAtual);
            $interval = date_diff($datetime1, $datetime2);
            $old = $interval->format($differenceFormat)+1;
            $margem = 3;
            //Se dia atual for segunda, então a margem é de 5 dias
            if($this->diaSemanaAtual == 1){
                $margem = 5;
            }
            //Se dia atual for terça então a margem é de 4 dias
            if($this->diaSemanaAtual == 2){
                $margem = 4;
            }
            //Se $old < $margem faz agendamento para 
            $localtime = localtime(time(), true);
      //      echo $old;
            if($old<=$margem){ 
                //se está no periodo da manhã agenda para proximo dia util e periodo 2 (tarde)
                if($localtime['tm_hour'] <13 ){ 
                    $resp['dataAg'] = $this->getNextUtilDay();
                    $resp['periodoAg'] = 2;
                    $resp['agenda'] = true;
                }
                //se está no periodo da tarde agenda para o proximo dia util periodo 1 (manhã)
                if($localtime['tm_hour'] >=13){
                    $resp['dataAg'] = $this->getNextUtilDay();
                    $resp['periodoAg'] = 1;
                    $resp['agenda'] = true;
                }
            } else {
                $resp['agenda'] = false;
            }
        return $resp;

    }
    
    
    
    
    
    //Functions
    
    function getNextUtilDay(){
        if($this->diaSemanaAtual == 5){
            $dataAg = date('Y-m-d', strtotime("+3 days"));
        } else {
           $dataAg = date('Y-m-d', strtotime("+1 days")); 
        }
        return $dataAg;
    }
    
}
