<?php

/* 
 * Retorna os dados para o relatorio dos gestores
 */

require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);

$total_submetido_CC = [0,0];
$total_recusados_CC = [0,0];
$total_aprovados_CC = [0,0];
$total_financiados_CC = [0,0];
$total_cancelados_CC = [0,0];
$total_submetido_CP = [0,0];
$total_recusados_CP = [0,0];
$total_aprovados_CP = [0,0];
$total_financiados_CP = [0,0];
$total_cancelados_CP = [0,0];

$resp = array();
$resp0 = array();

if(isset($dt->opc) && $dt->opc ){
        if( $dt->opc=="dia"){
            $tm_sub = ' DATE(F.datasubmetido)= DATE(NOW())'; 
            $tm_sts = ' DATE(F.datastatus)= DATE(NOW())'; 
            $tm_apr = ' DATE(F.dataaprovado)= DATE(NOW())'; 
            $tm_fin = ' DATE(F.datafinanciado)= DATE(NOW())'; 
        } else {
            $tm_sub = ' YEAR(F.datasubmetido)=YEAR(NOW()) AND MONTH(F.datasubmetido)=MONTH(NOW())';
            $tm_sts = ' YEAR(F.datastatus)=YEAR(NOW()) AND MONTH(F.datastatus)=MONTH(NOW())';
            $tm_apr = ' YEAR(F.dataaprovado)=YEAR(NOW()) AND MONTH(F.dataaprovado)=MONTH(NOW())';
            $tm_fin = ' YEAR(F.datafinanciado)=YEAR(NOW()) AND MONTH(F.datafinanciado)=MONTH(NOW())';
        }
} else {
    $tm_sub = " DATE(F.datasubmetido) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $tm_sts = " DATE(F.datastatus) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $tm_apr = " DATE(F.dataaprovado) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
    $tm_fin = " DATE(F.datafinanciado) BETWEEN '".$dt->data1."' AND '".$dt->data2."'";
}
$user = '';
if (isset($dt->user) && $dt->user>0) {
    $user = " AND L.analista = ".$dt->user;
}

