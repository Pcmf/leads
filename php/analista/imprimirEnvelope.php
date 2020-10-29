<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../openCon.php';
//require_once '../fpdf.php';
require_once '../rotation.php';

class PDF extends PDF_Rotate
{
function RotatedText($x,$y,$txt,$angle)
{
    //Text rotated around its origin
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
}
function RotatedImage($file,$x,$y,$w,$h,$angle)
{
    //Image rotated around its upper-left corner
    $this->Rotate($angle,$x,$y);
    $this->Image($file,$x,$y,$w,$h);
    $this->Rotate(0);
}
}

$lead = file_get_contents("php://input");

$orientation = 'Landscape';
$size = 'A4';
$angle = 270;

//Obter a morada
$result = mysqli_query($con, sprintf("SELECT nome, morada, localidade, cp FROM arq_process_form WHERE lead=%s", $lead));
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    
    //Criar PDF
    
    $pdf = new PDF('P', 'mm');
    $pdf->AddPage('portrait', [110,220]);
    
    $pdf->SetFont('Times','',11);
    
    //Remetente
    $pdf->RotatedImage('./../../img/gestlifes_logo_endereco.jpeg', 100, 10, 60, null, $angle);
    
    // Destinatário
    $pdf->RotatedText(30, 100, utf8_decode($row['nome']), $angle);
    $pdf->Ln();
    $pdf->RotatedText(25, 100, utf8_decode($row['morada']), $angle);
    $pdf->Ln();
    $pdf->RotatedText(20, 100, $row['cp'].'  '.utf8_decode($row['localidade']), $angle);
    
    
    // Envelope de devolução
     $pdf->AddPage('portrait', [110,220]);
     
        // Remetente
    $pdf->RotatedText(100, 10, utf8_decode($row['nome']), $angle);
    $pdf->Ln();
    $pdf->RotatedText(94, 10, utf8_decode($row['morada']), $angle);
    $pdf->Ln();
    $pdf->RotatedText(88, 10, $row['cp'].'  '.utf8_decode($row['localidade']), $angle);
    
        //Destinatario
    $pdf->RotatedImage('./../../img/gestlifes_logo_endereco.jpeg', 40, 130, 60, null, $angle);
    
    
    $doc = '../doc.pdf';
    $pdf->Output($doc, 'F');
    
    echo 'php/doc.pdf';
}

