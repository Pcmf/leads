<?php

/* 
 * Criar PDF com os dados do processo_form
 * e os valores credito pretendido
 */
require_once '../openCon.php';
require_once '../fpdf.php';

$dt = json_decode(file_get_contents("php://input"));

$query = sprintf("SELECT P.valorpretendido, P.prazopretendido, P.prestacaopretendida, P.valorhabitacao, F.*,"
        . " D.nome AS ndoc, SF.nome AS nestadocivil, H.nome AS ntipohabitacao, PF.nome AS ntipocontrato,  "
        . " D2.nome AS ndoc2, SF2.nome AS nestadocivil2, H2.nome AS ntipohabitacao2, PF2.nome AS ntipocontrato2,"
        . " R.descricao AS nrelacaofamiliar "
        . " FROM arq_processo P "
        . " INNER JOIN arq_process_form F ON F.lead=P.lead "
        . " INNER JOIN cnf_tiposdoc D ON D.id=F.tipodoc"
        . " INNER JOIN cnf_sitfamiliar SF ON SF.id=F.estadocivil "
        . " INNER JOIN cnf_tipohabitacao H ON H.id=F.tipohabitacao "
        . " INNER JOIN cnf_sitprofissional PF ON PF.id=F.tipocontrato "
        . " LEFT JOIN cnf_tiposdoc D2 ON D2.id=F.tipodoc"
        . " LEFT JOIN cnf_sitfamiliar SF2 ON SF2.id=F.estadocivil "
        . " LEFT JOIN cnf_tipohabitacao H2 ON H2.id=F.tipohabitacao "
        . " LEFT JOIN cnf_sitprofissional PF2 ON PF2.id=F.tipocontrato "
        . " LEFT JOIN cnf_relacaofamiliar R ON R.id=F.relacaofamiliar "        
        . " WHERE P.lead=%s ", $dt->lead);
