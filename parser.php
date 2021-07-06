<?php
require __DIR__ . '/vendor/autoload.php';

use Spatie\PdfToText\Pdf;

$pdfname = 'Fruehwarnung_gesamt 2AHET.pdf';
$pdftext = Pdf::getText($pdfname);
//echo $pdftext;
$fp = fopen('file.txt', 'w');
fwrite($fp,$pdftext);
fclose($fp);

//regex for name and class e.g.
//Match1: 942-971	Name: Knoll Michael (2AHETss)
//Group1: 948-962	Knoll Michael 
//Group2: 963-970	2AHETss
$pattern1 = '/Name: (.*)\((.*)\)/';

//regex for the course and the teacher e.g.
//Match1: 277-375	Gegenstand Angewandte Informatik und fachspezifische Informationstechnik (DI Thomas Messner, MSc.)
//Group1: 288-349	Angewandte Informatik und fachspezifische Informationstechnik
//Group2: 351-374	DI Thomas Messner, MSc.

$pattern2 = '/Gegenstand\n?(.+)\ \((.*\n?.*)\)/';

//regex for te address e.g.
//Match1: 0-113	Herr
//              Michael Knoll
//              Raiffeisenweg 32
//              8662 St. Barbara
//              Kapfenberg, 1. Juni 2021
//
//              Mitteilung über den Leistungsstand
//Group1: 0-4	Herr
//Group2: 5-18	Michael Knoll
//Group3: 19-35	Raiffeisenweg 32
//Group4: 36-52	8662 St. Barbara
//Group5        2021 
$pattern3 = '/([A-Za-z]*)\n(.*)\n(.*)\n(.*)\n\w+,\ \d+.\ \w+\ (\d+)\n+Mitteilung/';

//regex for law
//Match1: 138-172	Verständigung lt. SchUG § 19 (3) -
//Group1: 156-170	SchUG § 19 (3)
$pattern4 = '/Verständigung lt\.\ (.+)\ -/';

$success1 = preg_match_all($pattern1,$pdftext,$matches1,PREG_PATTERN_ORDER);

$success2 = preg_match_all($pattern2,$pdftext,$matches2,PREG_PATTERN_ORDER);

$success3 = preg_match_all($pattern3,$pdftext,$matches3,PREG_PATTERN_ORDER);

$success4 = preg_match_all($pattern4,$pdftext,$matches4,PREG_PATTERN_ORDER);

/*
if ($success1) {
	echo "Match: ".var_dump($matches1)."<br />"; 
	}
if ($success2) {
	echo "Match: ".var_dump($matches2)."<br />"; 
	}
if ($success3) {
	echo "Match: ".var_dump($matches3)."<br />"; 
	}	
*/
if($success1 !== $success2 || $success1 !== $success3 || $success1 !== $success4){
 echo "Regex size mismatch (".$success1.",".$success2.",".$success3.",".$success4.")";
 exit();
}

$array = array();
$array[] = array("Form1","Form2","Form3","Form4","Form5","Form6","Form7","Form8","Form9","Form10");
for ($index = 0; $index <$success1; $index++) {
  //echo "The number is: $index <br>";
  $array[] = array($matches3[1][$index],$matches3[2][$index],$matches3[3][$index],$matches3[4][$index],$matches1[1][$index],$matches1[2][$index],$matches2[1][$index],$matches2[2][$index],$matches3[5][$index],$matches4[1][$index]);
} 

//echo var_dump($array);

/***
 * @param $value array
 * @return string array values enclosed in quotes every time.
 */
function encodeFunc($value) {
    ///remove any ESCAPED double quotes within string.
    $value = str_replace('\\"','"',$value);
    //then force escape these same double quotes And Any UNESCAPED Ones.
    $value = str_replace('"','\"',$value);
    
    $value = preg_replace('/[\r\n]+/',' ',$value);
    //force wrap value in quotes and return
    return '|'.$value.'|';
}



$fp = fopen('file.csv', 'w');
$separator = ";";
   
foreach ($array as $fields) {
    fputs($fp, implode($separator, array_map("encodeFunc", $fields))."\r\n");
}

fclose($fp);

shell_exec('pdflatex a5label.tex');

?>


