<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    *   Shell for viewing cake Auth generated hashes (based on this app's salt)
    *   run like: app/Console/cake seehash centsupprtr123
*/
App::uses('Controller', 'Controller');
App::uses('AuthComponent', 'Controller/Component');

class SeehashShell extends Shell {
    var $Controller;
    var $Auth;

    function main() {
        $this->Controller =& new Controller();
        $this->Auth = $this->Controller->Components->load('Auth');
        //$this->out('data:' . $data . "\n"); 
        $this->out('----------------------------------------' .    "\n");
        $this->out('Auth->password(' . $this->args[0] . ') = ' .  
            $this->Auth->password($this->args[0]). "\n");
        $this->out('----------------------------------------' .    "\n");
        
    }
}
?>
