<?php
/** 
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class DhairLoggingComponent extends Component
{
    var $uses = array('Log');

    function logArrayContents($array, $description = "array to log"){
        ob_start();
        var_dump($array);
        $debugStr = ob_get_contents();
        ob_end_clean();
        $this->log($description . " = " . $debugStr . "; "
                    . Debugger::trace(), LOG_DEBUG);
    }

}
