<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
    * Run like: app/Console/cake closesession
    * Relies on the SurveySession class to apply finalization rules.
*/
App::uses('AppController', 'Controller');
App::uses('SurveysController', 'Controller');
App::uses('InstrumentsComponent', 'Controller/Component');

class CloseSessionsShell extends AppShell {
    var $uses = array('SurveySession', 'Site', 'User');
    var $Surveys;
    var $Instruments;

    function startup() {
        parent::startup();

        $this->Surveys =& new SurveysController();
        $this->Surveys->session_id = 0; # blank value to avoid an error
        $this->Surveys->authd_user_id = 0;
        $this->Surveys->constructClasses();
        $this->Instruments = $this->Surveys->Components->load('Instruments');
        $this->Instruments->startup($this->Surveys);
    }

    function main() {

        parent::main();

        fwrite($this->log, date(DATE_RFC822) . " - Closing Expired survey sessions\n");

        $sessions = $this->SurveySession->expired_open_survey_sessions();
        fwrite($this->log, "Survey sessions found: " . count($sessions) . PHP_EOL);
        $surveySessionIds = Array();
        foreach($sessions as $session) {
            $this->Instruments->calculate_for_session($session["SurveySession"]["id"]);
            $this->SurveySession->finish($session);
            array_push($surveySessionIds, $session["SurveySession"]["id"]);
            fwrite($this->log, "Done closing expired survey session id " . $session["SurveySession"]['id'] . ", patient id " . $session['SurveySession']['patient_id'] . PHP_EOL);
        }

        if (defined('EMAIL_STAFF_SESSION_FINISH') && EMAIL_STAFF_SESSION_FINISH){
            // Call find again so that dates are properly converted to/from GMT
            $closedSessions = $this->SurveySession->find('all', Array('conditions' => Array('SurveySession.id' => $surveySessionIds), 'recursive'=>0));
            $url = $this->getAppURL();
            foreach($closedSessions as $closedSession){
                if ($closedSession['SurveySession']['type'] != ELECTIVE){

                    $user = $this->User->find('first',
                        array('conditions' => array('User.id' =>
                                    $closedSession['SurveySession']['patient_id']),
                            'recursive' => 0,
                            'contain' => array('Clinic'),
                            'fields' => array('Clinic.patient_status_email')
                    ));

                    if (isset($user['Clinic']['patient_status_email'])){
                        $email = new CakeEmail();
                        $email->from(array($user['Clinic']['support_email'] => SHORT_TITLE));
                        $email->to($user['Clinic']['patient_status_email']);
                        $email->subject(
                            'Assessment complete for patient ' .
                            $closedSession['SurveySession']['patient_id'] .
                            ' (' . $url . ')'
                        );

                        $email->viewVars(array('session' => $closedSession, 'url' => $url));
                        $email->emailFormat('html');
                        $email->template(CProUtils::getInstanceSpecificEmailName('assesment_finished', 'html'));
                        $email->send();
                        fwrite($this->log, "Done sending survey session complete email for survey session id" . $closedSession["SurveySession"]['id'] . ", patient id " . $closedSession['SurveySession']['patient_id'] . PHP_EOL);
                    }
                }
            }
        }

        fwrite($this->log,
                date(DATE_RFC822) .
                " - Done closing all expired survey sessions\n\n");
    }
}

?>
