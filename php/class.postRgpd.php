<?php
/**
 * Description of postRgpd
 *  Cria um botão para incluir no email e aceitar o RGPD via POST
 * @author pedro
 */
class postRgpd {
    private $lead;

    public function __construct($lead) {
        $this->lead = $lead;       
    }
    
    
    function button() {
        $html = "<form type='GET' action='https://sisleads.gestlifes.com/php/registerRgpd.php'>"
                . "<input type='hidden' name='lead' value='".$this->lead."' />"
                . "<button type='submit'>Aceitar RGPD</button>"
                . "</form>";
        return $html;
    }
    
}
