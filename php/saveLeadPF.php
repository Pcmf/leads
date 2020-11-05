<?php

require_once "openCon.php";
require_once "passwordHash.php";
require_once '../class/PortalAccessEmail.php';

$data = file_get_contents("php://input");
$dt = json_decode($data);

//Check if user is valid
if(isset($dt->supplier)){
    $supplier = strip_tags(trim( $dt->supplier));
} else{
    echo'Falta o nome do fornecedor.';
}
//Check if password is valid
if(isset($dt->password)){
    $password = strip_tags(trim( $dt->password));
} else{
    echo'Falta a senha de acesso.';
}

$query = sprintf("SELECT * FROM cad_fornecedorleads where nome='%s'",$supplier);
$result = mysqli_query($con,$query);
if($result){
    
    $row = mysqli_fetch_array($result,MYSQLI_BOTH);
    if($row[0]){
        //Check if password is valid
        if(passwordHash::check_password($row['password'],$dt->password)){
            $montante = 0;
            //sanitize LEAD data
            //Lead name
            $nomeLead = strip_tags(trim($dt->leadName));
            //Client name
            $nome= ucwords( mb_strtolower( strip_tags(trim($dt->nome)), 'UTF-8'));
            //codigo postal
            isset($dt->codigo_postal) ? $codigo_postal = strip_tags(trim($dt->codigo_postal)) : $codigo_postal=NULL;
            //Lead type
            if(isset($dt->mais_dinheiro) && $dt->mais_dinheiro>0){
                $tipo = 'CC';
                $montante = $dt->mais_dinheiro;
            } 
            if(isset($dt->montante_pessoal) && $dt->montante_pessoal>0){
                $tipo = 'CP';
                $montante = $dt->montante_pessoal;
            }
            (isset($dt->rendimento_1) && is_integer($dt->rendimento_1))? $rendimento_1=$dt->rendimento_1:$rendimento_1=0;
            isset($dt->telefone)? $telefone=$dt->telefone:$telefone='';
            isset($dt->email)? $email=$dt->email:$email='';
            isset($dt->nif)? $nif=$dt->nif:$nif=0;
            !isset($dt->idade)? $dt->idade=0:null;
            (isset($dt->proprietario) && is_int($dt->proprietario))? $proprietario=$dt->proprietario:$proprietario=0;
            (isset($dt->emprestimo_habitacao) && is_int($dt->emprestimo_habitacao))? $emprestimo_habitacao=$dt->emprestimo_habitacao:$emprestimo_habitacao=0;
            (isset($dt->credito_habitacao) && is_int($dt->credito_habitacao))? $credito_habitacao=$dt->credito_habitacao:$credito_habitacao=0;
            (isset($dt->outros_creditos) && is_int($dt->outros_creditos))? $outros_creditos=$dt->outros_creditos:$outros_creditos=0;
            (isset($dt->mais_dinheiro)) ? $mais_dinheiro=$dt->mais_dinheiro:$mais_dinheiro=0;
            (isset($dt->montante_pessoal)) ? $montante_pessoal=$dt->montante_pessoal:$montante_pessoal=0;
            //situacao
            isset($dt->situacao)? $situacao = strip_tags(trim($dt->situacao)):$situacao = NULL;
            //Comentarios
            isset($dt->comentarios)? $comentarios = utf8_decode(strip_tags(trim($dt->comentarios))):$comentarios=NULL;
            //nacionalidade
            isset($dt->nacionalidade)? $nacionalidade = strip_tags(trim($dt->nacionalidade)):$nacionalidade=NULL;
            //rendimento_declarado_em
            isset($dt->rendimento_declarado_em)? $rendimento_declarado_em = strip_tags(trim($dt->rendimento_declarado_em)):$rendimento_declarado_em=NULL;
            //tipo_contrato
            isset($dt->tipo_contracto)? $tipo_contrato = strip_tags(trim($dt->tipo_contracto)):$tipo_contrato =NULL;
            //tipo emprego
            isset($dt->tipo_emprego)? $tipo_emprego = strip_tags(trim($dt->tipo_emprego)):$tipo_emprego=NULL;
            //duracao do contrato
            (isset($dt->duracao_contracto))   ? $duracao_contracto = strip_tags(trim($dt->duracao_contracto)):$dt->duracao_contracto=NULL;
            (isset($dt->rendimento_2) && is_integer($dt->rendimento_2))? $rendimento_2=$dt->rendimento_2:$rendimento_2=0;
            
            if($montante_pessoal ==0 && $mais_dinheiro >0 ) {
                $montante = $mais_dinheiro;
            } else {
                $montante = $montante_pessoal;
            }
            
            //Verificar se já existe lead com mesmo idorigem, fornecedor e nome de lead
            $query0 = sprintf("SELECT count(*) FROM arq_leads WHERE idleadorig=%s AND nomelead LIKE '%s' AND fornecedor=%s" , $dt->leadId,$nomeLead,$row['id']);
            $result0 = mysqli_query($con, $query0);
            if($result0){
                $row0 = mysqli_fetch_array($result0, MYSQLI_NUM);
                if($row0[0] == 0) {
                    //INSERIR - passo1
                    $query = sprintf("INSERT INTO arq_leads(idleadorig, nomelead, fornecedor, nome, email, telefone, montante ,status, datastatus) "
                            . " VALUES('%s', '%s', %s, '%s', '%s', '%s',%s, 1, NOW())",
                            $dt->leadId, $nomeLead, $row['id'], $nome, $email, $telefone, $montante);
                } else {
                    //ATUALIZAR
                    $query = sprintf("UPDATE arq_leads SET fornecedor=fornecedor+1, nome='%s', tipocontrato='%s', rendimento1=%s, rendimento2=%s, valorcreditohabitacao=%s ,"
                            . "  outroscreditos=%s, situacao='%s', info= '%s', datastatus=NOW() "
                            . "  WHERE idleadorig=%s AND nomelead LIKE '%s' AND fornecedor=%s  ", 
                            $nome, $tipo_contrato, $rendimento_1, $rendimento_2, $credito_habitacao, $outros_creditos, $situacao, $comentarios, $dt->leadId,$nomeLead,$row['id']);
                }
            }
            

            $result = mysqli_query($con,$query);
            $lead = mysqli_insert_id($con);
            if($result){
                if($row0[0] == 0) {
                    new PortalAccessEmail($con, $lead, $nome, $email);
                }
                echo"Ok";
            } else {
                mysqli_query($con, sprintf('INSERT INTO arq_logerroapi(query) VALUES("%s")', $query));
                echo"Erro IDB";
            }

        } else {
           echo'Password Errada'; 
        }
    } else {
        echo'Fornecedor não registado'; 
    }
} 


