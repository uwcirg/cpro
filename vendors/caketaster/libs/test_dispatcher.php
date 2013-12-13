<?php

class TestDispatcher extends Dispatcher 
{
    var $TestCase = null;
    
    function dispatch($url, $additionalParams=array()) 
    {
        return parent::dispatch($url, $additionalParams);       
    }
    
    function _invoke (&$controller, $params, $missingAction = false)
    {
        $return = parent::_invoke($controller, $params, $missingAction);
        $this->TestCase->Controller =& $controller;
        return $return;
    }
    
    function cakeError($method, $messages)
    {
        $this->TestCase->cakeError = array($method, $messages);
    }
}

?>