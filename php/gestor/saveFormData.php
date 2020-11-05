<?php

/* 
 *Recebe os dados do formulario, cria a lead com status 2, cria o processo com o numero da lead criada
 * e cria os registo  para pedir a documentação toda
 * 
 */

require_once '../openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$p = $dt->lead;

!isset($p->nomelead)?$p->nomelead='':null;
!isset($p->tipo)?$p->tipo='':null;
!isset($p->email)?$p->email='':null;
!isset($p->nif)?$p->nif='':null;
!isset($p->idade)?$p->idade=0:null;
!isset($p->outrainfo)?$p->outrainfo='':null;


//Criar a LEAD 
$query = sprintf("INSERT INTO arq_leads(idleadorig,nomelead,fornecedor,tipo,nome,email,telefone,idade,nif,montante,dataentrada,status,datastatus,info,user)" 
        . " VALUES('0','%s',0,'%s','%s','%s','%s',%s,%s,%s,NOW(),8,NOW(),'%s',%s) ",$p->nomelead,$p->tipo,$p->nome,$p->email,$p->telefone,$p->idade,$p->nif,
        $p->montante,$p->outrainfo,$dt->user->id );
$result =mysqli_query($con,$query);

if($result){
$p->id = mysqli_insert_id($con);
//Registar o contacto
$query0 = sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,motivocontacto) "
        . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),%s)",
        $p->id,$dt->user->id ,$p->id,$dt->user->id ,2);
