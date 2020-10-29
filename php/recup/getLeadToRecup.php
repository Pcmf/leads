<?php

/*
 * Este PHP recebe como parametro o user Id e retorna uma LEAD
 * 
 * A seleção da LEAD vai depender da Agenda. Se houver alguma lead agendada para 
 * o periodo em que se encontra vai escolher uma das agendadas senão escolhe uma nova,
 *  que tenha a data mais antiga
 */
require_once '../openCon.php';
$data = file_get_contents("php://input");
$dt = json_decode($data);

    $result102 = mysqli_query($con, sprintf("SELECT id FROM arq_leads WHERE status=102 AND user=%s ORDER BY id, datastatus ASC LIMIT 1", $dt->user));
    if ($result102) {
        $rowN = mysqli_fetch_array($result102, MYSQLI_ASSOC);
        if($rowN['id']>0) {
            echo $rowN['id'];
            return;
        }
    }

//Seleção sem ter uma LEAD definida
//Verificar se está definido turn N e se há novas. se não houver muda para turn A
if ($dt->turn == 'N') {
            $result = mysqli_query($con, sprintf("SELECT L.id FROM arq_recuperacao R INNER JOIN arq_leads  L ON L.id=R.lead "
                    . " WHERE R.statusrec =1  ORDER BY R.data ASC LIMIT 1"));
            if($result){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                if($row['id']>0) {
                    mysqli_query($con, sprintf("UPDATE arq_recuperacao SET statusrec=0 WHERE lead = %s", $row['id']));
                    mysqli_query($con, sprintf("UPDATE arq_leads SET status=102, user=%s WHERE id = %s", $dt->user, $row['id']));
                    echo $row['id'];
                    return;
                } else {
                    $dt->turn = 'A';
                }
            }
}

if ($dt->turn == 'A') {
    //Verificar se há LEADS agendadas para a data/hora atual se sim puxa a que estever como ativa e com data/hora agendada mais antiga
    $queryA = sprintf("SELECT L.id "
            . " FROM cad_agenda A "
            . " INNER JOIN arq_leads L ON A.lead=L.id "
            . " LEFT JOIN cad_agendadoc D ON D.lead=A.lead "
            . " WHERE L.status IN (106, 107, 108, 37, 38)  "
            . " AND ((  A.tipoagenda<>3 AND A.status=1 AND (( A.agendadata=CURRENT_DATE"
            . " AND A.agendahora<CURTIME() ) OR A.agendadata< CURRENT_DATE )) OR D.ativa=1) "
            . " ORDER BY A.agendadata ASC, A.agendahora ASC LIMIT 1");

    $resultA = mysqli_query($con, $queryA);
    if ($resultA) {
        $rowA = mysqli_fetch_array($resultA, MYSQLI_ASSOC);
        if ($rowA['id'] > 0) { //Existe Lead agendada, 
            // limpar da lista de fazer chamadas
            mysqli_query($con, sprintf("UPDATE cad_agendadoc SET ativa=0 WHERE lead=%s", $rowA['id'] ));
            echo $rowA['id'];
            return;
        }
        
    }
}

$result = mysqli_query($con, "SELECT lead FROM arq_recuperacao WHERE statusrec = 1 ORDER BY lead, data ASC LIMIT 1");
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_query($con, sprintf("UPDATE arq_recuperacao SET statusrec=0 WHERE lead = %s", $row['lead']));
    mysqli_query($con, sprintf("UPDATE arq_leads SET status=102, user=%s WHERE id = %s", $dt->user, $row['lead']));
    echo $row['lead'];
}