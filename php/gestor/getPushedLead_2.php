<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../openCon.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);
$user = $dt->user;


// Selecionar activa se existir
$res = mysqli_query($con, sprintf("SELECT id FROM arq_leads WHERE status=2 AND user=%s", $user->id));
if ($res) {
    $rowA = mysqli_fetch_array($res, MYSQLI_ASSOC);
    if ($rowA['id']) {
        echo $rowA['id'];
        return;
    }
}

// Selecionar prioritarias com documentação
// Apenas se o utilizador estiver presente
if ($user->presenca) {
    $query = sprintf("SELECT F.lead, L.status FROM cad_fila F "
            . " INNER JOIN arq_leads L ON L.id=F.lead"
            . " WHERE F.status=1 AND  L.status IN(36,39) AND L.user=%s ORDER BY L.id LIMIT 1", $user->id);
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        if ($row['lead']) {
            updateLeadToActive($con, $row['lead'], $user, $row['status']);
            echo $row['lead'];
            return;
        }
    }
    // Com speedup
    $query = sprintf("SELECT F.lead, L.status FROM `cad_fila` F "
            . " INNER JOIN arq_leads L ON L.id=F.lead "
            . " INNER JOIN cad_speedup S ON S.lead=F.lead "
            . " WHERE F.status=1 AND L.status IN(37,38) AND S.visto=0 AND L.user=%s "
            . " ORDER BY F.lead LIMIT 1", $user->id);
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        if ($row['lead']) {
            updateLeadToActive($con, $row['lead'], $user, $row['status']);
            echo $row['lead'];
            return;
        }
    }

    $agendada = getAgendada($con, $user);
    $nova= getNova($con, $user);

    if ($dt->turn == 'A' && $agendada) {
        updateLeadToActive($con, $agendada['id'], $user, $agendada['status']);
        echo $agendada['id'];
        return;
    } elseif ($dt->turn == 'A' && $nova) {
        updateLeadToActive($con, $nova['id'], $user, $nova['status']);
        echo $nova['id'];
        return;
    }

    if ($dt->turn == 'N' && $nova) {
        updateLeadToActive($con, $nova['id'], $user, $nova['status']);
        echo $nova['id'];
        return;
    } elseif ($dt->turn == 'N' && $agendada) {
        updateLeadToActive($con, $agendada['id'], $user, $agendada['status']);
        echo $agendada['id'];
        return;
    }
    echo 0;
    return;
} else {
    // se o utilizador não está presente apenas permite puxar agendadas
    $resp =  getAgendada($con, $user);
    if ($resp) {
        echo $resp['id'];
        return;
    }
    echo 0;
    return;
}


//obter agendada 
function getAgendada($con, $user) {
    $query = sprintf("SELECT L.id, L.status "
            . " FROM cad_agenda A "
            . " INNER JOIN arq_leads L ON A.lead=L.id "
            . " LEFT JOIN cad_agendadoc D ON D.lead=A.lead "
            . " WHERE L.status IN (6,7, 36, 37, 38)  AND L.user=%s "
            . " AND ((  A.tipoagenda<>3 AND A.status=1 AND (( A.agendadata=CURRENT_DATE"
            . " AND A.agendahora<CURTIME() ) OR A.agendadata< CURRENT_DATE )) "
            . " OR D.ativa=1) "
            . " ORDER BY A.agendadata ASC, A.agendahora ASC LIMIT 1", $user->id);
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return $row;
    }
    return null;
}

// obter uma nova
function getNova($con, $user) {
    $result = mysqli_query($con, sprintf("SELECT L.id, L.status "
                    . " FROM cad_fila F "
                    . " INNER JOIN arq_leads L ON L.id=F.lead "
                    . " WHERE F.status=1 AND (L.status= 1 OR (L.status IN(1, 37, 38, 40) AND L.user=%s))  ORDER BY F.lead LIMIT 1", $user->id));
    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return $row;
    }
    return null;
}

function updateLeadToActive($con, $lead, $user, $status) {
    if ($status == 1) {
        mysqli_query($con, sprintf("UPDATE arq_leads SET status=2, datastatus=NOW(), user=%s "
                        . " WHERE id=%s", $user->id, $lead));
    }
    // Remove da fila
    mysqli_query($con, sprintf("UPDATE cad_fila SET status=0 "
                    . " WHERE lead=%s", $lead));
}
