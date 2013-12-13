<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
    * Finds the correct datetime to put in reportable_datetime if null
    * Run like: app/Console/cake fix_reportable_datetime
*/

class FixReportableDatetimeShell extends Shell {
    var $uses = array('SurveySession', 'Answer');

    function main() {
        $logName = Inflector::underscore(str_replace('Shell', '', __CLASS__)) . '.log';

        $this->out('Output written to ' . LOGS . $logName . PHP_EOL);
        $log = fopen(LOGS . $logName, 'a');

        if (!$log)
            die("Failed to open file $logName");

        fwrite($log, date(DATE_RFC822) . ' - Adding reportable_datetime information' . PHP_EOL);

        $sessions = $this->SurveySession->find(
            'all',
            Array(
                'conditions' => Array('SurveySession.reportable_datetime' => null),
                'recursive'=>-1
            )
        );

        fwrite($log, 'Survey sessions found: ' . count($sessions) . '' . PHP_EOL);
        foreach($sessions as &$session) {
            $ssModified = new DateTime($session['SurveySession']['modified']);
            // Find the latest answer for each survey session
            $answer = $this->Answer->find(
                'first',
                Array(
                    'conditions' => Array(
                        'Answer.survey_session_id' => $session['SurveySession']['id'],
                        'Answer.modified !=' => null,
                    ),
                    'order' => 'Answer.modified DESC',
                    'recursive'=>-1
                )
            );

            // If we can't find an answer, default to the last time the survey session was modified
            if (!$answer or $ssModified > new DateTime($answer['Answer']['modified']) )
                $session['SurveySession']['reportable_datetime'] = $session['SurveySession']['modified'];
            else
                $session['SurveySession']['reportable_datetime'] = $answer['Answer']['modified'];
            
            
            
            fwrite(
                $log,
                'Done fixing survey session id ' . $session['SurveySession']['id'] .
                ', patient id ' . $session['SurveySession']['patient_id'] . '' . PHP_EOL
            );
        }
        
        $this->SurveySession->saveMany($sessions);
        
        fwrite($log, date(DATE_RFC822) . ' - Done fixing all survey sessions' . PHP_EOL . PHP_EOL);
    }
}
?>