$result = mysqli_query($con, sprintf("SELECT id, nome from cad_parceiros WHERE ativo=1"));
if($result){
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        //obter o submetidos por parceiro - CC
        $query = sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor  "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CC' ".$user,
                $row['id'], $tm_sub);
       // obter o submetidos por parceiro - CC
        $result1 = mysqli_query($con,$query); 
        if($result1){
            $row_sub_CC = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_submetido_CC[0] += $row_sub_CC['qty'];
            $total_submetido_CC[1] += $row_sub_CC['valor'];
        } else {
           $row_sub_CC =null; 
        }
        //obter o submetidos por parceiro - CP
        $query = sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F"
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . "WHERE F.parceiro=%s AND %s AND F.tipocredito='CP'".$user,
                $row['id'], $tm_sub);
        $result11 = mysqli_query($con,$query );
        if($result11){
            $row_sub_CP = mysqli_fetch_array($result11, MYSQLI_ASSOC);
            $total_submetido_CP[0] += $row_sub_CP['qty'];
            $total_submetido_CP[1] += $row_sub_CP['valor'];
        } else {
           $row_sub_CP =null; 
        }
        
        
        // Recusados CC
        $query = sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F"
                . " INNER JOIN arq_leads L ON L.id=F.lead "                
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CC'"
                . " AND F.status IN(3,5)".$user,
                $row['id'], $tm_sts);
       
        $result1 = mysqli_query($con,$query); 
        if($result1){
            $row_rec_CC = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_recusados_CC[0] += $row_rec_CC['qty'];
            $total_recusados_CC[1] += $row_rec_CC['valor'];
        } else {
           $row_rec_CC =null; 
        }
        //Recusados - CP
        $result1 = mysqli_query($con, sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F"
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . "WHERE F.parceiro=%s AND %s AND F.tipocredito='CP' "
                . " AND F.status IN(3,5)".$user,
                $row['id'], $tm_sts));
        if($result1){
            $row_rec_CP = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_recusados_CP[0] += $row_rec_CP['qty'];
            $total_recusados_CP[1] += $row_rec_CP['valor'];            
        } else {
           $row_rec_CP =null; 
        }
        
        // Aprovados CC
        $query = sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . "WHERE F.parceiro=%s AND %s AND F.tipocredito='CC'".$user,
                $row['id'], $tm_apr);
       
        $result1 = mysqli_query($con,$query); 
        if($result1){
            $row_apr_CC = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_aprovados_CC[0] += $row_apr_CC['qty'];
            $total_aprovados_CC[1] += $row_apr_CC['valor'];            
        } else {
           $row_apr_CC =null; 
        }
        //Aprovados - CP
        $result1 = mysqli_query($con, sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CP' ".$user,
                $row['id'], $tm_apr));
        if($result1){
            $row_apr_CP = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_aprovados_CP[0] += $row_apr_CP['qty'];
            $total_aprovados_CP[1] += $row_apr_CP['valor'];               
        } else {
           $row_apr_CP =null; 
        }        
        
        // Financiados CC
        $query = sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CC'"
                . " AND F.status=7".$user,
                $row['id'], $tm_fin);
       
        $result1 = mysqli_query($con,$query); 
        if($result1){
            $row_fin_CC = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_financiados_CC[0] += $row_fin_CC['qty'];
            $total_financiados_CC[1] += $row_fin_CC['valor'];               
        } else {
           $row_fin_CC =null; 
        }
        //Financiados - CP
        $result1 = mysqli_query($con, sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CP' "
                . " AND F.status=7".$user,
                $row['id'], $tm_fin));
        if($result1){
            $row_fin_CP = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_financiados_CP[0] += $row_fin_CP['qty'];
            $total_financiados_CP[1] += $row_fin_CP['valor'];           
        } else {
           $row_fin_CP =null; 
        } 
        
        // Cancelados CC
        $query = sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CC'"
                . " AND F.status IN(8,9,12)".$user,
                $row['id'], $tm_sts);
       
        $result1 = mysqli_query($con,$query); 
        if($result1){
            $row_can_CC = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_cancelados_CC[0] += $row_can_CC['qty'];
            $total_cancelados_CC[1] += $row_can_CC['valor'];    
        } else {
           $row_can_CC =null; 
        }
        //Financiados - CP
        $result1 = mysqli_query($con, sprintf("SELECT count(*) AS qty, SUM(F.montante) AS valor "
                . " FROM cad_financiamentos F "
                . " INNER JOIN arq_leads L ON L.id=F.lead "
                . " WHERE F.parceiro=%s AND %s AND F.tipocredito='CP' "
                . " AND F.status IN(8,9,12)".$user,
                $row['id'], $tm_sts));
        if($result1){
            $row_can_CP = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            $total_cancelados_CP[0] += $row_can_CP['qty'];
            $total_cancelados_CP[1] += $row_can_CP['valor'];   
        } else {
           $row_can_CP =null; 
        } 
        
        $temp = array();
        $temp = $row;
        $temp['tipo'] = 'CC';
        $temp['sub'] = $row_sub_CC;
        $temp['rec'] = $row_rec_CC;
        $temp['apr'] = $row_apr_CC;
        $temp['fin'] = $row_fin_CC;
        $temp['can'] = $row_can_CC;
        array_push($resp0, $temp);
        
        $temp1 = array();
        $temp1 = $row;
        $temp1['tipo'] = 'CP';
        $temp1['sub'] = $row_sub_CP;
        $temp1['rec'] = $row_rec_CP;
        $temp1['apr'] = $row_apr_CP;
        $temp1['fin'] = $row_fin_CP;
        $temp1['can'] = $row_can_CP;
        array_push($resp0, $temp1);
    }
}

$resp_t = array();
$resp_t['total_s_cc'] = $total_submetido_CC;
$resp_t['total_s_cp'] = $total_submetido_CP;
$resp_t['total_r_cc'] = $total_recusados_CC;
$resp_t['total_r_cp'] = $total_recusados_CP;
$resp_t['total_a_cc'] = $total_aprovados_CC;
$resp_t['total_a_cp'] = $total_aprovados_CP;
$resp_t['total_f_cc'] = $total_financiados_CC;
$resp_t['total_f_cp'] = $total_financiados_CP;
$resp_t['total_c_cc'] = $total_cancelados_CC;
$resp_t['total_c_cp'] = $total_cancelados_CP;

array_push($resp, $resp0);
array_push($resp, $resp_t);
echo json_encode($resp);