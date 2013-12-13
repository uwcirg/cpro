<?php

require_once(dirname(dirname(__FILE__)).DS.'libs'.DS.'cake_taster.namespace.php');
CakeTaster::initTaster();

class TestsController extends AppController
{
    //var $scaffold;
    var $name = "Tests";
    var $uses = array();
    var $view = 'CakeTaster';
    var $helpers = array('Html', 'Javascript');
    
    function beforeFilter()
    {
        unset($this->Session);
        $this->components = array();
    }
    
    function index()
    {        
        $this->setAction('filter');
    }
    
    function filter()
    {    
        if (isset($this->params['url']['filter']))
            $filter = $this->params['url']['filter'];
        else 
            $filter = '*';
        
        // Allow * character to be used as wildchar
        $regexFilter = str_replace('*', '.*', $filter);
        
        // Replace DIRECTORY_SEPERATORS
        if (DS=='\\')
            $regexFilter = str_replace('/', DS.DS, $regexFilter);
        else 
            $regexFilter = str_replace('/', '\\'.DS, $regexFilter);
        
        // Escape slashes (/)
        $regexFilter = str_replace('/', '\\/', $regexFilter);
        
        $regexFilter = '^'.$regexFilter.'\.php$';
       
        uses('Folder');
        $TestsFolder =& new Folder(CAKE_TASTER_TESTS);
        $testFiles = $TestsFolder->findRecursive($regexFilter);
        
        $testCases = CakeTaster::runTests($testFiles);
                
        $this->set(compact('filter', 'testCases'));
    }
    
    function webroot()
    {
        $this->autoRender = false;
        
        $args = func_get_args();
        $path = join(DS, $args);
        if (strpos($path, '..')!==false)
            die('You cannot access files outside of '.CAKE_TASTER_WEBROOT.' with this method!');
            
        $file = CAKE_TASTER_WEBROOT.$path;
        
        
        if (file_exists($file))
        {
            $mimeType = CakeTaster::fileMimeType($file);
            header('Content-Type: '.$mimeType);
            header('Content-Length: '.filesize($file));
            readfile($file);
        }
        
        return;        
    }
}

?>