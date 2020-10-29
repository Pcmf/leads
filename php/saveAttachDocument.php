<?php

/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once '../sisleadsrest/db/DB.php';
require_once 'openCon.php';
require_once '../class/sendEmail.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);




$db = new DB();
$resp = $db->query("INSERT INTO arq_documentacao(lead,linha,nomefx,tipo,fx64)  VALUES(:lead,:linha,:nomefx,:tipo,:fx64)", 
        array(':lead'=>$dt->lead, ':linha'=>$dt->docAnx->linha, ':nomefx'=>$dt->novonome, ':tipo'=>substr($dt->file->filetype,strpos($dt->file->filetype,"/")+1), ':fx64'=>$dt->file->base64));
if(!$resp){

    //Registar o documento anexado
        $queryDOC = sprintf("UPDATE cad_docpedida SET recebido=1,datarecebido=NOW() "
            . " WHERE lead=%s AND tipodoc=%s",$dt->lead,$dt->docAnx->id );
        mysqli_query($con,$queryDOC);

        //ficheiro carregado sem alterar o status...continuam a faltar ficheiros
            echo 'OK';

    } else {
        mysqli_query($con, sprintf("INSERT INTO arq_log(log,user,tipo) VALUES('%s',%s,'I') ",sprintf("INSERT INTO arq_documentacao(lead,linha,nomefx,tipo) "
        . " VALUES(%s,%s,'%s','%s')",$dt->lead,$dt->docAnx->linha,$dt->novonome,substr($dt->file->filetype,strpos($dt->file->filetype,"/")+1)),$dt->lead));
    echo "Erro sAD33! Não foi possivél fazer o upload do ficheiro. Entre em contacto com suporte!\n".$resp;
}


//Função para enviar email a informar a recepção da documentação
function enviarEmailInfo($con,$lead,$image,$sts){
    $msg="";
    $query = sprintf("SELECT L.id,L.user,U.nome AS gestor,U.email AS gemail,U.telefone, P.nome,P.email"
        . " FROM arq_leads L "
        . " INNER JOIN arq_processo P ON P.lead=L.id "
        . " INNER JOIN cad_agenda A ON A.lead=L.id "
        . " INNER JOIN cad_utilizadores U ON U.id=L.user "
        . " WHERE L.id=%s ",$lead);

    $result = mysqli_query($con,$query);
    if($result){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
        
        //Toda documentação recebida e passou para analise
        if($sts==10){
            $assunto="Ref: ".$row['id'].".  Obrigado - Documentação Recebida";

            $msg = "<p>Exmo(a). Sr(a) ".$row['nome'].",</p> "
                    . "<p>Venho por este meio confirmar a receção da sua documentação.<br/>"
                    . "Mais informo que a mesma será encaminhada para análise.</p>"
                        . "<p>Veja o estado do seu processo em:</p>"
                    ."<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>";
               
        }
       
       //Passou para analise com documentação em falta
       if($sts==11){
            $query0 = sprintf("SELECT N.* FROM cad_docpedida D "
                 . " INNER JOIN cnf_docnecessaria N ON N.id=D.tipodoc"
                 . " WHERE D.lead=%s AND D.recebido=0 ", $row['id']);
            $result0 = mysqli_query($con,$query0);
            if($result0){
                $listDocs = array();
                while ($row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC)) {
                    array_push($listDocs, $row0);
                }
            
               //Lista dos documentos pedidos
                $lista = "<ul>";
                foreach($listDocs AS $d){
                    $lista .="<li><u>".$d['nomedoc'].".</u> <span>".$d['descricao']."</span></li>";
                }
                $lista .="</ul>";

                $assunto="Ref: ".$row['id'].".  Obrigado - Documentação em falta";
                $msg = "<p>Exmo(a). Sr(a) ".$row['nome'].",</p> "
                        . "<p>Venho por este meio confirmar a receção da sua documentação.<br/>"
                        . "No entanto verificamos que se encontra em falta o seguinte:</p>"
                        . "<p>".$lista."</p>"
                        . "<p>Mais informo que a mesma será encaminhada para análise.</p>"
                        . "<p>Veja o estado do seu processo em:</p>"
                    ."<h2><a href='https://gestlifes.com/GestLifesAC'>&#9758; Área de Cliente</a></h2>"
                    ."<p> Ao usar o Portal do Cliente para anexar a documentação pedida estará a dar mais rapidez ao seu processo!</p>";                     
            }
        }
        
        //Enviar email
        $result = new sendEmail($con,$row['user'],  $row['gemail'], $row['email'], $assunto, $msg,"", 10, $row['id']);        
        if($result){
            //  Registar no contacto
            mysqli_query($con,sprintf("INSERT INTO cad_registocontacto(lead,user,contactonum,dtcontacto,envsms,envemail,motivocontacto) "
                    . " VALUES(%s,%s,(SELECT COUNT(*)+1 FROM cad_registocontacto R WHERE R.lead=%s AND R.user=%s),NOW(),0,1,3)"
                    ,$row['id'],$row['user'],$row['id'],$row['user']));
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro no envio do email. Por favor contacte suporte!";
        }   
        
    }
}


