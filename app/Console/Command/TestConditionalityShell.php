<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    *   Shell for calculating and storing instrument scores for a session 
    *   run like: app/Console/cake score_session 1234
*/

App::uses('Controller', 'Controller');
App::uses('SurveysController', 'Controller');
App::uses('ConditionalityComponent', 'Controller/Component');


class TestConditionalityShell extends Shell {
    var $uses = array("SurveySession");
    var $Surveys;
    var $Conditionality;

    function startup() {
        $this->Surveys =& new SurveysController();
        //$this->Surveys->session_id = 0; # blank value to avoid an error
        $this->Surveys->constructClasses();
        $this->Conditionality = 
            $this->Surveys->Components->load('Conditionality');
        //$this->Conditionality =& new ConditionalityComponent();
        $this->Conditionality->startup($this->Surveys);
    }

    function main() {

        if (sizeof($this->args) != 3){
            $this->out("You need to provide 3 arguments: \n");
            $this->out("1) 'Qr' or 'Page'\n");
            $this->out("2) ID of the above'\n");
            $this->out("3) Survey Session ID'\n");
            return;
        }

        $type = $this->args[0];
        $elementId = $this->args[1];
        $sessionId = $this->args[2];

        $this->out("Running condition 'show$type' for $type $elementId and survey session " . $sessionId . "\n");
        $this->startup();

        $session = $this->SurveySession->findById($sessionId);
        $this->Surveys->session = $session;

        // eg showQr || showPage
        $result = call_user_func(array($this->Conditionality, 'show' . $type),
                                    $elementId, 
                                    $sessionId);
        $result ? $result = 'true' : $result = 'false';
        $this->out("\nResult: " . $result . "\n");
    }
}

?>
