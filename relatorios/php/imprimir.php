<?php

/* 
 * Imprimir listagens do relatorio de leads
 */
require_once './fpdf.php';

$dt = json_decode(file_get_contents("php://input"));
$data = $dt->data;

//Obter a descrção do status


    $pdf = new FPDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    $pdf->SetFont('Times','B',12);
    $pdf->MultiCell(0,12,utf8_decode($dt->sts),0,'C');
    $pdf->Ln(6);
    
    $pdf->SetFont('Courier', 'B', 9);
    $pdf->Cell(12, 6, 'LEAD',1,0,'L');
    $pdf->Cell(40, 6, 'CLIENTE',1,0,'L');
    $pdf->Cell(20, 6, 'TELEFONE',1,0, 'L');
    $pdf->Cell(16, 6, 'VALOR',1,0, 'L');
    $pdf->Cell(28, 6, 'DATA ENTRADA',1,0,'L');
    $pdf->Cell(30, 6, 'DATA STATUS',1,0,'L');
    $pdf->Cell(22, 6, 'GESTOR',1,0, 'L');
    $pdf->Cell(22, 6, 'ANALISTA',1,0,'L');   
    
    $pdf->Ln(8);
    $pdf->SetFont('Times', '', 8);
    $ln = 0;
    foreach ($data AS $d){
        $ln++;
        !isset($d->analista)? $d->analista='':null;
        
        $pdf->Cell(12, 6, $d->id,0,0,'L');
        $pdf->Cell(40, 6, utf8_decode(ucwords(strtolower($d->nome))),0,0,'L');
        $pdf->Cell(20, 6, $d->telefone,0,0, 'R');
        $pdf->Cell(16, 6, $d->montante,0,0, 'R');
        $pdf->Cell(28, 6, substr($d->dataentrada,0,10),0,0, 'C');
        $pdf->Cell(30, 6, $d->datastatus,0,0, 'C');
        $pdf->Cell(22, 6, $d->gestor,0,0,'L');
        $pdf->Cell(22, 6, $d->analista,0,0,'L');
        $pdf->Ln(6);
        $ln % 40 == 0 ? $pdf->AddPage():null;
    }
    
$doc = 'doc/listagem.pdf';
$pdf->Output($doc,'F');
echo 'listagem.pdf';

