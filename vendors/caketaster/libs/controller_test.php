<?php

class ControllerTestCase extends UnitTestCase
{
    var $cakeError = null;
    var $Controller = null;
    var $viewVars = null;
    
    function ControllerTestCase()
    {
        $this->UnitTestCase();
    }
    
    function requestUrl($url, $method = 'get', $params = array())
    {
        $this->__reset();
    
        if (low($method)=='get')
        {
            if (!empty($params))
                $url = $url.'?'.join('&', $params);
        }
        else 
            $_POST = $params;
        
        /**
         * By creating our on AppError class we can catch Object::cakeError() calls and
         * output them so we can throw an exception later on!
         */
        
        if (class_exists('AppError'))
        {
            eval(
            'class AppError
            {                  
                function AppError($method, $messages)
                {
                    AppError::setLastError($method, $messages);
                }
                
                function getLastError()
                {
                    return AppError::setLastError();
                }
                
                function setLastError($method = null, $messages = null, $delete = false)
                {
                    static $error;
                
                    if (!empty($messages))
                        $error = array($method, $messages);
                
                    if ($delete==true)
                        $error = null;
                
                    return $error;
                }
                
                function delLastError()
                {
                    AppError::setLastError(null, null, true);
                }
            }');
        }
        
        
        $this->cakeError = null;        
        
        $TestDispatcher =& new TestDispatcher();
        $TestDispatcher->TestCase =& $this;
        @ob_start();
        $TestDispatcher->dispatch($url);
        $output = @ob_get_clean();
        
        if (is_callable(array('AppError', 'getLastError')))
        {
            $error = AppError::getLastError();
            if (!empty($error))
            {
                $this->cakeError = $error;
            }
            AppError::delLastError();
        }        
        
        if (!is_null($this->cakeError))
        {
            switch ($this->cakeError[0])
            {
                case 'missingView':
                    $addition = '"'.$this->cakeError[1][0]['file'].'"';
                    break;
                default:
                    $addition = '"'.$this->cakeError[1][0]['className'].'"';
                    break;
            }
            
            $error = ': '.Inflector::humanize(Inflector::underscore($this->cakeError[0])).' '.$addition;
        }
        else 
            $error = null;

        $this->assertNull($this->cakeError, 'Get "'.$url.'"'.$error);
        
        /**
         * When running our TestCase rendering an action might causes some error notices
         * from the Sessions object that is trying to send headers.
         * 
         * Since there is nothing we can do to prevent those, we are going to filter them
         * out and add the $realErrors back into the queue later on.
         */
        $queue = &SimpleErrorQueue::instance();        
        $realErrors = array();        
        
        while ($error = $queue->extract())
        {
            if (!(preg_match('/headers already sent/i', $error[1]) && get_class($error[4]['this'])=='cakesession'))
            {
                $realErrors[] = $error;
                break;
            }
        }        
        
        /**
         * Add the real errors back to the queue
         */
        foreach ($realErrors as $error)
        {
            call_user_func_array(array(&$queue, 'add'), $error);
        }       
        
        $this->viewVars = $this->Controller->_viewVars; 
        
        return $output;
    }
    
    function get($url, $params = array())
    {
        return $this->requestUrl($url, 'get', $params);
    }
    
    function post($url, $params = array())
    {
        return $this->requestUrl($url, 'post', $params);
    }
    
    function __reset()
    {
        $this->viewVars = null;
        $_POST = array();
        $_GET = array();
        $_FILES = array();
        $_COOKIE = array();        
    }
}

?>