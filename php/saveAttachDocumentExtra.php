<?php
/* 
 * inserir os fx base64 no arq_documentacao
 * atualizar os ficheiros recebidos 
 * alterar o status da lead para 10 ou 11
 */
require_once 'openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);
echo json_encode($dt->doc);
$result0 = mysqli_query($con, sprintf("SELECT MAX(linha)+1 FROM cad_docpedida WHERE lead=%s",$dt->lead));
if($result0){
    $row = mysqli_fetch_array($result0,MYSQLI_NUM);
    if($row[0]){
        $linha= $row[0];
        
        $query = sprintf("INSERT INTO cad_docpedida(lead,linha,tipodoc,recebido,datarecebido) "
             . " VALUES(%s,%s,%s,0,NOW())"
             ,$dt->lead, $linha, $dt->doc->tipodoc);
        mysqli_query($con,$query); 
        echo $linha;
    } 
}


