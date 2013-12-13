<?php

/**
 * Find out where our test application rests by reversing the path
 * tree until we find app/index.php.
 */
$dir = dirname(getcwd());

while (!file_exists($dir.DIRECTORY_SEPARATOR.'index.php'))
{
    $dir = dirname($dir);
}

define('TEST_APP', $dir);

/**
 * Bootstrap CakePHP without triggering a render action
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'bootstrap_cakephp.php');

/**
 * Include the required SimleTest files
 */
vendor('simpletest/unit_tester');
/////////////////////////////////////////////////
/**
 * This file is responsible for bootstraping CakePHP without invoking the Dispatcher
 */

/**
 * The file we include checks for this parameter in order to decide whether 
 * to dispatch or not. So if we set it to favicon.ico we avoid the Dispatcher
 * to be invoked.
 */
$_GET['url'] = 'favicon.ico';

// Let's capture the output. Because otherwise when DEBUG > 0 we get the <!-- execution time !-->
ob_start();
    require_once(TEST_APP.DIRECTORY_SEPARATOR.'webroot'.DIRECTORY_SEPARATOR.'index.php');
@ob_end_clean();

// Remove our little $_GET value like nothing had happened ; ).
unset($_GET['url']);

// Set our error reporting to get all errors regardless of CakePHP's debug setting
error_reporting(E_ALL);

// Load the AppController
loadController(null);   


?>