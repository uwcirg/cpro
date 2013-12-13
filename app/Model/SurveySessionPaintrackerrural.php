<?php

/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
App::import('Model', 'SurveySession');
class SurveySessionPaintrackerrural extends SurveySession {

    var $name = 'SurveySession';
    var $useTable = 'survey_sessions';

    function finish($session) {
        parent::finish($session);
        // $this->log(__CLASS__ . '.' . __FUNCTION__ . '('.print_r($session, true).')', LOG_DEBUG);
        $this->id = $session['SurveySession']['id'];

        $this->bindModel(array(
            'hasMany' =>array(
                'Answer' => array('className' => 'Answer', 'dependent' => true)
            )
        ),false);

        $MEDChangeText = $this->Answer->analysisValueForSessionAndQuestion($this->id, 1225);
        $HUI3Emotion = $this->Answer->analysisValueForSessionAndQuestion($this->id, 1077);

        if ($MEDChangeText or $HUI3Emotion == 5){

            $user = $this->User->findById($session['SurveySession']['patient_id']);
            $email = new CakeEmail();
            $email->from(array($user['Clinic']['support_email'] => SHORT_TITLE))
                ->to($user['Clinic']['patient_status_email'])
                // ->to('ivanc@uw.edu')
                ->subject(SHORT_TITLE.': Change in Medication for patient: '.$user['User']['id'])
                ->viewVars(array(
                    'session' => $session,
                    'MEDChangeText' => $MEDChangeText,
                    'user' => $user
                ))
                ->emailFormat('html')
                ->template('assesment_finished_paintrackerrural');

            if ($MEDChangeText)
                $email->send();
            if ($HUI3Emotion == 5){
                $responseText = $this->Answer->bodyTextForAnswerToQuestion($this->id, 1077);
                $email->
                    subject(SHORT_TITLE.": Emotional State Warning for patient: {$user['User']['id']}")->
                    template('emotional_state_warning')->
                    viewVars(compact('session', 'responseText', 'user'));
                $email->send();
            }
        }
    }
}
