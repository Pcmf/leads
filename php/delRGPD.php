<?php

/* 
 * Eliminar todos os dados pessoais de uma lead.
 */

require_once 'openCon.php';
require_once '../class/EncryptProcesso.php';
require_once '../class/DeleteRegistoChamadas.php';
require_once '../class/DeleteLogEmail.php';
require_once '../class/EncryptLeads.php';
require_once '../class/Encrypt_Decrypt.php';

$json = file_get_contents("php://input");

$dt = json_decode($json);

//Obter da BD a chave para encriptação
$result = mysqli_query($con, "SELECT rgpdkey FROM cad_utilizadores WHERE rgpdkey IS NOT NULL");
if( $result){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $rgpdkey = $row['rgpdkey'];
    
    //Obter os dados pessoais: telefone, nif, email a partir de uma lead
    $result0 = mysqli_query($con, sprintf("SELECT telefone, nif, email FROM arq_leads WHERE id= %s", $dt->lead));
    if($result0){
        $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
    }
    
    //Processar a encriptação para todas as leads que contenham algum dos dados pessoais 
    $query =  sprintf("SELECT id FROM arq_leads WHERE telefone LIKE '%s' OR nif='%s' OR email LIKE '%s' "
            , $row0['telefone'], $row0['nif'], $row0['email'] );
   // echo $query;
    $result1 = mysqli_query($con,$query);
    
    if($result1){
        while ($row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
            new EncryptProcesso($con,'encrypt', $row1['id'], $rgpdkey);
            new DeleteRegistoChamadas($con,  $row0['telefone']);
            new DeleteLogEmail($con, $row0['email']);
            new EncryptLeads($con, 'encrypt', $row1['id'], $rgpdkey);
            mysqli_query($con, sprintf("INSERT INTO cad_encriptleads(lead,user,rgpdkey) VALUES(%s,%s,'%s') ", $row1['id'], $dt->user, $rgpdkey));
        }
        echo "Processo de encriptação concluido!";
    }
    //Limpar o accesso do portal - cad_clientes
    mysqli_query($con, sprintf("DELETE FROM cad_clientes WHERE nif=%s OR email LIKE '%s' OR lead=%s"
            , $row0['nif'], $row0['email'], $dt->lead));
    
} else {
    echo "Erro! Não é possivel completar a acção por falta de chave de encriptação.";
}

