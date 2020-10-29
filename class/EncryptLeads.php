<?php

/*
 * Encriptar/Decriptar os dados pessoais do arq_leads e colocar status a 26
 *
 * Desativar agendamentos
 * Desistencia no financiamentos. Colocar o status em 10 para os que estiverem em 4,6,7,11
 * Apagar lead, se existir, do cad_agendatemp
 */

/**
 * Description of EncryptLeads
 *
 * @author pedro
 */
class EncryptLeads {

        public function __construct($con, $action, $lead, $key) {

            $result = mysqli_query($con, sprintf("SELECT * FROM arq_leads WHERE id=%s", $lead));
            if($result){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                 $enome = new  encrypt_decrypt($action, $row['nome'], $key);
                 $etelefone = new  encrypt_decrypt($action,$row['telefone'], $key);
                 $enif = new  encrypt_decrypt($action,$row['nif'], $key);
                 $eemail = new  encrypt_decrypt($action,$row['email'], $key);
                 
                 
                 if($action == 'encrypt'){ 
                     mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s', telefone='%s', nif='%s', email='%s', rgpd=1, status=26, datastatus=NOW()  WHERE id=%s "
                         , $enome->getReturnString(), $etelefone->getReturnString(), $enif->getReturnString(), $eemail->getReturnString(), $lead));
                     
                    //Limpar a agenda
                    mysqli_query($con, sprintf("UPDATE cad_agenda SET status=0, data=NOW() WHERE lead=%s AND status=1 ",$lead));
                    //limpar a agenda temporaria
                    mysqli_query($con, sprintf("DELETE FROM cad_agendatemp WHERE lead=%s", $lead));
                    //Cancelar finaciamentos ativos
                    mysqli_query($con, sprintf("UPDATE cad_financiamentos SET status=10, datastatus=NOW() WHERE lead= %s AND status IN(4,6,7,11) " , $lead));
                } else {
                    //SE for decript
                    //Obter o ultimo status da lead antes do 26
                    $result = mysqli_query($con, sprintf("SELECT status FROM arq_histprocess "
                            . "WHERE lead=%s AND status!=26 ORDER BY data DESC LIMIT 1", $lead));
                    if($result){
                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        if($row['status'] >=6 && $row['status']<=8 ){
                            //Reativa o ultimo agendamento
                            $result0 = mysqli_query($con, sprintf("SELECT data FROM cad_agenda "
                                    . " WHERE lead=%s ORDER By data DESC LIMIT 1", $lead));
                            if($result0){
                                $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
                                mysqli_query($con, sprintf("UPDATE cad_agenda SET status=1 "
                                        . " WHERE lead=%s AND data='%s' ", $lead, $row0['data']));
                            }
                            
                        }
                    
                        mysqli_query($con, sprintf("UPDATE arq_leads SET nome='%s', telefone='%s', nif='%s', email='%s', rgpd=0, status=%s, datastatus=NOW()  WHERE id=%s "
                             , $enome->getReturnString(), $etelefone->getReturnString(), $enif->getReturnString(), $eemail->getReturnString(), $row['status'], $lead));  
                        mysqli_query($con, sprintf("DELETE FROM cad_encriptleads WHERE lead = %s", $lead));
                    }
                }
                
                
            }
        }
}
