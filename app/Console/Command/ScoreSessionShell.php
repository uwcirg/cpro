<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    *   Shell for calculating and storing instrument scores for a session 
    *   run like: app/Console/cake score_session 1234
*/

App::uses('AppController', 'Controller');
App::uses('SurveysController', 'Controller');
App::uses('InstrumentsComponent', 'Controller/Component');

class ScoreSessionShell extends Shell {
    var $uses = array("SurveySession");
    var $Surveys;
    var $Instruments;

    function startup() {

        Configure::write('debug', 3);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

    }

    function main() {

        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $sessionId = $this->args[0];
        $this->out("Calculating and saving instrument scores for survey session " . $sessionId . ". Note that this doesn't touch survey_session's modified, finished, or partial_finalization fields\n");

        $this->Surveys =& new SurveysController();
        $this->Surveys->session_id = $sessionId; 
        $this->Surveys->authd_user_id = 0;
        $this->Surveys->constructClasses();

        $this->Instruments = $this->Surveys->Components->load('Instruments');
        //$this->Instruments =& new InstrumentsComponent();
        $this->Instruments->startup($this->Surveys);

        $session = $this->SurveySession->findById($sessionId);
        $this->Instruments->calculate_for_session($sessionId);

        $this->out("Done calculating and saving instrument scores for survey session " . $session['SurveySession']['id'] . "\n");
        $this->log(__CLASS__ . "." . __FUNCTION__ . "() done", LOG_DEBUG);
    }
}

?>
