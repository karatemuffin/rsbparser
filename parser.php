<?php
/**
 * RSb Parser mainscript
 *
 * @license    GPLv3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author     Christian Trenkwalder <christian@karatemuffin.it>
 *
 */
 
require_once('config.php');
require_once(__DIR__ . '/vendor/autoload.php');

use Mpdf\Mpdf;
use Spatie\PdfToText\Pdf;


//checks for errors and if the file is really uploaded
if ($_FILES['uploadedfile']['error'] !== UPLOAD_ERR_OK
	|| !is_uploaded_file($_FILES['uploadedfile']['tmp_name'])) {
	echo 'File upload failed. <a href="javascript:history.back()">Go Back</a>';
	exit();
}

try {
	$pdftext = Pdf::getText($_FILES['uploadedfile']['tmp_name']);
} catch (Exception $e) {
	echo 'Unable to validate PDF. <a href="javascript:history.back()">Go Back</a>';
	exit();
}

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

$mpdf = new Mpdf([
	'format' => 'A5-L',
	'margin_left' => 0,
	'margin_right' => 0,
	'margin_top' => 0,
	'margin_bottom' => 0,
	'margin_header' => 0,
	'margin_footer' => 0,
]);

$mpdf->SetCreator($conf['creator']);
$mpdf->SetCreator($conf['title']);

function writeFieldDiv($x,$y,$w,$txt){
	global $mpdf, $conf;
	$mpdf->WriteHTML('<div style="border-width: '.$conf['border-width'].'; border-style: '.$conf['border-style'].'; font-size: '.$conf['font-size'].'; position: absolute; top: '.$y.'mm; left: '.$x.'mm; width: '.$w.'mm;">'.$txt.'</div>');
}

for ($index = 0; $index <$success1; $index++) {
	$message=$matches4[1][$index].", ".$matches2[2][$index].", ".$matches1[2][$index].", ".$matches1[1][$index].", ".$matches3[4][$index];
	$receiver=$matches3[1][$index]."<br>".$matches3[2][$index]."<br>".$matches3[3][$index];

	$mpdf->AddPage();
	//Produktionsnorm Klebeetiketten Juni 2016 https://www.post.at/g/c/behoerdenbrief-rsa-rsb-geschaeftlich
	//Empfängerfeld (56,5 x 16 mm)
	writeFieldDiv(35,10,56.5,$receiver);

	//Absenderfeld (82 x 13 mm)
	writeFieldDiv(57,30,82,$conf['sender'].", ".$conf['id']);

	//Angabe des ursprünglichen Empfängers auf der Rückantwortkarte (60 x 10 mm)
	//left: 75-135mm top: 80-90mm
	writeFieldDiv(75,80,60,$message);

	//Rücksendungsanschrift auf der Rückantwortkarte (60 x 20 mm)
	writeFieldDiv(75,100,60,$conf['sender']."<br>".$conf['id']);
	
	//Empfängerfeld (56,5 x 60 mm)
	//left: 150-195mm top: 65-125mm
	writeFieldDiv(148,66,56.5,$receiver);
}

$mpdf->Output('RSb_'.$_FILES['uploadedfile']['name'], \Mpdf\Output\Destination::DOWNLOAD);
