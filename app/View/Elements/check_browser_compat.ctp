<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<?php
if (Configure::read('isProduction')){

    echo "<script type=\"text/javascript\">" . "\n";
    echo "$(function(){" . "\n";
    echo "if(checkDhairBrowserCompatibility() == \"no\"){" . "\n";
    echo "window.location = \"users/help\"" . "\n";
    echo "}});" . "\n";
    echo "</script>" . "\n";
}
?>