//echo $query;
$result = mysqli_query($con,$query );
if($result){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    // Criar o PDF
    $pdf = new FPDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    $pdf->SetFont('Times','B',12);
    $pdf->MultiCell(0,12,utf8_decode('CLIENTE GESTLIFES - REF: '.$row['lead']),0,'C');
    $pdf->Ln(6);
    
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Dados Pessoais');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
    $pdf->Ln(9);
    
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Times', '', 9);
    $pdf->Cell(12, 6, 'Nome:');
    $pdf->Cell(76, 6, utf8_decode($row['nome']),1,0,'L',true);
    $pdf->Cell(34, 6, 'Data de nascimento:',0,0, 'R');
    $pdf->Cell(20, 6, $row['datanascimento'],1,0,'L',true);
    $pdf->Cell(14, 6, 'NIF:',0,0, 'R');
    $pdf->Cell(22, 6, $row['nif'],1,0,'L',true);
    
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(20, 6, 'Documento:');
    $pdf->Cell(32, 6, utf8_decode($row['ndoc']),1,0,'L',true);    
    $pdf->Cell(12, 6, utf8_decode('Nº:'),0,0, 'R');
    $pdf->Cell(22, 6, $row['numdocumento'],1,0,'L',true);
    $pdf->Cell(18, 6, 'Validade:',0,0, 'R');
    $pdf->Cell(20, 6, $row['validade'],1,0,'L',true);    
    $pdf->Cell(26, 6, 'Nacionalidade:',0,0, 'R');
    $pdf->Cell(32, 6, $row['nacionalidade'],1,0,'L',true);      
    
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(24, 6, 'Estado Civil:');
    $pdf->Cell(40, 6, utf8_decode($row['nestadocivil']),1,0,'L',true);    
    $pdf->Cell(22, 6, utf8_decode('Filhos:'),0,0, 'R');
    $pdf->Cell(12, 6, $row['filhos'],1,0,'L',true);
    
//MORADA E CONTACTOS
    $pdf->Ln(16);
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Morada e Contactos');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
    $pdf->Ln(9);
    $pdf->SetFont('Times', '', 9);

    $pdf->Cell(24, 6, utf8_decode('Tipo Habitação:'));
    $pdf->Cell(40, 6, $row['ntipohabitacao'],1,0,'L',true);    
    $pdf->Cell(22, 6, 'Desde:',0,0,'R');
    $pdf->Cell(12, 6, $row['anoiniciohabitacao'],1,0,'L',true);  
    $pdf->Cell(32, 6, utf8_decode('Renda/Prestação:'),0,0,'R');
    $pdf->Cell(20, 6, $row['valorhabitacao'],1,0,'L',true);  
    
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(14, 6, 'Morada:');
    $pdf->Cell(70, 6, utf8_decode($row['morada']),1,0,'L',true);    
    $pdf->Cell(24, 6, 'Localidade:',0,0,'R');
    $pdf->Cell(40, 6, utf8_encode($row['localidade']),1,0,'L',true);     
    $pdf->Cell(16, 6, 'C.Postal:',0,0,'R');
    $pdf->Cell(18, 6, utf8_encode($row['cp']),1,0,'L',true);      
    
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(20, 6, 'Telefone:');
    $pdf->Cell(20, 6, utf8_decode($row['telefone']),1,0,'L',true);    
    $pdf->Cell(22, 6, 'Email:',0,0, 'R');
    $pdf->Cell(70, 6, utf8_decode($row['email']),1,0,'L',true);

//DADOS PROFISSIONAIS
    $pdf->Ln(16);
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Dados Profissionais');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
    $pdf->Ln(9);
    $pdf->SetFont('Times', '', 9);
    
    $pdf->Cell(14, 6, 'Sector:');
    $pdf->Cell(22, 6, utf8_decode($row['sector']),1,0,'L',true);    
    $pdf->Cell(34, 6, 'Tipo Contrato:',0,0,'R');
    $pdf->Cell(40, 6, utf8_encode($row['ntipocontrato']),1,0,'L',true);     
    $pdf->Cell(30, 6, 'Desde (ano-mes):',0,0,'R');
    $pdf->Cell(20, 6, $row['desde'].'-'.$row['desdemes'],1,0,'L',true);     

    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(18, 6, 'Empresa:');
    $pdf->Cell(70, 6, utf8_decode($row['nomeempresa']),1,0,'L',true);    
    $pdf->Cell(30, 6, 'NIF Empresa:',0,0,'R');
    $pdf->Cell(20, 6, $row['nifempresa'],1,0,'L',true);     
    $pdf->Cell(24, 6, 'Telefone:',0,0,'R');
    $pdf->Cell(20, 6, $row['telefoneempresa'],1,0,'L',true);    

//DADOS BANCARIOS
    $pdf->Ln(16);
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Dados Bancarios');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
    $pdf->Ln(9);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Times', '', 9);    

    $pdf->Cell(14, 6, 'IBAN:');
    $pdf->Cell(50, 6, $row['iban'],1,0,'L',true);    
    $pdf->Cell(24, 6, 'Desde:',0,0,'R');
    $pdf->Cell(20, 6, $row['ibandesde'],1,0,'L',true);     
    $pdf->Cell(38, 6, utf8_decode('Dia Prestação:'),0,0,'R');
    $pdf->Cell(20, 6, $row['telefoneempresa'],1,0,'L',true);  
    
//CREDITO PRETENDIDO
    $pdf->Ln(16);
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, utf8_decode('Crédito Pretendido'));
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
    $pdf->Ln(9);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Times', '', 9);    

    $pdf->Cell(14, 6, 'Valor:');
    $pdf->Cell(20, 6, $row['valorpretendido'],1,0,'L',true);    
    $pdf->Cell(24, 6, 'Prazo:',0,0,'R');
    $pdf->Cell(20, 6, $row['prazopretendido'],1,0,'L',true);     
    $pdf->Cell(30, 6, utf8_decode('Prestação:'),0,0,'R');
    $pdf->Cell(20, 6, $row['prestacaopretendida'],1,0,'L',true); 
    
    $pdf->Cell(30, 6, utf8_decode('Segundo Titular:'),0,0,'R');
    $row['segundoproponente']==1 ?
    $pdf->Cell(20, 6, 'SIM',1,0,'L',true) :      
    $pdf->Cell(20, 6, utf8_decode('Não'),1,0,'L',true);      
    //Segundo Titular se existir
    
