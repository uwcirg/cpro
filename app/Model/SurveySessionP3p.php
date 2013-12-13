<?php

/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
App::import('Model', 'SurveySession');
class SurveySessionP3p extends SurveySession
{

    var $name = 'SurveySession';
    var $useTable = 'survey_sessions';

    /**
     *
     */
    function partially_finalize($session) {
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "(session)", LOG_DEBUG);
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "() session param:" . print_r($session, true), LOG_DEBUG);
        parent::partially_finalize($session);

        $this->id = $session['SurveySession']['id'];
        $this->Patient->id = $session['SurveySession']['patient_id'];

        $answerRef = ClassRegistry::init('Answer');

        // Check if patient was shown elements of consent and agreed
        $consented = $answerRef->analysisValueForSessionAndQuestion($this->id, 2055);
        if ($consented)
            $this->Patient->saveField('consent_status', Patient::ELEMENTS_OF_CONSENT);



        if ($session['Project']['id'] == P3P_BASELINE_PROJECT){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(session), next is setToParticipantAndRandomize()", LOG_DEBUG);
            $this->Patient->setToParticipantAndRandomize(
                            $session['Patient']['id'],
                            $session['Patient']['test_flag']);
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(session), returning", LOG_DEBUG);
    }// function partially_finalize($session)

    /**
     *
     */
    function finish($session) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(session)", LOG_DEBUG);
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "() session param:" . print_r($session, true), LOG_DEBUG);

        if ($session['SurveySession']['project_id'] != P3P_ELIGIBILITY_PROJECT 
            && $session['SurveySession']['project_id'] != P3P_BASELINE_CLINICAL_PROJECT){
            parent::finish($session);
            return;
        }

        $this->id = $session['SurveySession']['id'];
        $this->Patient->id = $session['SurveySession']['patient_id'];

        $answerRef = ClassRegistry::init('Answer');

        // Set eligibility flag
        if (
            // Inclusion criteria
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2056)) > 0 and
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2057)) > 0 and
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2058)) > 0 and
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2059)) > 0 and
            // Exclusion criteria
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2060)) == 0 and
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2061)) == 0 and
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2118)) == 0 and
            // Final eligibility test
            $answerRef->analysisValueForSessionAndQuestion($this->id, 2062) == 1
        )
            $this->Patient->saveField('eligible_flag', 1);
        elseif (
            // Exclusion criteria
            (sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2060)) > 0 ||
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2061)) > 0 ||
            sizeof($answerRef->analysisValueForSessionAndQuestion($this->id, 2118)) > 0)
            // Final eligibility test
            and $answerRef->analysisValueForSessionAndQuestion($this->id, 2062) == 0
        )
            $this->Patient->saveField('eligible_flag', 0);
        else {
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), didn't save eligible_flag", LOG_DEBUG);
            return; // without finishing, since the input was inconclusive. 
        }
        parent::finish($session);
        
    }//function finish

}

?>
