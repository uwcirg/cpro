<?php

class CakeTasterReporter extends SimpleReporter  
{
    var $log;

    function CakeTasterReporter() 
    {

    }
    
    function logMsg($status, $message, $breadcrumb = null)
    {
        if (!is_array($this->log))
            $this->log = array();
            
        $entry = array();
        $entry['status'] = $status;
        $entry['message'] = $message;
        
        if (!empty($breadcrumb))
            $entry['breadcrumb'] = $breadcrumb;
        
        
        $this->log[] = $entry;
    }
    
    function paintHeader($test_name){}

    function paintFooter($test_name) 
    {
        /**
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        print "<div style=\"";
        print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
        print "\">";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete:\n";
        print "<strong>" . $this->getPassCount() . "</strong> passes, ";
        print "<strong>" . $this->getFailCount() . "</strong> fails and ";
        print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
        print "</div>\n";
        print "</body>\n</html>\n";
        **/
    }

    function paintFail($message) 
    {        
        parent::paintFail($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        
        $this->logMsg('failed', $message, $breadcrumb);
    }

    function paintError($message) 
    {
        parent::paintError($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        
        $this->logMsg('error', $message, $breadcrumb);
    }
    
     function paintPass($message)
     {
        parent::paintPass($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        
        $this->logMsg('passed', $message, $breadcrumb);     
     }

    function paintFormattedMessage($message) 
    {
        /**
        print '<pre>' . $this->_htmlEntities($message) . '</pre>';
        **/
    }
}

?>