if($row['segundoproponente']){
    $pdf->AddPage();
    $pdf->Ln(8);
    $pdf->SetFont('Times','B',12);
    $pdf->MultiCell(0,12,'Segundo Titular',0,'C');
    $pdf->Ln(3);
    
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Dados Pessoais');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
    
    $pdf->Ln(8);     
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Times', '', 9);       
    $pdf->Cell(28, 6, utf8_decode('Relação familiar:'));
    $pdf->Cell(50, 6, utf8_decode($row['nrelacaofamiliar']),1,0,'L',true);
        
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(12, 6, 'Nome:');
    $pdf->Cell(76, 6, utf8_decode($row['nome2']),1,0,'L',true);
    $pdf->Cell(34, 6, 'Data de nascimento:',0,0, 'R');
    $pdf->Cell(20, 6, $row['datanascimento2'],1,0,'L',true);
    $pdf->Cell(14, 6, 'NIF:',0,0, 'R');
    $pdf->Cell(22, 6, $row['nif2'],1,0,'L',true);
    
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(20, 6, 'Documento:');
    $pdf->Cell(32, 6, utf8_decode($row['ndoc2']),1,0,'L',true);    
    $pdf->Cell(12, 6, utf8_decode('Nº:'),0,0, 'R');
    $pdf->Cell(22, 6, $row['numdocumento2'],1,0,'L',true);
    $pdf->Cell(18, 6, 'Validade:',0,0, 'R');
    $pdf->Cell(20, 6, $row['validade2'],1,0,'L',true);    
    $pdf->Cell(26, 6, 'Nacionalidade:',0,0, 'R');
    $pdf->Cell(32, 6, $row['nacionalidade2'],1,0,'L',true);      
    
if(!$row['mesmahabitacao']){
    $pdf->Ln(8);
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Morada e Contactos');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);
     
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Times', '', 9);  
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(24, 6, utf8_decode('Tipo Habitação:'));
    $pdf->Cell(40, 6, $row['ntipohabitacao2'],1,0,'L',true);    
    $pdf->Cell(22, 6, 'Desde:',0,0,'R');
    $pdf->Cell(12, 6, $row['anoiniciohabitacao2'],1,0,'L',true);  

    
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(14, 6, 'Morada:');
    $pdf->Cell(70, 6, utf8_decode($row['morada2']),1,0,'L',true);    
    $pdf->Cell(24, 6, 'Localidade:',0,0,'R');
    $pdf->Cell(40, 6, utf8_encode($row['localidade2']),1,0,'L',true);     
    $pdf->Cell(16, 6, 'C.Postal:',0,0,'R');
    $pdf->Cell(18, 6, utf8_encode($row['cp2']),1,0,'L',true);      
}
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(20, 6, 'Telefone:');
    $pdf->Cell(20, 6, utf8_decode($row['telefone2']),1,0,'L',true);    
    $pdf->Cell(22, 6, 'Email:',0,0, 'R');
    $pdf->Cell(70, 6, utf8_decode($row['email2']),1,0,'L',true);

    //DADOS PROFISSIONAIS
    $pdf->Ln(8);
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(12, 6, 'Dados Profissionais');
    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY()+6;
    $pdf->Line(10, $y1, 200, $y1);

    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Times', '', 9);      
    $pdf->Ln();
    $pdf->Ln(6);
    $pdf->Cell(14, 6, 'Sector:');
    $pdf->Cell(22, 6, utf8_decode($row['sector2']),1,0,'L',true);    
    $pdf->Cell(34, 6, 'Tipo Contrato:',0,0,'R');
    $pdf->Cell(40, 6, utf8_encode($row['ntipocontrato2']),1,0,'L',true);     
    $pdf->Cell(30, 6, 'Desde (ano-mes):',0,0,'R');
    $pdf->Cell(20, 6, $row['desde2'].'-'.$row['desdemes2'],1,0,'L',true);     
    

    $pdf->Ln();
    $pdf->Ln(6);    

    $pdf->Cell(18, 6, 'Empresa:');
    $pdf->Cell(70, 6, utf8_decode($row['nomeempresa2']),1,0,'L',true);    
    $pdf->Cell(30, 6, 'NIF Empresa:',0,0,'R');
    $pdf->Cell(20, 6, $row['nifempresa2'],1,0,'L',true);     
    $pdf->Cell(24, 6, 'Telefone:',0,0,'R');
    $pdf->Cell(20, 6, $row['telefoneempresa2'],1,0,'L',true);   
    } 
    // Criar pdf
    $doc = 'doc_NB/Proposta_NB_'.$row['lead'].'.pdf';
    $pdf->Output($doc,'F');
    echo 'Proposta_NB_'.$row['lead'].'.pdf';
}

