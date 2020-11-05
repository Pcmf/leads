<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);



$resp = array();
//Novas
//$result = mysqli_query($con, sprintf("SELECT COUNT(*) FROM arq_leads L "
//        . " LEFT JOIN cad_agenda A ON A.lead=L.id "
//        . " WHERE L.status=1 OR (L.status=2 AND L.user=%s)", $dt->id));
$result = mysqli_query($con, "SELECT COUNT(*) FROM cad_fila WHERE status=1");

if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['novas'] = $row[0];
} else {
    $resp['novas']=0;
}
//Ativa
$result = mysqli_query($con, "SELECT COUNT(*) FROM arq_leads WHERE status=2 AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['ativa'] = $row[0];
}

//Atribuidas 
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads WHERE status IN (2,6,7,8) AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['ativas'] = $row[0];
}
//Agendadas com data ultrapassada
$result = mysqli_query($con, "SELECT COUNT(*) FROM arq_leads L INNER JOIN cad_agenda A ON L.id=A.lead "
        . " WHERE L.status IN (6,7, 36, 37, 38) "
        . " AND A.tipoagenda<>3 AND A.status=1 AND ((A.agendaData=DATE(NOW()) AND A.agendaHora<CURTIME())"
        . " OR A.agendadata<DATE(NOW())) AND L.user=".$dt->id." ORDER BY A.agendadata ASC, A.agendahora ASC");
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['agendaAtiva'] = $row[0];
}
// AgendaDoc - agendadas para chamada
$result = mysqli_query($con, sprintf("SELECT COUNT(*) FROM cad_agendadoc D"
        . " INNER JOIN arq_leads L ON L.id=D.lead "
        . " WHERE  L.user=%s AND L.status =8 AND D.ativa=1 AND D.data<=DATE(NOW())  ORDER BY D.lead ",$dt->id)); 
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['agendaDoc'] = $row[0];
}
//Agendadas 
$resp['agendadas'] =0;
$result = mysqli_query($con,"SELECT COUNT(*) FROM cad_agenda WHERE status=1 AND tipoagenda<>3 AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['agendadas'] = $row[0];
}

//Aguardam Documentação
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads L"
        . " INNER JOIN cad_agenda A ON A.lead = L.id"
        . " WHERE L.status=8 AND A.status=1 AND A.tipoagenda=3 AND DATE(A.agendadata)>=DATE(NOW()) AND L.user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['aguardaDoc'] = $row[0];
}
//Aguardam Documentação - Do ANALISTA
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads L"
        . " WHERE L.status=21 AND L.user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['agDocAnalist'] = $row[0];
}

//Documentação Atrasada
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads L"
        . " INNER JOIN cad_agenda A ON A.lead = L.id"
        . " WHERE L.status=8 AND A.status=1 AND A.tipoagenda=3 AND DATE(A.agendadata)<DATE(NOW()) AND L.user=".$dt->id);

if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['atrasadaDoc'] = $row[0];
}

//Documentação Atrasada com algum documento recebido
$result = mysqli_query($con,"SELECT COUNT(DISTINCT(L.id)) FROM arq_leads L"
        . " INNER JOIN cad_agenda A ON A.lead = L.id "
        . " INNER JOIN arq_documentacao D ON D.lead=L.id"
        . " WHERE L.status=8 AND A.status=1 AND A.tipoagenda=3  AND DATE(A.agendadata)<DATE(NOW()) AND L.user=".$dt->id);

if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['atrasadaDocParcial'] = $row[0];
}

//CLIENTE
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads L"
        . " WHERE L.status IN(37,38)  AND L.user=".$dt->id);
$resp['portalClient'] = 0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['portalClient'] = $row[0];
} 
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads L"
        . " WHERE L.status IN(36)  AND L.user=".$dt->id);
$resp['portalClientDocRec'] = 0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['portalClientDocRec'] = $row[0];
} 
//BPS-DOCS
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads L"
        . " WHERE L.status=39  AND L.user=".$dt->id);
$resp['bpsDocs'] = 0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['bpsDocs'] = $row[0];
} 

