<?php

/*
 * Criar ficheiros com os nomes das origens e tipo de credito (CC ou CP) e anexar as as leads correspondentes,
 *   que tenham sido financiadas no dia 
 * 
 *  Os nomes de origem são: 
 *  nomeorigem = [ 'CreditoRapido' ]
 * 
 *  tipocredito = [ CC, CP]
 * 
 * 
 * Parametros a incluir no fx
 * 
 *   	"Google Click ID" - é para ter o valor do gclid
 * 	"Conversion Name" – [Gestlifes Financed Lead Consolidated] ou [Gestlifes Financed Lead Personal]
 * 	"Conversion Time" - data e hora de entrada em sistema
 * 	"Conversion Value" - vazio
 * 	"Conversion Currency" – vazio
 * 
 *      O nome do ficheiro  será:  [nomeorigem]_[tipocredito].csv
 * 
 *  Exemplo
 * 
 *  Parameters:TimeZone=Europe/Lisbon
 *  Google Click ID,"Conversion Name","Conversion Time","Conversion Value","Conversion Currency"
 *  EAIaIQobChMI1J7csZSb3gIVbCHTCh08ZQubEAEYASAAEgLAOPD_BwE,"Gestlifes Financed Lead Consolidated","2018-10-23 00:08:54",,
 *  Cj0KCQjwjbveBRDVARIsAKxH7vlwInkJYV2_VUL8qATwz9afs7QhW6YzTb4tgf5P_HukRS9nwThk1MoaApanEALw_wcB,"Gestlifes Financed Lead Consolidated","2018-10-23 22:02:53",,
 */

require_once '../../php/openCon.php';

$origens = ['CreditoRapido' ];

//Criar ficheiros para cada origem
foreach ($origens as $value) {
    criarFicheiro($con, $value);
}


function criarFicheiro($con, $origem) {
    //Criar ficheiro PERSONAL
    $filename = "../../doc/".$origem."_CP.csv";
    $handle = fopen($filename, 'w ', 1);


    if ($handle) {

        fwrite($handle, 'Parameters:TimeZone=Europe/Lisbon' . "\n");
        fwrite($handle, 'Google Click ID,"Conversion Name","Conversion Time","Conversion Value","Conversion Currency"' . "\n");

//Obter dados
        $query = sprintf("SELECT L.id,L.gcid, L.nomelead, L.dataentrada FROM arq_leads L, F.montante "
                . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE L.status IN (17,23,24) AND DATE(F.datafinanciado)=DATE(NOW()) AND F.tipocredito='CP' "
                . " AND L.nomelead='%s' AND L.gcid IS NOT NULL AND L.gcid<>'' ", $origem);
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

                //inserir os dados no ficheiro
                fwrite($handle, $row['gcid'] . ',"Gestlifes Financed Lead Personal",' . $row['dataentrada'] . ',' .$row['montante'].',n');
            }
        }


//Fechar e guardar ficheiro
        fclose($handle);
    }


//Criar ficheiro CONSOLIDATED
    $filename = "../../doc/".$origem."_CC.csv";
    $handle = fopen($filename, 'w ');


    if ($handle) {

        fwrite($handle, 'Parameters:TimeZone=Europe/Lisbon' . "\n");
        fwrite($handle, 'Google Click ID,"Conversion Name","Conversion Time","Conversion Value","Conversion Currency"' . "\n");

//Obter dados
        $query = sprintf("SELECT L.id,L.gcid, L.nomelead, L.dataentrada FROM arq_leads L "
                . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE L.status IN (17,23,24) AND DATE(F.datafinanciado)=DATE(NOW()) AND F.tipocredito='CC' "
                . " AND L.fornecedor='%s' AND L.gcid IS NOT NULL AND L.gcid<>'' ",$origem);
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

                //inserir os dados no ficheiro
                fwrite($handle, $row['gcid'] . ',"Gestlifes Financed Lead Consolidated",' . $row['dataentrada'] . ',' .$row['montante'].',n');
            }
        }


//Fechar e guardar ficheiro
        fclose($handle);
    }

    return;
}
