<?php
require __DIR__ . '/vendor/autoload.php';
require_once('tfpdf.php');

use Spatie\PdfToText\Pdf;

//checks for errors and if the file is really uploaded
if ($_FILES['uploadedfile']['error'] !== UPLOAD_ERR_OK
	|| !is_uploaded_file($_FILES['uploadedfile']['tmp_name'])) {
	echo 'File upload failed.';
	exit();
}

$pdftext = Pdf::getText($_FILES['uploadedfile']['tmp_name']);

//eliminate (ascii) control characters, except \n and \r
$pdftext=preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $pdftext);

//regex for name and class e.g.
//Match1: Name: Max Mustermann (2AHETss)
//Group1: Max Mustermann 
//Group2: 2AHETss
$pattern1 = '/Name: (.*)\ \((.*)\)/';

//regex for the course and the teacher e.g.
//Match1: Gegenstand Angewandte Informatik und fachspezifische Informationstechnik (DI Marianne Musterfrau, MSc.)
//Group1: Angewandte Informatik und fachspezifische Informationstechnik
//Group2: DI Marianne Musterfrau, MSc.

$pattern2 = '/Gegenstand\n?(.+)\ \((.*\n?.*)\)/';

//regex for te address e.g.
//Match1: Herr
//        Max Mustermann
//        Musterweg 32
//        1234 St. Musterstadt
//        Musterdorf, 1. Juni 2021
//
//        Mitteilung über den Leistungsstand
//Group1: Herr
//Group2: Max Mustermann
//Group3: Musterweg 32
//Group4: 8662 St. Musterstadt
//Group5  2021 
$pattern3 = '/(.*)\n(.*)\n(.*)\n.+,\ \d+\.\ .+\ (\d+)\n+Mitteilung/';

//regex for law
//Match1: Verständigung lt. SchUG § 19 (3) -
//Group1: SchUG § 19 (3)
$pattern4 = '/Verständigung lt\.\ (.+)\ -/';

$success1 = preg_match_all($pattern1,$pdftext,$matches1,PREG_PATTERN_ORDER);

$success2 = preg_match_all($pattern2,$pdftext,$matches2,PREG_PATTERN_ORDER);

$success3 = preg_match_all($pattern3,$pdftext,$matches3,PREG_PATTERN_ORDER);

$success4 = preg_match_all($pattern4,$pdftext,$matches4,PREG_PATTERN_ORDER);

if ($success1 === 0 
	|| $success1 !== $success2 
	|| $success1 !== $success3 
	|| $success1 !== $success4) {
	echo 'The pdf you submitted was malformed. ('.$success1.','.$success2.','.$success3.','.$success4.')</br>';
	echo '<pre>'; print_r($matches1); echo '</pre></br>';
	echo '<pre>'; print_r($matches2); echo '</pre></br>';
	echo '<pre>'; print_r($matches3); echo '</pre></br>';
	echo '<pre>'; print_r($matches4); echo '</pre></br>';
	exit();
}

$id=utf8_decode("ID: 621417");
//NOTE fpdf uses 
$sender="HTBLA Kapfenberg\nViktor-Kaplan-Straße 1\nAT-8605 Kapfenberg";

$pdf = new tFPDF( 'L', 'mm', 'A5' );;
$pdf->SetTopMargin(0);
$pdf->SetLeftMargin(0);
$pdf->SetCreator('https://github.com/karatemuffin/rsbparser');
$pdf->AddFont('DejaVu','','DejaVuSerifCondensed.ttf',true);
$pdf->SetFont('DejaVu','',11);

for ($index = 0; $index <$success1; $index++) {
	$message=$matches4[1][$index].", ".$matches2[2][$index].", ".$matches1[2][$index].", ".$matches1[1][$index].", ".$matches3[4][$index];
	$receiver=$matches3[1][$index]."\n".$matches3[2][$index]."\n".$matches3[3][$index];

	$pdf->AddPage();
	//Produktionsnorm Klebeetiketten Juni 2016 https://www.post.at/g/c/behoerdenbrief-rsa-rsb-geschaeftlich
	//Empfängerfeld (56,5 x 16 mm)
	$pdf->SetXY(42,7);
	$pdf->MultiCell(56.5,4,$receiver);

	//Absenderfeld (82 x 13 mm)
	$pdf->SetXY(52,25);
	$pdf->MultiCell(82,4,$sender.", ".$id);

	//Angabe des ursprünglichen Empfängers auf der Rückantwortkarte (60 x 10 mm)
	$pdf->SetXY(75,77);
	$pdf->MultiCell(60,4,$message);

	//Rücksendungsanschrift auf der Rückantwortkarte (60 x 20 mm)
	$pdf->SetXY(75,97);
	$pdf->MultiCell(60,4,$sender."\n".$id);
	
	//Empfängerfeld (56,5 x 60 mm)
	$pdf->SetXY(150,70);
	$pdf->MultiCell(56.5,4,$receiver);
}

$pdf->Output();