//Anuladas 
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads WHERE status IN (3,4,5,9) AND DATEDIFF(NOW(), datastatus) < 60  AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['anuladas'] = $row[0];
}
//Anulados pelo Gestor
$resp['anuladasGestor'] = 0;
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads WHERE status=4 AND DATEDIFF(NOW(), datastatus) < 60 AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['anuladasGestor'] = $row[0];
}
//Anulados por não atender
$resp['anuladasNaoAtende'] = 0;
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads WHERE status=5 AND DATEDIFF(NOW(), datastatus) < 60 AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['anuladasNaoAtende'] = $row[0];
}
//Anulados pelo Gestor
$resp['anuladasFaltaDoc'] = 0;
$result = mysqli_query($con,"SELECT COUNT(*) FROM arq_leads WHERE status=9 AND DATEDIFF(NOW(), datastatus) < 60 AND user=".$dt->id);
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['anuladasFaltaDoc'] = $row[0];
}

//Hoje
//Tentativas de contacto
//$result = mysqli_query($con,sprintf("SELECT COUNT(*) FROM cad_registocontacto WHERE user=%s AND DATE(dtcontacto)=DATE(NOW()) AND motivocontacto=0",$dt->id));
$result = mysqli_query($con,sprintf("SELECT COUNT(*) FROM arq_histprocess WHERE user=%s "
        . " AND DATE(data)=DATE(NOW()) AND status=2",$dt->id));
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['tentativas'] = $row[0];
}
//Contactados
$result = mysqli_query($con,sprintf("SELECT COUNT(*) FROM cad_registocontacto WHERE user=%s AND DATE(dtcontacto)=DATE(NOW()) AND motivocontacto IN (2,3,6)",$dt->id));
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['contactados'] = $row[0];
}
//Sucesso - passaram para analise
$result = mysqli_query($con,sprintf("SELECT COUNT(*) FROM arq_histprocess WHERE user=%s AND DATE(data)=DATE(NOW()) AND status IN(10,11)",$dt->id));
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['sucesso'] = $row[0];
}

//Count + valor Financiados - 17 + 24  se o mes da datafinanciamento for o mesmo da seleção - 25
$query = "SELECT count(DISTINCT(L.id)), sum(F.montante)  from arq_leads  L "
        . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
        . " WHERE ( (L.status=17 AND F.status=7  AND MONTH(F.datafinanciado)=MONTH(NOW()) AND YEAR(F.datafinanciado)=YEAR(NOW()) ) "
        . " OR (L.status IN(23,24) AND F.status=7 AND MONTH(F.datafinanciado)=MONTH(NOW()) AND YEAR(F.datafinanciado )=YEAR(NOW()) ) )"
       //  . " OR (L.status=25 AND F.status=12 AND MONTH(F.datastatus)=MONTH(NOW()) AND YEAR(F.datastatus )=YEAR(NOW()) ) )"
        . " AND user= ".$dt->id;
$result = mysqli_query($con,$query);
$resp['financiados'] =0;
if($result){
    $row = mysqli_fetch_array($result,MYSQLI_NUM);
    $resp['financiados'] = $row[0];
    $resp['valorFinanciado'] = $row[1];

}



//MURAL - Conversas

$query = sprintf("SELECT M.*, U.nome AS userorigem, U1.nome AS userdestino FROM mural M "
        . " INNER JOIN cad_utilizadores U ON U.id=M.origem "
        . " INNER JOIN cad_utilizadores U1 ON U1.id=M.destino "
        . " WHERE  (M.destino=%s OR M.origem=%s) AND (DATEDIFF(DATE(NOW()),DATE(M.dataenvio))<5) ORDER BY M.dataenvio ", $dt->id, $dt->id);
$temp = array();
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        if($row['origem'] == $dt->id){
            $row['sentido'] = 'msg-out';
        } else {
           $row['sentido'] = 'msg-in';
        }
        array_push($temp, $row);
    }
    $resp['conversas'] = $temp;
}


// SPEED UP
$query = sprintf("SELECT * FROM cad_speedup WHERE visto = 0");$temp = array();
$result = mysqli_query($con, $query);
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($temp, $row);
    }
    $resp['speedup'] = $temp;
}

echo json_encode($resp);