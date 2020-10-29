<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Result.php';
require_once 'Processo.php';
require_once 'PortalAccess.php';

/**
 * Description of Lead
 *
 * @author pedro
 */
class Lead {

    private $db;
    private $error;
    private $Processo;

    public function __construct() {
        $this->db = new DB();
        $this->error = new Result();
        $this->Processo = new Processo();
    }

    public function saveLead($obj, $supplier) {
        //Check required fields
        if (!isset($obj->email)) {
            return $this->error->set(true, "Falta email do cliente", $obj->idleadorig);
        }
        if (!isset($obj->telefone)) {
            return $this->error->set(true, "Falta telefone do cliente", $obj->idleadorig);
        }


        //check optional fields
        !isset($obj->idleadorig) ? $obj->idleadorig = '0' : null;
        !isset($obj->codigofiltro) ? $obj->codigofiltro = '' : null;
        !isset($obj->nomelead) ?  $obj->nomelead = $supplier['empresa']: null;
        !isset($obj->nome) ? $obj->nome = '' : null;
        !isset($obj->telefone) ? $obj->telefone = '' : null;
        !isset($obj->tipo) ? $obj->tipo = 'CP' : null;
        !isset($obj->nif) ? $obj->nif = 0 : null;
        !isset($obj->idade) ? $obj->idade = 0 : null;
        if (substr($obj->idade, 0, 4) > 99 ) {
            $obj->idade = date('Y') - substr($obj->idade, 0, 4);
        }

        
        // Verificar se o montante é numerico o texto
        if (isset($obj->montante)) {
            if (!is_numeric($obj->montante)) {
                $obj->montante = (int) preg_replace('/[a-zA-z.,]/', '', $obj->montante) / 100;
            }
        } else {
            $obj->montante = 0;
        }
        // Verificar se o montante é numerico ou texto
        if (isset($obj->rendimento)) {
            if (!is_numeric($obj->rendimento)) {
                $obj->rendimento = (int) preg_replace('/[a-zA-z.,]/', '', $obj->rendimento) / 100;
            }
        } else {
            $obj->rendimento = 0;
        }        
        !isset($obj->rendimento2) ? $obj->rendimento2 = 0 : null;
        !isset($obj->outroscreditos) ? $obj->outroscreditos = 0 : null;
        !isset($obj->situacao) ? $obj->situacao = '' : null;
        !isset($obj->outrainfo) ? $obj->outrainfo = '' : null;
        !isset($obj->gcid) ? $obj->gcid = '' : null;

        !isset($obj->codigopostal) ? $obj->codigopostal = '' : null;
        !isset($obj->proprietario) ? $obj->proprietario = 0 : null;
        !isset($obj->creditohabitacao) ? $obj->creditohabitacao = 0 : null;
        !isset($obj->valorcreditohabitacao) ? $obj->valorcreditohabitacao = 0 : null;
        !isset($obj->maisdinheiro) ? $obj->maisdinheiro = 0 : null;
        !isset($obj->montantepessoal) ? $obj->montantepessoal = 0 : null;
        !isset($obj->tipocredito) ? $obj->tipocredito='CP': null;
        
        //Verificar se o supplier é codificado

        if ($supplier['encrypt']==1) {
            try {
                openssl_private_decrypt(base64_decode($obj->nome), $obj->nome, $supplier['privKey']);
                openssl_private_decrypt(base64_decode($obj->email), $obj->email, $supplier['privKey']);
                openssl_private_decrypt(base64_decode($obj->telefone), $obj->telefone, $supplier['privKey']);
                openssl_private_decrypt(base64_decode($obj->nif), $obj->nif, $supplier['privKey']);
            } catch (Exception $exc) {
                return $this->error->set(true, "Erro na encriptacao. " . $exc, $obj->idleadorig);
            }
        }

        // Verificar se já foi inserida recentemente
            if ($obj->idleadorig != 0) {
                $result0 = $this->db->query("SELECT count(*) AS num FROM arq_leads "
                        . " WHERE (idleadorig=:idleadorig OR email=:email OR telefone=:telefone) AND DATE(dataentrada)=DATE(NOW())"
                        . " AND fornecedor=:fornecedor",
                        [':idleadorig' => $obj->idleadorig, ':email' => $obj->email, ':telefone' => $obj->telefone, ':fornecedor' => $obj->fornecedor]);
            } else {
                $result0 = $this->db->query("SELECT count(*) AS num FROM arq_leads "
                        . " WHERE (email=:email OR telefone=:telefone) AND DATE(dataentrada)=DATE(NOW())"
                        . " AND fornecedor=:fornecedor",
                        [':email' => $obj->email, ':telefone' => $obj->telefone, ':fornecedor' => $obj->fornecedor]);
            }
            if ($result0[0]['num']) {
                return $this->error->set(true, "Lead repetida. Inserida recentemente", $obj->idleadorig);
            }

            // Para BPS
            !isset($obj->montante) ? $obj->montante=0 : null;
            isset($obj->tipo) && $obj->tipo =='pessoal' ? $tipo='CP' : $tipo='CC';
            if ((isset($obj->mais_dinheiro) && $obj->mais_dinheiro > 0) || (isset($obj->outros_creditos) && $obj->outros_creditos>0) 
                    && (!isset($obj->montante_pessoal) || $obj->montante_pessoal == 0) ) {
                // $tipo = 'CC';
                $obj->montante = $obj->mais_dinheiro + $obj->outros_creditos;
                if(isset($obj->outros_creditos) && $obj->outros_creditos>0) {
                    $obj->situacao = $obj->situacao.';  '.' Outros creditos: '.$obj->outros_creditos;
                }
            }
            if (isset($obj->montante_pessoal) && $obj->montante_pessoal > 0 ) {
                // $tipo = 'CP';
                $obj->montante = $obj->montante_pessoal;
                if(isset($obj->outros_creditos) && $obj->outros_creditos>0) {
                    $obj->situacao = $obj->situacao.';  '.'Outros creditos: '.$obj->outros_creditos;
                }
            }
            !isset($obj->prazopretendido) ? $obj->prazopretendido = 0 : null;


        try {
            
            $obj->nome =  ucwords(strtolower($obj->nome));
            //Numero de telefone, limpar caracters não numericos
            $obj->telefone = preg_replace('/[a-zA-Z-._]/', '', $obj->telefone);
            
            $result = $this->db->query("INSERT INTO arq_leads(idleadorig, codigofiltro, nomelead, fornecedor, tipo, nome, "
                    . " email, telefone, idade, nif, montante, prazopretendido, rendimento1, outroscreditos, situacao, info, gcid, dataentrada, status) "
                    . " VALUES(:idleadorig, :codigofiltro, :nomelead, :fornecedor, :tipo, :nome, "
                    . " :email, :telefone, :idade, :nif, :montante, :prazopretendido, :rendimento1, :outroscreditos, :situacao, :info, :gcid, NOW(), 1)",
                    [':idleadorig' =>$obj->idleadorig, ':codigofiltro' =>$obj->codigofiltro, ':nomelead' => $obj->nomelead, ':fornecedor' => $obj->fornecedor, ':tipo' => $obj->tipo,
                        ':nome' => $obj->nome, ':email' => $obj->email, ':telefone' => $obj->telefone, ':idade' => $obj->idade, ':nif' => $obj->nif,
                        ':montante' => $obj->montante, ':prazopretendido' => $obj->prazopretendido, ':rendimento1' => $obj->rendimento, ':outroscreditos' => $obj->outroscreditos,
                        ':situacao' => $obj->situacao, ':info' => $obj->outrainfo, ':gcid' => $obj->gcid]);

            if ($lead = $this->db->lastInsertId()) {
                //Insere no processo
                $this->Processo->insert($lead, $obj->nome, $obj->nif, $obj->email, $obj->telefone, $obj->idade, $obj->rendimento,
                        $obj->montante, $obj->prazopretendido, $obj->tipocredito);
                new PortalAccess($lead, $obj->nome, $obj->email, $obj->nomelead);
                return $this->error->set(false, "Inserido com sucesso", $lead);
            }
            return $this->error->set(true, "Erro ao inserir na BD", $obj->idleadorig);
        } catch (Exception $exc) {
            return $this->error->set(true, "Erro na insercao da lead. " . $exc, $obj->idleadorig);
        }
    }


}
