<?php

/* 
 * Re-ativar a lead indicad
 *  Decriptar os dados do arq_leads, arq_processo
 */
require_once '../openCon.php';
require_once '../../class/EncryptLeads.php';
require_once '../../class/EncryptProcesso.php';

$json = file_get_contents("php://input");
$dt = json_decode($json);

new EncryptLeads($con, 'decrypt', $dt->lead, $dt->key );
new EncryptProcesso($con, 'decrypt', $dt->lead, $dt->key );

echo $dt->key;