$result0=mysqli_query($con,$query0);
if($result0){
    if(!isset($p->moradarua)){$p->moradarua='';}
    if(!isset($p->moradalocalidade)) {$p->moradalocalidade='';}
    if(!isset($p->moradacp)) {$p->moradacp='';}
    if(!isset($p->filhos)) {$p->filhos=0;}
    if(!isset($p->valorhabitacao)) {$p->valorhabitacao=0;}    
    if(!isset($p->declarada)) {$p->declarada='';}    
    if(!isset($p->prazopretendido)) {$p->prazopretendido=0;}
    if(!isset($p->valorprestacao)) {$p->valorprestacao=0;}
    if(!isset($p->outrainfo)) {$p->outrainfo='';}
    if(!isset($p->finalidade)) {$p->finalidade='';}
    if(!isset($p->profissao2)) {$p->profissao2='';}
    if(!isset($p->idade2)) {$p->idade2='NULL';}    
    if(!isset($p->vencimento2)) {$p->vencimento2='NULL';} 
    
    if(!isset($p->valorhabitacao2)) {$p->valorhabitacao2=0;}
    if(!isset($p->anoiniciohabitacao2)) {$p->anoiniciohabitacao2=1900;}
    if(!isset($p->declarada2)) {$p->declarada2='';}
    if(!isset($p->parentesco2)) {$p->parentesco2='';}
    !isset($p->tipohabitacao2)? $tipohabitacao2='NULL': $tipohabitacao2=$p->tipohabitacao2->id;    
    if(!isset($p->mesmahabitacao)) {$p->mesmahabitacao='';}     
    if(isset($p->mesmahabitacao) && $p->mesmahabitacao=='Sim'){
        $tipohabitacao2='NULL';
        $p->declarada2='';
        $p->anoiniciohabitacao2='NULL';
        $p->valorhabitacao2=0;
    }
    !isset($p->tipocontrato2)? $tipocontrato2='NULL': $tipocontrato2=$p->tipocontrato2->id;   
    if(!isset($p->anoinicio2)) {$p->anoinicio2='NULL';} 
    if(!isset($p->telefone2)) {$p->telefone2='';} 
    if(!isset($p->nif2)) {$p->nif2='NULL';}    
     !isset($p->mesinicio) ? $p->mesinicio=1: null;
     !isset($p->mesinicio2) ? $p->mesinicio2=1: null;
    //Save process
    $query = sprintf("INSERT INTO arq_processo(lead,user,nome,nif,email,telefone,idade,profissao,vencimento,tipocontrato,anoinicio,"
            . " estadocivil,filhos,parentesco2,telefone2,nif2,idade2,profissao2,vencimento2,tipocontrato2,anoinicio2,irs,tipohabitacao,"
            . " valorhabitacao,declarada,anoiniciohabitacao,"
            . " tipohabitacao2,valorhabitacao2,declarada2,anoiniciohabitacao2,mesmahabitacao,"
            . " valorpretendido,prazopretendido,prestacaopretendida,finalidade,outrainfo,moradarua,moradalocalidade,moradacp,"
            . "tipoenviodoc,datainicio,tipocredito, mesinicio, mesinicio2) "
            . " VALUES(%s,%s,'%s',%s,'%s','%s',%s,'%s',%s,%s,%s,%s,%s,'%s','%s',%s,%s,'%s',%s,%s,%s,'%s',%s,%s,'%s',%s,"
            . " %s,%s,'%s',%s,'%s',%s,%s,%s,'%s','%s','%s','%s','%s','%s',NOW(),'%s', %s, %s)",
            $p->id,$dt->user->id,$p->nome,$p->nif,$p->email,$p->telefone,$p->idade,$p->profissao,$p->vencimento,$p->tipocontrato->id,$p->anoinicio,
            $p->estadocivil->id,$p->filhos,$p->parentesco2,$p->telefone2,$p->nif2,$p->idade2,$p->profissao2,$p->vencimento2,$tipocontrato2,$p->anoinicio2,$p->irs,
            $p->tipohabitacao->id,$p->valorhabitacao,$p->declarada,$p->anoiniciohabitacao,
            $tipohabitacao2,$p->valorhabitacao2,$p->declarada2,$p->anoiniciohabitacao2,$p->mesmahabitacao,$p->montante,$p->prazopretendido,
            $p->valorprestacao,$p->finalidade,$p->outrainfo,$p->moradarua,$p->moradalocalidade,$p->moradacp,'',$p->tipocredito, $p->mesinicio, $p->mesinicio2);
    $result = mysqli_query($con,$query);
    if($result){
        //Insere Outros rendimentos
        if(isset($p->or) && sizeof($p->or)>0){
            $ln =1;
            foreach ($p->or as $line){
                if($line->valorrendimento>0){
                    $queryOR = sprintf("INSERT INTO cad_outrosrendimentos(lead,linha,tiporendimento,valorrendimento,periocidade) "
                        . " VALUES(%s,%s,'%s',%s)",$p->id,$ln,$line->tiporendimento,$line->valorrendimento,$line->periocidade);
                    mysqli_query($con,$queryOR);
                    $ln++;
                }
            }
        }
        //Insere Outros Creditos
        if(isset($p->oc) && sizeof($p->oc)>0){
            $ln =1;
            foreach ($p->oc as $line){
                if($line->prestacao>0){
                    !isset($line->valorcredito)?$line->valorcredito=0:null;
                    !isset($line->prestacao)?$line->prestacao=0:null;
                    $queryOC = sprintf("INSERT INTO cad_outroscreditos(lead,linha,tipocredito,valorcredito,prestacao) "
                        . " VALUES(%s,%s,'%s',%s,%s)",$p->id,$ln,$line->tipocredito,$line->valorcredito,$line->prestacao);
                    mysqli_query($con,$queryOC);
                    $ln++;
                }
            }
        }    
        //Registar todos documentos como pedidos
        $resultD = mysqli_query($con, "SELECT id FROM cnf_docnecessaria");
        if($resultD){
            $ln =1;
            while ($row = mysqli_fetch_array($resultD,MYSQLI_NUM)) {
                mysqli_query($con,sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc) "
                    . " VALUES(%s,%s,%s)",$p->id,$ln,$row[0]));
                  $ln++;
            }
        }

        }
    }
    echo $p->id;
}
