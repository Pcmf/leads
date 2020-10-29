<?php
require_once './../class/PasswordGenerator.php';
require_once 'sendEmails.php';
/**
 * Description of PortalAccess
 *
 * @author pedro
 */
class PortalAccess {

    private $db;
    
    public function __construct($lead, $nome, $email, $nomelead) {
        $this->db = new DB();
        //Obter a data e a hora
        $diaSemanaAtual = date('w', strtotime(date('Y-m-d'))); //  6-sabado 7- domingo
        $hora = date('H:i');
        // Verificar se está fora do horario de trabalho
//        if ($diaSemanaAtual == 0 || $diaSemanaAtual == 6 || ($diaSemanaAtual < 6 && ($hora < '09:55' || $hora > '19:00'))) {
            //Obter uma senha
            $pass = gerarPassword(6);
            //Criar registo no cad_clientes com uma password
            $this->db->query("INSERT INTO cad_clientes( lead, nome, email, password) VALUES(:lead, :nome, :email, :password) ",
                            [':lead'=>$lead, ':nome'=>$nome, ':email'=>$email, ':password'=>passwordHash::hash($pass)]);

            //Enviar email e registar
            $senha = '';
            if (isset($pass) && $pass) {
                $senha = "Senha de acesso: " . $pass;
            }

            $assunto = "Ref " . $lead . " - Obtenha o seu crédito agora!!";

            $msg = "<p>Olá " . $nome . "!</p>"
                    . "<p>Recebemos o seu pedido do crédito através do ".$nomelead." Por favor continue a submissão de dados"
                    . " através da "
                    . "<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>"
                    . "<h3><strong>( " . $senha . " )</strong></h3>"
                     . "<p>Trabalhamos com as melhores marcas de crédito e temos a licença "
                    . " <a href='https://www.bportugal.pt/intermediariocreditofar/jpcom-unipessoal-lda'>nº 0001409</a> do Banco Portugal. </p>"
                    . "<p>Desta forma conseguimos comparar por si a melhor oferta para que obtenha "
                    . "<strong>as melhores taxas, sem custos e sem compromissos.</strong></p>"
                    . "<p>Para mais informações consulte a <a href='https://www.gestlifes.com/politica-privacidade/'>Política de Privacidade</a>"
                    . " e <a href='https://www.gestlifes.com/termos-condicoes/'>Termos e Condições</a>.</p>"; 

            return new sendEmails( 99, "noreply@gestlifes.com", $email, $assunto, $msg, '', 4, $lead);
//        }
        return;
    }

}
