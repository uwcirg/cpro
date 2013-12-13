<?php

if (!defined('DS'))
    define('DS', DIRECTORY_SEPARATOR);

require_once(dirname(__FILE__).DS.'libs'.DS.'cake_taster.namespace.php');

CakeTaster::initTaster();

if (!@empty($argv[0]))
    $script = $argv[0];
    

?>