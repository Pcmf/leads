<?php
require_once 'db/DB.php';
require_once 'Leads.php';

/**
 * Description of Report
 *
 * @author pedro
 */
class Report {

    private $db;

    public function __construct() {
        $thisdb = new DB();
    }

    /**
     * 
     * @param type $fornecedor
     * @param type $data1
     * @param type $data2
     * @return type
     */
    public function getReport($fornecedor, $data1, $data2) {
        return $this->report($fornecedor, $data1, $data2);
    }

    public function getReportList($fornecedor, $data1, $data2, $tipo, $motivo) {
        return $this->reportList($fornecedor, $data1, $data2, $tipo, $motivo);
    }

    /**
     * 
     * @param type $forn
     * @param string $data1
     * @param string $data2
     * @return type
     */
    private function report($forn, $data1, $data2) {
        require_once '../php/openCon.php';

        $data1 = substr($data1, 0, 4) . '-' . substr($data1, 4, 2) . '-' . substr($data1, 6, 2);
        $data2 = substr($data2, 0, 4) . '-' . substr($data2, 4, 2) . '-' . substr($data2, 6, 2);

        if( $forn == 990 ){
            $fornecedor = ' AND fornecedor IN(17, 21, 24, 27) ';
            $fornecedorL = ' AND L.fornecedor IN(17, 21, 24, 27) ';
        } elseif ($forn == 995) {
            $fornecedor = ' AND fornecedor IN(5, 6, 20, 23) ' ;
            $fornecedorL =' AND L.fornecedor IN(5, 6, 20, 23) ';
        }
        else{
            $fornecedor =   ' AND fornecedor=' . $forn;
            $fornecedorL = ' AND L.fornecedor=' . $forn;
        }


        $dataentrada = " DATE(dataentrada) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $datastatus = " DATE(L.datastatus) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $dataH = " DATE(H.data) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $dataF = " DATE(F.datafinanciado) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $dataC = " DATE(C.dtcontacto) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";


        $resp = array();
        //Obter as recebidas
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads  WHERE " . $dataentrada . $fornecedor);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['recebidas'] = $row['qty'];
        }

