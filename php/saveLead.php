<?php

require_once "openCon.php";
require_once "passwordHash.php";
require_once '../class/PortalAccessEmail.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);

//Check if user is valid
if (isset($dt->supplier)) {
    $supplier = strip_tags(trim($dt->supplier));
} else {
    echo'Falta o nome do fornecedor.';
}
//Check if password is valid
if (isset($dt->password)) {
    $password = strip_tags(trim($dt->password));
} else {
    echo'Falta a senha de acesso.';
}

$query = sprintf("SELECT * FROM cad_fornecedorleads where nome='%s'", $supplier);
$result = mysqli_query($con, $query);
if ($result) {

    $row = mysqli_fetch_array($result, MYSQLI_BOTH);
    if ($row[0]) {
        //Check if password is valid
        if (passwordHash::check_password($row['password'], $dt->password)) {
            $montante = 0;
            //sanitize LEAD data
            //Lead name
            $nomeLead = strip_tags(trim($dt->leadName));
            //Client name
            $nome = $dt->nome;
            //codigo postal
            isset($dt->codigo_postal) ? $codigo_postal = strip_tags(trim($dt->codigo_postal)) : $codigo_postal = '';
            //Comentarios
            $comentarios = '';
            isset($dt->comentarios)? $comentarios = utf8_decode(strip_tags(trim($dt->comentarios))) : $comentarios='';
             //situacao
            isset($dt->situacao) ? $situacao = strip_tags(trim($dt->situacao)) : $situacao = '';
            //Lead type
            $tipo='';
            isset($dt->tipo) && $dt->tipo =='pessoal' ? $tipo='CP' : $tipo='CC';
            !isset($dt->montante) ? $montante=0 : null;
            
            if ((isset($dt->mais_dinheiro) && $dt->mais_dinheiro > 0) || (isset($dt->outros_creditos) && $dt->outros_creditos>0) 
                    && (!isset($dt->montante_pessoal) || $dt->montante_pessoal == 0) ) {
               // $tipo = 'CC';
                $montante = $dt->mais_dinheiro + $dt->outros_creditos;
                if(isset($dt->outros_creditos) && $dt->outros_creditos>0) {
                    $situacao = $situacao.';  '.' Outros creditos: '.$dt->outros_creditos;
                }
            }
            if (isset($dt->montante_pessoal) && $dt->montante_pessoal > 0 ) {
              //  $tipo = 'CP';
                $montante = $dt->montante_pessoal;
                if(isset($dt->outros_creditos) && $dt->outros_creditos>0) {
                    $situacao = $situacao.';  '.'Outros creditos: '.$dt->outros_creditos;
                }
            }
            (isset($dt->rendimento_1) && is_integer($dt->rendimento_1)) ? $rendimento_1 = $dt->rendimento_1 : $rendimento_1 = 0;
            isset($dt->telefone) ? $telefone = $dt->telefone : $telefone = '';
            isset($dt->email) ? $email = $dt->email : $email = '';
            isset($dt->nif) ? $nif = $dt->nif : $nif = 0;
            (isset($dt->proprietario) && is_int($dt->proprietario)) ? $proprietario = $dt->proprietario : $proprietario = 0;
            (isset($dt->emprestimo_habitacao) && is_int($dt->emprestimo_habitacao)) ? $emprestimo_habitacao = $dt->emprestimo_habitacao : $emprestimo_habitacao = 0;
            (isset($dt->credito_habitacao) && is_int($dt->credito_habitacao)) ? $credito_habitacao = $dt->credito_habitacao : $credito_habitacao = 0;
            (isset($dt->outros_creditos) && is_int($dt->outros_creditos)) ? $outros_creditos = $dt->outros_creditos : $outros_creditos = 0;
            (isset($dt->mais_dinheiro) && is_int($dt->mais_dinheiro)) ? $mais_dinheiro = $dt->mais_dinheiro : $mais_dinheiro = 0;
            (isset($dt->montante_pessoal) && is_int($dt->montante_pessoal)) ? $montante_pessoal = $dt->montante_pessoal : $montante_pessoal = 0;

            !isset($dt->codigofiltro) ? $dt->codigofiltro='' : null;

            //nacionalidade
            isset($dt->nacionalidade) ? $nacionalidade = strip_tags(trim($dt->nacionalidade)) : $nacionalidade = NULL;
            //rendimento_declarado_em
            isset($dt->rendimento_declarado_em) ? $rendimento_declarado_em = strip_tags(trim($dt->rendimento_declarado_em)) : $rendimento_declarado_em = NULL;
            //tipo_contrato
            isset($dt->tipo_contracto) ? $tipo_contrato = strip_tags(trim($dt->tipo_contracto)) : $tipo_contrato = NULL;
            //tipo emprego
            isset($dt->tipo_emprego) ? $tipo_emprego = strip_tags(trim($dt->tipo_emprego)) : $tipo_emprego = NULL;
            //duracao do contrato
            (isset($dt->duracao_contracto)) ? $duracao_contracto = strip_tags(trim($dt->duracao_contracto)) : $dt->duracao_contracto = NULL;
            (isset($dt->rendimento_2) && is_integer($dt->rendimento_2)) ? $rendimento_2 = $dt->rendimento_2 : $rendimento_2 = 0;

            !isset($dt->nascimento) ? $dt->nascimento = '1900-01-01' : NULL;
            $anoNasc = explode('-', $dt->nascimento);
            $idade = (date('Y')) - $anoNasc[0];

            if ($row['encrypt']) {
                openssl_private_decrypt(base64_decode($nome), $nome, $row['privKey']);
                openssl_private_decrypt(base64_decode($email), $email, $row['privKey']);
                openssl_private_decrypt(base64_decode($telefone), $telefone, $row['privKey']);
                openssl_private_decrypt(base64_decode($nif), $nif, $row['privKey']);
            }
            //Verificar se já existe alguma lead com o mesmo idleadorig
            $q0 = sprintf("SELECT count(*) FROM arq_leads WHERE idleadorig =%s ", $dt->leadId);
            $result0 = mysqli_query($con, $q0);
            if ($result0) {
                $row0 = mysqli_fetch_array($result0, MYSQLI_NUM);
                if ($row0[0] > 0) {
                    echo 'Ok';  //Já existe, não insere mas retorna OK porque na inserção anterior não tinha recebido resposta
                } else {
                    //Save to DB
                    $query = sprintf("INSERT INTO arq_leads(idleadorig, codigofiltro, nomelead,fornecedor,tipo,nome,email,telefone,idade,nif,montante,codigopostal,"
                            . " proprietario,creditohabitacao,valorcreditohabitacao,outroscreditos,maisdinheiro,montantepessoal,"
                            . " tipocontrato, rendimento2, situacao, info, rendimento1, status, datastatus) "
                            . " VALUES('%s','%s',%s,'%s','%s','%s','%s',%s,%s,%s,'%s',%s,%s,%s,%s,%s,%s,'%s',%s,'%s','%s',%s,1,NOW())",
                            $dt->leadId, $dt->codigofiltro, $nomeLead, $row['id'], $tipo, $nome, $email, $telefone, $idade, $nif, $montante, $codigo_postal, $proprietario,
                            $emprestimo_habitacao, $credito_habitacao, $outros_creditos, $mais_dinheiro, $montante_pessoal,
                            $tipo_contrato, $rendimento_2, $situacao, $comentarios, $rendimento_1);
                    $result = mysqli_query($con, $query);
                    if ($result) {
                        $lead = mysqli_insert_id($con);
                        // INSERIR lead no arq_processo
                        mysqli_query($con, sprintf("INSERT INTO arq_processo(lead, user, nome, email, telefone, nif, idade, vencimento, tipocredito, valorpretendido)"
                                        . " VALUES(%s, 0, '%s', '%s', '%s', '%s', %s, %s, '%s', %s)",
                                        $lead, $nome, $email, $telefone, $nif, $idade, $rendimento_1, $tipo, $montante ));
                        new PortalAccessEmail($con, $lead, $nome, $email);
                        echo"Ok";
                    } else {
                        mysqli_query($con, sprintf('INSERT INTO arq_logerroapi(query) VALUES("%s")', $query));
                        echo"Erro IDB";
                    }
                }
            }
        } else {
            echo'Password Errada';
        }
    } else {
        echo'Fornecedor não registado';
    }
    mysqli_close($con);
} 


