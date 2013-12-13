<?php
/** 
    NOTE: THIS CODE IS ONLY NEEDED IF wkhtmltopdf IS RUNNING REMOTELY

Must be run on a system which has wkhtmltopdf (specifically, 0.8.0 static) functional (currently being run on phosphorus).
Intended to be called via HTTP.
Currently being called by views/results/clinic_report_pdf.ctp 
@param reportHtml The report html; note that the html currently references ./bannerprint.png
This code is a modification of svn:dhair/branches/cnics/provider-report-pdf.php
*/
 

$surveySessionId = $_REQUEST['surveySessionId'];
$reportHtml = $_REQUEST['reportHtml'];

if (get_magic_quotes_gpc() == 1){
    $reportHtml = stripslashes($reportHtml);
}

header('Content-type: application/pdf');
header('Content-disposition: attachment; filename="clinic-report-' . 
        $surveySessionId . '.pdf');

$tmpFileName = "/tmp/dhair2/wkhtmltopdf/transferred-clinic-report-" . 
                $surveySessionId . ".html";

$tmpFile = fopen($tmpFileName, 'w');
fwrite($tmpFile, $reportHtml);
fclose($tmpFile);

$command = 'wkhtmltopdf -s Letter ' . 
            //'-T 0 -R 0 -B 0 -L 0 ' . 
            //'--print-media-type ' . 
            $tmpFileName . ' -';

$descriptorspec = array(0 => array('pipe', 'r'), //stdin
			1 => array('pipe', 'w'), //stdout
			2 => array('pipe', 'w')); //stderr
$pipes = array();
// Display via Xvfb (have a cron job on phosphorus that starts an xvfb server process as www-data whenever the machine boots up):
// Xvfb :2 -screen 0 1600x1200x24 -nolisten tcp -dpi 72 & export DISPLAY=:2 xhost +
$env = array('DISPLAY' => ':2');

$process = proc_open($command, $descriptorspec, $pipes,
                     null, $env, array('bypass_shell' => TRUE));
if (is_resource($process)) {
  fpassthru($pipes[1]);
}

unlink($tmpFileName);
?>
