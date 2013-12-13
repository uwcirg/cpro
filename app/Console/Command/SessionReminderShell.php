<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    * Send reminder emails to patients and staff with information about survey session window duration
    * This should be run after close_session
    * Run like: app/Console/cake session_reminder
*/
App::uses('AppController', 'Controller');
class SessionReminderShell  extends AppShell {

    var $uses = array('SurveySession', 'User', 'Patient');

    function main() {
        parent::main();

        $this->User->Behaviors->attach('Containable');
        $unfinishedSessions = $this->User->find('all', array(
            'contain' => array('Patient', 'Clinic'),
            // 'joins' => array(
                // array(
                    // 'table' => 'survey_sessions',
                    // 'alias' => 'SurveySession',
                    // 'conditions' => array(
                        // 'SurveySession.patient_id = User.id',
                        // 'SurveySession.finished !=' => 1,
                        // 'SurveySession.type' => array(
                            // ODD_WEEK,
                            // EVEN_WEEK,
                            // EVEN_WEEK_8,
                            // EVEN_WEEK_12
                        // ),
                    // )
                // ),
            // ),
            'fields' => array(
                'Patient.*',
                'User.*',
                // 'SurveySession.*',
                'Clinic.*',
            ),
            'recursive' => -1,
        ));
        fwrite($this->log, 'Found '.count($unfinishedSessions). ' users(s) to check'  . PHP_EOL);

        $url = $this->getAppURL();

        // Number of days to send staff emails at the end of a survey-session window
        $staffEmailDayRange = 2;

        // If DotW is Friday, send out emails day earlier so SSs don't close before patient can be contacted
        if (date('w') == 5 and $staffEmailDayRange <= 2)
            $staffEmailDayRange = 3;

        $localDate = new DateTime();
        $viewVars = array('url' => $url);
        $patientEmail = new CakeEmail();
        $patientEmail->
            subject(
                SHORT_TITLE . ' Survey Reminder')->
            from(array(
                // $user['Clinic']['support_email'] =>
                HELP_EMAIL_ADDRESS =>
                    SHORT_TITLE . ((Configure::read('isProduction')) ? '' : ' Development System')))->
            emailFormat('html')->
            template('patient_session_reminder');

        $staffEmail = clone $patientEmail;
        $staffEmail->template('staff_session_reminder')
        ->subject(SHORT_TITLE . ' Session Ending Soon');

        foreach ($unfinishedSessions as $session){
            $this->Patient->currentWindow = $this->Patient->nextWindow = null;
            $this->Patient->_initializeIntervalSessions($session);

            $timezone = new DateTimeZone($this->Patient->getTimeZone($session));
            $localDate = new DateTime($this->Patient->gmtToLocal("now", $timezone->getName()));

            if ($this->Patient->currentWindow)
            if (!(
                // $this->Patient->currentWindow and
                $this->Patient->currentWindow['start'] < $localDate and
                $this->Patient->currentWindow['stop'] > $localDate
            ))
                fwrite($this->log, "Error in calculating currentWindow for patient {$session['Patient']['id']}" . PHP_EOL);

            if ($this->Patient->nextWindow)
            if (!(
                // $this->Patient->nextWindow and
                $this->Patient->nextWindow['start'] > $localDate and
                $this->Patient->nextWindow['stop'] > $localDate
            ))
                fwrite($this->log, "Error in calculating nextWindow for patient {$session['Patient']['id']}" . PHP_EOL);

            $viewVars = array_merge($viewVars, array(
                'user' => $session['User'],
                'patient' => $session['Patient'],
                'currentWindow' => $this->Patient->currentWindow,
            ));

            // Patient has current, open window, send a reminder email
            if (
                $this->Patient->currentWindow and
                isset($session['User']['email']) and
                $session['User']['email']
            ){
                $patientEmail->
                    from(array(
                        $session['Clinic']['support_email'] =>
                            SHORT_TITLE . ((Configure::read('isProduction')) ? '' : ' Development System')))->
                    to($session['User']['email'])->
                    viewVars($viewVars);
                $patientEmail->send();
                fwrite($this->log, "Patient {$session['Patient']['id']} emailed: {$session['User']['email']}" . PHP_EOL);
            }

            // Send email to staff last 2 days of window, 3 if during weekend
            if (
                isset($this->Patient->currentWindow['stop'])
                and
                date_diff(
                    $localDate,
                    $this->Patient->currentWindow['stop']
                )->format('%a') < $staffEmailDayRange
            ){
                $staffEmail->
                    from(array(
                        $session['Clinic']['support_email'] =>
                            SHORT_TITLE . ((Configure::read('isProduction')) ? '' : ' Development System')))->
                    to($session['Clinic']['patient_status_email'])->
                    viewVars($viewVars);
                $staffEmail->send();
                fwrite($this->log, "Staff reminder for patient {$session['Patient']['id']} emailed: {$session['User']['email']}" . PHP_EOL);
            }
        }
    }
}
