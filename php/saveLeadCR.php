<?php

require_once "openCon.php";
require_once "passwordHash.php";
require_once '../class/PortalAccessEmail.php';


$data = file_get_contents("php://input");
$dt = json_decode($data);
$resp = (object) array();
//Check if user is valid
if (isset($dt->supplier)) {
    $supplier = strip_tags(trim($dt->supplier));

//mysqli_query($con, sprintf('INSERT INTO arq_logerroapi(query) VALUES("%s")', $dt->id.'  '.$dt->email));
//Check if password is valid
    if (isset($dt->password)) {
        $password = strip_tags(trim($dt->password));


        $query = "SELECT * FROM cad_fornecedorleads where nome='" . $supplier . "'";
        $result = mysqli_query($con, $query);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_BOTH);
            if ($row[0]) {
                //Check if password is valid
                if (passwordHash::check_password($row['password'], $dt->password)) {
                    //if($row['password']==$dt->password){
                    //sanitize LEAD data
                    //Lead name
                    !isset($dt->leadName) ? $nomeLead ="" : $nomeLead = strip_tags(trim($dt->leadName));
                    //Client name
                    !isset($dt->nome) ? $nome="" : $nome = ucwords(mb_strtolower(strip_tags(trim($dt->nome)), 'UTF-8'));
                    //Client email
                    !isset($dt->email)? $email="" : $email = strip_tags(trim($dt->email));
                    //Lead telefone
                    !isset($dt->telefone)? $telefone="" : $telefone = strip_tags(trim($dt->telefone));
                    //Lead Montante
                    (isset($dt->montante) && $dt->montante > 0) ? $montante = strip_tags(trim($dt->montante)) : $montante = 0;

                    !isset($dt->id) ? $leadId=0 : $leadId = strip_tags(trim($dt->id));

                    isset($dt->gclid) ? $gcid = strip_tags($dt->gclid) : $gcid = NULL;
                    isset($dt->tipo) && $dt->tipo == 'Consolidado' ?    $tipoLead = 'CC' : $tipoLead = 'CP'; 
                    $idade = 0;
                    $nif = 0;

                    //Verificar se já existe lead com mesmo idorigem, fornecedor e nome de lead
                    $query0 = sprintf("SELECT count(*) FROM arq_leads WHERE idleadorig=%s AND nomelead LIKE '%s' AND fornecedor=%s", $leadId, $nomeLead, $row['id']);
                    $result0 = mysqli_query($con, $query0);
                    if ($result0) {
                        $row0 = mysqli_fetch_array($result0, MYSQLI_NUM);
                        if ($row0[0] == 0) {
                            //Save to DB
                            $query = sprintf("INSERT INTO arq_leads(idleadorig,nomelead,fornecedor,tipo,nome,email,telefone,idade,nif,montante,status,gcid) "
                                    . " VALUES('%s','%s','%s','%s','%s','%s',%s,%s,%s,%s,1, '%s')",
                                    $leadId, $nomeLead, $row['id'], $tipoLead, $nome, $email, $telefone, $idade, $nif, $montante, $gcid);
                            $result = mysqli_query($con, $query);
                            $lead = mysqli_insert_id($con);
                            if ($result) {
                                mysqli_query($con, sprintf("INSERT INTO arq_processo(lead, user, nome, nif, email, telefone, idade, valorpretendido) "
                                        . " VALUES(%s, 0, '%s', '%s', '%s', '%s', %s, %s)",
                                        $lead, $nome, $nif, $email, $telefone, $idade, $montante));
                                new PortalAccessEmail($con, $lead, $nome, $email, $nomeLead);
                                $resp->error = false;
                                $resp->info = "Lead inserida";
                                echo json_encode($resp);
                            } else {
                                mysqli_query($con, sprintf("INSERT INTO cad_apierrorlog (idoriginal, fornecedor, nome, email, telefone, montante, erro, query)"
                                                . "VALUES('%s', %s, '%s', '%s', '%s', %s, '%s', '%s')",
                                                $leadId, $row['id'], $nome, $email, $telefone, $montante, 'Insert to DB', $query));
                                $resp->error = true;
                                $resp->info = "Lead nao inserida";
                                echo json_encode($resp);
                            }
                        } else {
                            mysqli_query($con, sprintf("INSERT INTO cad_apierrorlog (idoriginal, fornecedor, nome, email, telefone, montante, erro) "
                                            . " VALUES('%s', %s, '%s', '%s', '%s', %s, '%s')",
                                            $leadId, $row['id'], $nome, $email, $telefone, $montante, 'Id Original duplicado'));
                            $resp->error = true;
                            $resp->info = "Lead repetida";
                            echo json_encode($resp);
                        }
                    }
                } else {
                    mysqli_query($con, sprintf("INSERT INTO cad_apierrorlog (idoriginal,  erro) VALUES('%s', '%s')", strip_tags(trim($dt->id)), 'Password Errada'));
                    $resp->error = true;
                    $resp->info = "Password errada";
                    echo json_encode($resp);
                }
            } else {
                mysqli_query($con, sprintf("INSERT INTO cad_apierrorlog (idoriginal,  erro) VALUES('%s', '%s')", strip_tags(trim($dt->id)), 'Fornecedor não registado'));
                $resp->error = true;
                $resp->info = "Fornecedor nao registado";
                echo json_encode($resp);
            }
        }
    } else {
        mysqli_query($con, sprintf("INSERT INTO cad_apierrorlog (idoriginal,  erro) VALUES('%s', '%s')", strip_tags(trim($dt->id)), 'Falta a senha de acesso.'));
        $resp->error = true;
        $resp->info = "Falta senha de acesso";
        echo json_encode($resp);
    }
} else {
    $resp->error = true;
    $resp->info = "Falta nome do fornecedor";
    echo json_encode($resp);
}
