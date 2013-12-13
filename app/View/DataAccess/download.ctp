<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/

header("Content-type: $contentType");
header('Content-disposition: attachment; filename="' . $destfile . '"');

//header('Content-Length: ' . fileSize($sourcefile));
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

ob_end_clean();

$handle = fopen($sourcefile, "rb");

while (
    $handle and
    !feof($handle) and
    connection_status() == 0 and
    !connection_aborted()
){
    set_time_limit(0);
    $buffer = fread($handle, 8192);
    echo $buffer;
    // flush();
    ob_flush();
}

flush();
ob_flush();
fclose($handle);

?>
