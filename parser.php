<?php
/*
FIXME address data where no "anrede" is given will result in malformed pdf error
*/
require __DIR__ . '/vendor/autoload.php';

use Spatie\PdfToText\Pdf;

//$pdfname = 'Fruehwarnung_gesamt 2AHET.pdf';
if ($_FILES['uploadedfile']['error'] !== UPLOAD_ERR_OK               //checks for errors and if the file is really uploaded
    || !is_uploaded_file($_FILES['uploadedfile']['tmp_name'])) {
    echo 'File upload failed.';
    exit();
}


$pdftext = Pdf::getText($_FILES['uploadedfile']['tmp_name']);

//regex for name and class e.g.
//Match1: Name: Knoll Michael (2AHETss)
//Group1: Knoll Michael 
//Group2: 2AHETss
$pattern1 = '/Name: (.*)\((.*)\)/';

//regex for the course and the teacher e.g.
//Match1: Gegenstand Angewandte Informatik und fachspezifische Informationstechnik (DI Thomas Messner, MSc.)
//Group1: Angewandte Informatik und fachspezifische Informationstechnik
//Group2: DI Thomas Messner, MSc.

$pattern2 = '/Gegenstand\n?(.+)\ \((.*\n?.*)\)/';

//regex for te address e.g.
//Match1: Herr
//        Michael Knoll
//        Raiffeisenweg 32
//        8662 St. Barbara
//        Kapfenberg, 1. Juni 2021
//
//        Mitteilung über den Leistungsstand
//Group1: Herr
//Group2: Michael Knoll
//Group3: Raiffeisenweg 32
//Group4: 8662 St. Barbara
//Group5  2021 
$pattern3 = '/([A-Za-z]*)\n(.*)\n(.*)\n(.*)\n\w+,\ \d+.\ \w+\ (\d+)\n+Mitteilung/';

//regex for law
//Match1: Verständigung lt. SchUG § 19 (3) -
//Group1: SchUG § 19 (3)
$pattern4 = '/Verständigung lt\.\ (.+)\ -/';

$success1 = preg_match_all($pattern1,$pdftext,$matches1,PREG_PATTERN_ORDER);

$success2 = preg_match_all($pattern2,$pdftext,$matches2,PREG_PATTERN_ORDER);

$success3 = preg_match_all($pattern3,$pdftext,$matches3,PREG_PATTERN_ORDER);

$success4 = preg_match_all($pattern4,$pdftext,$matches4,PREG_PATTERN_ORDER);

if($success1 === 0 
    || $success1 !== $success2 
    || $success1 !== $success3 
    || $success1 !== $success4){
    echo 'The pdf you submitted was malformed. ('.$success1.','.$success2.','.$success3.','.$success4.')</br>';
    echo '<pre>'; print_r($matches1); echo '</pre></br>';
    echo '<pre>'; print_r($matches2); echo '</pre></br>';
    echo '<pre>'; print_r($matches3); echo '</pre></br>';
    echo '<pre>'; print_r($matches4); echo '</pre></br>';
    exit();
}

$array = array();
$array[] = array("Form1","Form2","Form3","Form4","Form5","Form6","Form7","Form8","Form9","Form10");
for ($index = 0; $index <$success1; $index++) {
    $array[] = array($matches3[1][$index],$matches3[2][$index],$matches3[3][$index],$matches3[4][$index],$matches1[1][$index],$matches1[2][$index],$matches2[1][$index],$matches2[2][$index],$matches3[5][$index],$matches4[1][$index]);
}

/***
 * Handles how values are represented e.g. enclosing
 * @param $value array
 * @return string array values enclosed in quotes every time.
 */
function encodeFunc($value) {
    ///remove any ESCAPED double quotes within string.
    $value = str_replace('\\"','"',$value);
    //then force escape these same double quotes And Any UNESCAPED Ones.
    $value = str_replace('"','\"',$value);
    //remove newlines inside a value
    $value = preg_replace('/[\r\n]+/',' ',$value);
    //force wrap value in quotes and return
    return '|'.$value.'|';
}

//create uuid so we have no collisions
$uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4)); 

$build_dir = 'build/';

$fp = fopen($build_dir.$uuid.'.csv', 'w');

//write the array down to the csv file, so we can read it in latex
$separator = ";";

foreach ($array as $fields) {
    fputs($fp, implode($separator, array_map("encodeFunc", $fields))."\r\n");
}

fclose($fp);

//execute the latex build command NOTE: listenname is expected from a5label.tex therefore we have to define it previous to call a5label.tex
//also note that we have to export here the home directory for www-data, else pdflatex will not find the necessary fonts
shell_exec('export HOME="/var/www"; pdflatex -output-directory='.$build_dir.' -jobname='.$uuid.' "\def\listenname{'.$build_dir.$uuid.'.csv}\input{a5label.tex}"');

$file = $build_dir.$uuid.'.pdf';

//send the file as whole directly to the client
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    
    //create an scheduled task to delete old files
		shell_exec('echo "rm '.$build_dir.$uuid.'*" | at now + 10 minutes');
  
    exit();
} else {
  	echo 'There was an error generating the output file. Please contact your administrator to have a look at the logs ('.$uuid.'.log)';
}
?>