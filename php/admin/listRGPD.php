<?php

/* 
 * Obter as leads que estão encriptadas e devolver decriptadas
 */

require_once '../openCon.php';
require_once '../../class/Encrypt_Decrypt.php';


$userId = file_get_contents("php://input");

$resp = array();
//Verificar se o utilizador tem permissões para DataProtector - se tiver rgpdkey
$result0 = mysqli_query($con, sprintf("SELECT * FROM cad_utilizadores WHERE id=%s", $userId));
if($result0){
    $row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
    
        if($row0['rgpdkey']){
            //Pode prosseguir
            $query = sprintf("SELECT L.id, L.nome, L.nif, L.telefone, L.email, E.rgpdkey, E.data "
                    . " FROM cad_encriptleads E "
                    . " INNER JOIN arq_leads L ON L.id=E.lead");
            $result = mysqli_query($con, $query);
            if($result){
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    $temp = array();
                    $temp['lead'] = $row['id'];
                    $decript = new Encrypt_Decrypt('decrypt',$row['nome'], $row['rgpdkey']);
                    $temp['nome'] = $decript->getReturnString();
                    $decript = new Encrypt_Decrypt('decrypt',$row['nif'], $row['rgpdkey']);
                    $temp['nif'] = $decript->getReturnString();
                    $decript = new Encrypt_Decrypt('decrypt',$row['telefone'], $row['rgpdkey']);
                    $temp['telefone'] = $decript->getReturnString();
                    $decript = new Encrypt_Decrypt('decrypt',$row['email'], $row['rgpdkey']);
                    $temp['email'] = $decript->getReturnString(); 
                    $temp['rgpdkey'] = $row['rgpdkey'];
                    $temp['dataRgpd'] = $row['data'];
                    array_push($resp, $temp);
                }
                
                echo json_encode($resp);
            }
            
            
        } else {
            echo "Não tem permissões para prosseguir";
        }
        
        
    
}