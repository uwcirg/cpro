<?php

class CakeTaster
{
    function initTaster()
    {
        if (defined('CAKE_TASTER_ROOT'))
            return;
        
        define('CAKE_TASTER_ROOT', dirname(dirname(__FILE__)));
        define('CAKE_TASTER_VENDORS', CAKE_TASTER_ROOT.DS.'vendors'.DS);
        define('CAKE_TASTER_LIBS', CAKE_TASTER_ROOT.DS.'libs'.DS);
        define('CAKE_TASTER_CONTROLLERS', CAKE_TASTER_ROOT.DS.'controllers'.DS);
        define('CAKE_TASTER_VIEWS', CAKE_TASTER_ROOT.DS.'views'.DS);
        define('CAKE_TASTER_WEBROOT', CAKE_TASTER_ROOT.DS.'webroot'.DS);
    
        /**
         * Find out where our test application rests by reversing the path
         * tree until we find app/index.php.
         */
        $dir = dirname(getcwd());
        
        while (!file_exists($dir.DIRECTORY_SEPARATOR.'index.php'))
        {
            $dir = dirname($dir);
        }
        
        define('CAKE_TASTER_APP', $dir.DS);
        define('CAKE_TASTER_TESTS', CAKE_TASTER_APP.'tests'.DS);
        
        if (!defined(CAKE))
            CakeTaster::bootstrapCakePHP();        
        
        // Set our error reporting to get all errors regardless of CakePHP's debug setting
        error_reporting(E_ALL);
        
        CakeTaster::vendor('simpletest/unit_tester');
        CakeTaster::vendor('simpletest/reporter');
        CakeTaster::vendor('spyc/spyc');
        CakeTaster::uses('ControllerTest');
        CakeTaster::uses('TestDispatcher');
        CakeTaster::uses('CakeTasterReporter');
        
        require_once CAKE_TASTER_VIEWS.'cake_taster_view.php';
    }
    
    function existingClasses($classes = null)
    {
        static $existingClasses;
        
        if (!empty($classes))
            $existingClasses = get_declared_classes();
            
        return $existingClasses;
    }
    
    function runTest($start = false)
    {
        $newClasses = array_diff(get_declared_classes(), CakeTaster::existingClasses());
        
        foreach ($newClasses as $newClass)
        {
            $Test =& new $newClass();
            $Test->run(CakeTaster::getCurrentReporter());
        }
    }
    
    function runTests($testFiles)
    {
        $testStats = array();
        $testCases = array();
        foreach ($testFiles as $nr => $testFile)
        {
            if (basename($testFile)=='config.php')
                array_splice($testFiles, $nr, 1);
            else 
            {
                CakeTaster::existingClasses(get_declared_classes());
                require_once($testFile);
                $reporter =& CakeTaster::getCurrentReporter(true);
                
                $testName = str_replace('\\', '/' , substr($testFile, strlen(CAKE_TASTER_TESTS)));
                
                $passed = 0;
                $failed = 0;
                $errors = 0;
                $tests = $reporter->log;
                foreach ($tests as $test)
                {
                    if ($test['status']=='failed')
                        $failed++;
                    elseif ($test['status']=='error')
                        $errors++;
                    else 
                        $passed++;
                }
                
                list($type) = explode('/', $testName);
                $type = Inflector::camelize($type);
                
                $testCases[] = array('name' => $testName,
                                     'type' => $type,
                                     'passed' => $passed,
                                     'failed' => $failed,
                                     'errors'=> $errors,
                                     'tests' => $tests);
                
                
            }
        }
        
        return $testCases;
    }
    
    function &getCurrentReporter($reset = false)
    {
        static $TestReporter;
        
        if ($reset==true)
            $oldReporter =& $TestReporter[0];
        
        if (empty($TestReporter[0]) || ($reset == true))
            $TestReporter[0] =& new CakeTasterReporter();
        
        if (isset($oldReporter))
            return $oldReporter;
        else 
            return $TestReporter[0];
    }
    
    /**
     * Includes one or more foreign vendor files
     *
     * @param unknown_type $file
     */
    function vendor($file)
    {
        $args = func_get_args();
        foreach ($args as $arg)
        {
            if (strpos($arg, '.php')===false)
                $vendorFile = $arg.'.php';
            else 
                $vendorFile = $arg;

            require_once(CAKE_TASTER_VENDORS.$vendorFile);
        }
    }
    
    /**
     * Includes one or more Kaizhi Libraries
     *
     * @param unknown_type $class
     */
    function uses($class)
    {
        $args = func_get_args();
        foreach ($args as $arg)
        {
            $libraryFile = CakeTaster::underscore($arg).'.php';
            require_once(CAKE_TASTER_LIBS.$libraryFile);
        }
    }    

    function bootstrapCakePHP()
    {
        /**
        * The file we include checks for this parameter in order to decide whether 
        * to dispatch or not. So if we set it to favicon.ico we avoid the Dispatcher
        * to be invoked.
        */
        $_GET['url'] = 'favicon.ico';
        
        // Let's capture the output. Because otherwise when DEBUG > 0 we get the <!-- execution time !-->
        ob_start();
        require_once(CAKE_TASTER_APP.DS.'webroot'.DS.'index.php');
        @ob_end_clean();
        
        // Remove our little $_GET value like nothing had happened ; ).
        unset($_GET['url']);
                
        // Load the AppController
        loadController(null);   
    }

    function fileMimeType($file)
    {    
        preg_match('/.+\.(.+)$/', $file, $match);
        list($raw, $ext) = $match;
        
        switch ($ext)
        {
            case 'css':
                return 'text/css';
            case 'js':
                return 'text/javascript';
            case 'jpg':
                return 'image/jpg';
            case 'jpge':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case 'png':
                return 'image/png';
            default:
                return mime_content_type($file);

        }   
    }
    
    /**
     * Converts a $lowerCaseAndUnderscoredWord into a camlized one
     * 
     * This function is intellectual property of the cake software foundation.
     * Their licenses / regulations apply. (MIT license)
     *
     * @link http://cakefoundation.org/
     * @param unknown_type $lowerCaseAndUnderscoredWord
     * @return unknown
     */
	function camelize($lowerCaseAndUnderscoredWord) 
	{
		$replace = str_replace(" ", "", ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord)));
		return $replace;
	}

    /**
     * Converts a $camelCasedWord into and underscored one
     * 
     * This function is intellectual property of the cake software foundation.
     * Their licenses / regulations apply. (MIT license)
     *
     * @link http://cakefoundation.org/
     * @param unknown_type $camelCasedWord
     * @return unknown
     */
	function underscore($camelCasedWord) 
	{
		$replace = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
		return $replace;
	}
}

?>