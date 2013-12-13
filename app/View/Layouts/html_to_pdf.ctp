<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

//$this->log("you are in the html_to_pdf layout!", LOG_DEBUG);
//$this->log("action: " . $this->request->params['action'] . "; filename_prefix:$filename_prefix; pdfId:$pdfId", LOG_DEBUG);

/**
REPORT WILL NOT CONVERT TO PDF PROPERLY IF CAKE'S DEBUG LEVEL IS > 0 !!! 
*/
header('Content-type: application/pdf');
header('Content-disposition: inline; filename="' .
        $filename_prefix . $pdfId . '.pdf"');

/** 
Must be run on a system which has wkhtmltopdf (tested w/ v 0.9.9 aka 0.9.6 )
This code is a modification of svn:dhair/branches/cnics/provider-report-pdf.php
*/

if (get_magic_quotes_gpc() == 1){
    $content_for_layout = stripslashes($content_for_layout);
}

$tmpFileName = TMP . "report-pdf/for-pdf-" .
                $pdfId . ".html";

$tmpFile = fopen($tmpFileName, 'w');
fwrite($tmpFile, $content_for_layout);
fclose($tmpFile);

$command = 'wkhtmltopdf -s Letter ' .
            //'-T 0 -R 0 -B 0 -L 0 ' . 
            //'--print-media-type ' . 
            $tmpFileName . ' -';

$descriptorspec = array(0 => array('pipe', 'r'), //stdin
            1 => array('pipe', 'w'), //stdout
            2 => array('pipe', 'w')); //stderr
$pipes = array();

$process = proc_open($command, $descriptorspec, $pipes,
                     null, null, array('bypass_shell' => TRUE));
if (is_resource($process)) {
  fpassthru($pipes[1]);
}

unlink($tmpFileName);

?>