        //Obter as que foram puxadas 
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM cad_registocontacto C INNER JOIN arq_leads L ON L.id=C.lead  WHERE C.motivocontacto=0 AND " . $dataC . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['puxadas'] = $row['qty'];
        }
        //Obter as não atribuidos
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM  arq_leads L WHERE L.status=3 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['naoAtribuidas'] = $row['qty'];
        }

        //Obter dados Anuladas
        $query = "SELECT count(*) AS qty, R.motivo AS Motivo "
                . " FROM cad_rejeicoes R "
                . " INNER JOIN arq_leads L ON L.id=R.lead "
                . " WHERE  L.status=4 AND " . $datastatus . $fornecedorL
                . " GROUP BY  R.motivo ";

        $result = mysqli_query($con, $query);
        if ($result) {
            $temp = array();
            $total = 0;
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                array_push($temp, $row);
                $total += $row['qty'];
            }
            $resp['anuladas'] = $temp;
            $resp['totalAnuladas'] = $total;
        }

        //Obter as canceladas por excesso de tempo - não atendidas
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=5 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['cancExcTmp'] = $row['qty'];
        }
        //Obter as canceladas por não receberem documentação
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM  arq_leads L WHERE L.status=9 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['anulaFaltaDoc'] = $row['qty'];
        }
        //Obter as que Agendadas 6
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=6 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['agendadas6'] = $row['qty'];
        }
        //Obter as que Agendadas 7
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=7 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['agendadas7'] = $row['qty'];
        }
        //Obter as que passaram para analise
        $result = mysqli_query($con, "SELECT count(DISTINCT H.lead ) AS qty, SUM(P.valorpretendido) AS valor "
                . " FROM arq_histprocess H "
                . " INNER JOIN arq_leads L ON L.id=H.lead "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " WHERE H.status IN(10,11,20) AND " . $dataH . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['paraAnalise'] = $row;
        }
        //Obter as que estão em analise
        $result = mysqli_query($con, "SELECT count(*) AS qty, SUM(P.valorpretendido) AS valor  FROM arq_leads L "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " WHERE L.status IN(12,13,21,22) AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['emAnalise'] = $row;
        }
        //Obter as Recusadas ou não aprovadas na analise
        $result = mysqli_query($con, "SELECT count(DISTINCT H.lead) AS qty, SUM(P.valorpretendido) AS valor "
                . " FROM arq_histprocess H "
                . " INNER JOIN arq_leads L ON L.id=H.lead "
                . " INNER JOIN arq_processo P ON P.lead=L.id "
                . " WHERE H.status IN(14,15,19) AND " . $dataH . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['recNAproAnalise'] = $row;
        }
        //Obter as que Aguardam Documentação
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=8 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['aguardamDoc'] = $row['qty'];
        }

        //Obter as que foram aprovadas no periodo
        $result = mysqli_query($con, "SELECT count(DISTINCT H.lead) AS qty "
                . " FROM arq_histprocess  H "
                . " INNER JOIN arq_leads L ON L.id=H.lead "
//            . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE  H.status=16  AND " . $dataH . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['aprovadas']['qty'] = $row['qty'];
        }

        $result = mysqli_query($con, "SELECT sum(F.montante) AS valor"
                . " FROM ( SELECT distinct( H.lead) AS lead  FROM arq_histprocess  H "
                . " INNER JOIN arq_leads L ON L.id=H.lead "
                . " WHERE  H.status=16 AND  " . $dataH . $fornecedorL . ") A "
                . " INNER JOIN cad_financiamentos F ON F.lead=A.lead AND F.status IN (6,7) ");
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['aprovadas']['valor'] = $row['valor'];
        }
        //Obter as desistencias
        $result = mysqli_query($con, "SELECT count(*) AS qty FROM arq_leads L WHERE L.status=18 AND " . $datastatus . $fornecedor);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['desistencias'] = $row['qty'];
        }

        //Obter as financiadas e o valor
        $result = mysqli_query($con, "SELECT count(*) AS qty, sum(F.montante) AS valor "
                . " FROM arq_leads L "
                . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE  L.status=17 AND F.status=7 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['financiadas'] = $row;
        }

        //Obter as financiadas ACP e o valor
        $result = mysqli_query($con, "SELECT count(*) AS qty, sum(F.montante) AS valor FROM arq_leads L"
                . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE  L.status IN(23,24) AND F.status=7 AND " . $dataF . $fornecedorL);                                   //AND MONTH(F.datafinanciado)=%s 
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['financiadasCP'] = $row;
        }

        //Obter as anulados FCP e o valor
        $result = mysqli_query($con, "SELECT count(*) AS qty, sum(F.montante) AS valor FROM arq_leads L"
                . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                . " WHERE  L.status=25 AND F.status=12 AND " . $datastatus . $fornecedorL);
        if ($result) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $resp['financiadasFCP'] = $row;
        }

        return $resp;
    }

    private function reportList($forn, $data1, $data2, $tipo, $motivo) {
        require_once '../php/openCon.php';
        $resp = array();

        
        if( $forn == 990 ){
            $fornecedor = ' AND fornecedor IN(17, 21) ';
            $fornecedorL = ' AND L.fornecedor IN(17, 21) ';
        } elseif ($forn == 995) {
            $fornecedor = ' AND fornecedor IN(5, 6, 20, 23) ' ;
            $fornecedorL =' AND L.fornecedor IN(5, 6, 20, 23) ';
        }
        else{
            $fornecedor =   ' AND fornecedor=' . $forn;
            $fornecedorL = ' AND L.fornecedor=' . $forn;
        }



        $dataentrada = " DATE(dataentrada) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $datastatus = " DATE(L.datastatus) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $dataH = " DATE(H.data) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $dataF = " DATE(F.datafinanciado) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";
        $dataC = " DATE(C.dtcontacto) BETWEEN '" . $data1 . "' AND '" . $data2 . "'";



        if ($tipo == 1) {
                $query = "SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, L.montante, L.dataentrada, L.datastatus, U.nome AS gestor "
                    . " FROM arq_histprocess H "
                    . " INNER JOIN arq_leads L ON L.id=H.lead"
                    . " LEFT JOIN cad_utilizadores U ON U.id=L.user "
                    . " WHERE H.status=1 AND " . $dataH . $fornecedorL . " GROUP BY L.id ";

        } elseif ($tipo == 4) {
            $query = sprintf("SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, L.montante, L.dataentrada, L.datastatus, U.nome AS gestor , R.motivo, R.outro, R.obs "
                    . " FROM arq_leads L "
                    . " LEFT JOIN cad_rejeicoes R ON R.lead=L.id "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " WHERE L.status=4 AND R.motivo = '%s' AND %s%s",  $motivo, $datastatus, $fornecedorL);
        } elseif ($tipo == 10) {
            $query = "SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus, U.nome AS gestor "
                    . " FROM arq_histprocess H "
                    . " INNER JOIN arq_leads L ON L.id=H.lead "
                    . " INNER JOIN arq_processo P ON P.lead=L.id "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " WHERE H.status IN(10,11,20) AND " . $dataH . $fornecedorL . " GROUP BY L.id ";
        } elseif ($tipo == 12) {
            $query = "SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus, U.nome AS gestor "
                    . " FROM arq_leads L "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " INNER JOIN arq_processo P ON P.lead=L.id"
                    . " WHERE L.status IN(12,13,21,22) AND " . $datastatus . $fornecedorL;
        } elseif ($tipo == 14) {
            $query = "SELECT L.idleadorig, L.id, H.lead, L.nome, L.email, L.telefone, P.valorpretendido AS montante, L.dataentrada, L.datastatus, U.nome AS gestor, R.motivo, R.outro, R.obs "
                    . " FROM arq_histprocess H "
                    . " INNER JOIN arq_leads L ON L.id=H.lead "
                    . " INNER JOIN arq_processo P ON P.lead=L.id "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " LEFT JOIN cad_rejeicoes R ON R.lead=L.id "
                    . " WHERE H.status IN(14, 15, 19) AND " . $dataH . $fornecedorL . " GROUP BY H.lead";
        } elseif ($tipo == 16) {
            $query = "SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, F.montante, L.dataentrada, L.datastatus, U.nome AS gestor, F.tipocredito, F.montante AS valorAprov, P.nome AS parceiro "
                    . " FROM arq_histprocess  H "
                    . " INNER JOIN arq_leads L ON L.id=H.lead "
                    . " INNER JOIN cad_financiamentos F ON F.lead=L.id"
                    . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " WHERE  H.status=16 AND F.status IN(6,7) AND " . $dataH . $fornecedorL . " GROUP BY L.id";
        } elseif ($tipo == 17) {
            $query = "SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, F.montante, L.dataentrada, L.datastatus, U.nome AS gestor, F.tipocredito, F.montante AS valorAprov, P.nome AS parceiro "
                    . " FROM arq_leads L  "
                    . " INNER JOIN cad_financiamentos F ON F.lead=L.id "
                    . " INNER JOIN cad_parceiros P ON P.id=F.parceiro "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " WHERE  L.status IN(17,23,24) AND F.status=7 AND " . $datastatus . $fornecedorL;
        } else {
            $query = "SELECT L.idleadorig, L.id, L.nome, L.email, L.telefone, L.montante, L.dataentrada, L.datastatus, U.nome AS gestor "
                    . " FROM arq_leads L "
                    . " INNER JOIN cad_utilizadores U ON U.id=L.user "
                    . " WHERE L.status=" . $tipo . " AND " . $datastatus . $fornecedorL;
        }

//echo $query;
        $result = mysqli_query($con, $query);
//        $temp = array();
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                array_push($resp, $row);
                
            }
        }
//        $resp['leads']= $temp;
        return json_encode($resp);
    }

}
