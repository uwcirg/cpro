<?php 
/**
    * View helper for minifying js and css files
    * based on: http://verens.com/archives/2008/05/20/efficient-js-minification-using-php/
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

function delete_old_md5s($folder) {
    $olddate=time()-3600;
    $dircontent = scandir($folder);
    foreach($dircontent as $filename) {
        if (strlen($filename)==32 && filemtime($folder.$filename) && filemtime($folder.$filename)<$olddate) unlink($folder.$filename);
    }
}

function md5_of_request($folder, $files) {
    $dircontent = scandir($folder);
    $ret='';
    foreach($dircontent as $filename) {
        if(in_array($filename, $files)) {
            if (filemtime($folder.$filename) === false) return false;
            $ret.=date("YmdHis", filemtime($folder.$filename)).$filename;
        }
    }
    return md5($ret);
}



Class MinifyHelper extends AppHelper{

    var $helpers = array('Js','Html'); //used for seamless degradation when MinifyAsset is set to false;
    var $dir = "webroot/js/tmp/";

    var $js_files = array();
    var $css_files = array();

    function js($assets){
        if(!Configure::read('minify_js')) {
            echo $this->Html->script($assets);
        } else{
            $js_dir = ROOT .DS. APP_DIR .DS. 'webroot' .DS. 'js' .DS;
            try {
                $name = md5_of_request($js_dir, $assets);
            } catch (Exception $e) {
                print "</head><body><h1>Minification error; please email <?php echo HELP_EMAIL_ADDRESS; ?> this message: $js_dir</h1>";
                print_r($e);
            }

            if(!file_exists($js_dir . "tmp/$name.js")) {
                $js = "";
                foreach($assets as $asset) {
                    $js .= file_get_contents($js_dir . $asset);
                }
                require 'JSMin.php';
                $js = JSMin::minify($js);
                file_put_contents($js_dir . "tmp/$name.js", $js);
            }

            echo $this->Html->script("tmp/$name.js");
        }
    }

    // could add a similar css tool
}

?>
