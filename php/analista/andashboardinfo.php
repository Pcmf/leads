<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
$json = file_get_contents("php://input");

$user = json_decode($json);
$resp = array();
//Count NEW - paraAnalise - 10,11
$query = "SELECT count(distinct(id)) from arq_leads WHERE status IN (10,11,20)";
$result = mysqli_query($con,$query);
$resp['paraAnalise'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['paraAnalise'] = $row[0];
}

// Contar as que estão com documentação OK
$query = sprintf("SELECT count(id) from arq_leads WHERE status=22 and analista=%s", $user->id);
$result = mysqli_query($con,$query);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['docOk'] = $row[0];
} else {
    $resp['docOk'] =0;
}


//Verificar se existe alguma LEAD em analise se houver retorna o numero
$result= mysqli_query($con,sprintf("SELECT id from arq_leads where status=12 and analista=%s",$user->id));
if($result){
    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
    $resp['ativa'] = $row['id'];
}

//Count emAnalise/Pendentes - 13
$query = "SELECT count(*) from arq_leads WHERE status IN(12,13,20,21,22) AND analista=".$user->id;
$result = mysqli_query($con,$query);
$resp['emAnalise'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['emAnalise'] = $row[0];
}
//List emAnalise/Pendentes - 13
$query = sprintf("SELECT P.lead,P.nome,P.valorpretendido,DATE(L.datastatus) AS dtstatus,L.status, S.nome AS stsnome, S.descricao "
        . " FROM arq_processo P "
        . " INNER JOIN arq_leads L ON L.id=P.lead "        
        . " INNER JOIN cnf_statuslead S ON L.status = S.id "
        . " WHERE L.status IN(12,13,20,21,22) AND L.analista=%s "
        . " ORDER BY L.status,L.datastatus ASC LIMIT 3 ",$user->id);
$result = mysqli_query($con,$query);
$listaEmAnalise = array();
if($result){
    while ($row1 = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        if($row1['status']==13){
            $query0 = sprintf("SELECT S.status FROM cad_financiamentos F "
                . " INNER JOIN cnf_stsfinanciamentos S ON S.id=F.status "
                . " WHERE F.lead=%s ORDER BY F.datastatus DESC LIMIT 1",$row1['lead']);
            $result0 = mysqli_query($con,$query0);
            if($result0){
                $row0 = mysqli_fetch_array($result0,MYSQLI_ASSOC);
                $row1['stsnome']= $row0['status'];
            }
        }
        array_push($listaEmAnalise, $row1);
    }
    $resp['listaEmAnalise']= $listaEmAnalise;
}


//Count Aprovados - 16
$query = "SELECT count(*) from arq_leads  WHERE status=16 AND analista=".$user->id;
$result = mysqli_query($con,$query);
$resp['aprovados'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['aprovados'] = $row[0];
}
//List Resultados - 16
$query = sprintf("SELECT P.lead,A.nome,F.*,DATE(F.dataaprovado) AS dtaprovado,"
        . " DATE(F.dtcontratocliente) AS dtcliente,DATE(F.dtcontratoparceiro) AS dtparceiro  "
        . " FROM arq_processo P"
        . " INNER JOIN arq_leads L ON L.id=P.lead "
        . " INNER JOIN cad_financiamentos F ON P.lead=F.lead "
        . " INNER JOIN cad_parceiros A ON F.parceiro=A.id "
        . " WHERE L.status=16 AND L.analista=%s AND F.status=6"
        . " ORDER BY L.datastatus LIMIT 3",$user->id);
$result = mysqli_query($con,$query);
$listaAprovados = array();
if($result){
    while ($row1 = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        array_push($listaAprovados, $row1);
    }
    $resp['listaAprovados']= $listaAprovados;
}


//Count + valor Financiados - 17 + 24 se o mes da datafinanciamento for o mesmo da seleção
$query = "SELECT count(*), sum(F.montante)  from arq_leads  L "
        . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
        . " WHERE ((L.status=17 AND F.status=7  AND MONTH(L.datastatus)=MONTH(NOW()) AND YEAR(L.datastatus)=YEAR(NOW())) "
        . " OR (L.status=24 AND F.status=7 AND MONTH(F.datafinanciado)=MONTH(NOW()) AND YEAR(F.datafinanciado )=YEAR(NOW())) "
         . " OR (L.status=25 AND F.status=12 AND MONTH(F.datastatus)=MONTH(NOW()) AND YEAR(F.datastatus )=YEAR(NOW())))"
        . " AND analista= ".$user->id;
$result = mysqli_query($con,$query);
$resp['financiados'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['financiados'] = $row[0];
    $resp['valorFinanciado'] = $row[1];

}
//Count Financiados ACP - 23 
$query = "SELECT count(*) from arq_leads  WHERE status=23 "
        . " AND analista= ".$user->id;
$result = mysqli_query($con,$query);
$resp['financiadosACP'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['financiadosACP'] = $row[0];
}
//Check date of status has more then 10 days
$query = "SELECT count(*) FROM arq_leads WHERE DATEDIFF(NOW(), DATE(datastatus))>=10 AND status=23 AND analista= ".$user->id;
$result = mysqli_query($con,$query);
$resp['alertaACP'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['alertaACP'] = $row[0];
}
//Count FinanciadosRCP - 35 
$query = "SELECT count(*) from arq_leads  WHERE status=35 "
        . " AND analista= ".$user->id;
$result = mysqli_query($con,$query);
$resp['financiadosRCP'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['financiadosRCP'] = $row[0];
}


//Count Recusados na analise ou no final do financiamento - 14,19
$query = "SELECT count(*) from arq_leads  WHERE status IN(14,15,18,19) "
        . " AND MONTH(datastatus)>=(MONTH(NOW())-1) AND YEAR(datastatus)=YEAR(NOW())"
        . " AND analista=".$user->id;
$result = mysqli_query($con,$query);
$resp['recusados'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['recusados'] = $row[0];
}

// Suspensos - 41
$query = "SELECT count(*) from arq_leads  WHERE status=41 "
        . " AND analista=".$user->id;
$result = mysqli_query($con,$query);
$resp['suspensos'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['suspensos'] = $row[0];
}

//MURAL - Conversas
$query = sprintf("SELECT M.*, U.nome AS userorigem, U1.nome AS userdestino FROM mural M "
        . " INNER JOIN cad_utilizadores U ON U.id=M.origem "
        . " INNER JOIN cad_utilizadores U1 ON U1.id=M.destino "
        . " WHERE  (M.destino=%s OR M.origem=%s) AND (DATEDIFF(DATE(NOW()),DATE(M.dataenvio))<5) ORDER BY M.dataenvio ", $user->id, $user->id);
$temp = array();
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        if($row['origem'] == $user->id){
            $row['sentido'] = 'msg-out';
        } else {
           $row['sentido'] = 'msg-in';
        }
        array_push($temp, $row);
    }
    $resp['conversas'] = $temp;
}

echo json_encode($resp);
