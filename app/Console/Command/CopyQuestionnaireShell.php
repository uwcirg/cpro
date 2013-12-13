<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    *   run like: app/Console/cake copy_questionnaire 123
*/

App::uses('Controller', 'Controller');
App::uses('SurveysController', 'Controller');
App::uses('InstrumentsComponent', 'Controller/Component');

class CopyQuestionnaireShell extends Shell {
    var $uses = array("SurveySession");
    var $Surveys;
    var $Instruments;

    function startup() {

        Configure::write('debug', 3);
    }

    function main() {

        $qnrId = $this->args[0];
        $this->out("Copying questionnaire $qnrId.\n");

        $this->Surveys =& new SurveysController();
        //$this->Surveys->session_id = $qnrId; 
        //$this->Surveys->authd_user_id = 0;
        $this->Surveys->constructClasses();

        $this->Surveys->copy_questionnaire($qnrId);

        $this->out("Done copying questionnaire $qnrId.\n");
    }
}

?>
