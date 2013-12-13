<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
    * Run like: app/Console/cake email_registration
    * Needs to be run daily to send out registration reminder emails
*/
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class EmailRegistrationShell extends AppShell {
    var $uses = array('User', 'Patient');
    var $helpers = array('Html');

    function startup() {
        $this->controller = new AppController();
    }

    function main() {

        $this->out("Output written to " . LOGS . "email_registration.log\n");
        $log = fopen(LOGS . "email_registration.log", "a");
        if (!$log) {
            die('Failed to open email_registration.log file');
        }
        fwrite($log, date(DATE_RFC822) .  " - Emailing unregistered patients\n");

        if (
            defined('PATIENT_SELF_REGISTRATION')
            && PATIENT_SELF_REGISTRATION
            && $this->User->hasField('dt_created')
        ){

            $url = $this->getAppURL();

            $week = new DateTime("now");
            $week->sub(new DateInterval('P'. 7 .'D'));

            $weeklyRegReminderCount = 3;

            for ($weekNum=1; $weekNum<=$weeklyRegReminderCount; $weekNum++){
                $offset = new DateTime($week->format(DATE_RFC822));
                $offset->sub(new DateInterval('P'. 1 .'D'));

                $users = $this->User->find(
                    'all',
                    array(
                        'conditions' => array(
                            'Patient.off_study_status' => null,
                            'User.registered' => null,
                            'User.dt_created >' => $offset->format('Y-m-d H:i:s'),
                            'User.dt_created <' => $week->format('Y-m-d H:i:s'),
                        ),
                    )
                );
                fwrite($log, "Checking date range: ".$offset->format('Y-m-d H:i').'---'.$week->format('Y-m-d H:i'). "\n");

                fwrite($log,'User ids found: ');
                foreach ($users as $user)
                    fwrite($log,$user['User']['id']);
                fwrite($log,"\n");

                foreach ($users as $user){

                    $email = new CakeEmail();
                    $email->from(array($user['Clinic']['support_email'] => SHORT_TITLE));
                    $email->to($user['User']['email']);
                    $email->subject(__('Registration for %s', SHORT_TITLE));

                    $email->viewVars(array('user' => $user, 'url' => $url, 'weekNum' => $weekNum));
                    $email->emailFormat('html');
                    $email->template(CProUtils::getInstanceSpecificEmailName('registration_reminder', 'html'));
                    $email->send();
                    fwrite($log, "Done sending sending registration reminder for userid ".print_r($user['User']['id'], true). "\n");
                }

                $week->sub(new DateInterval('P'. 7 .'D'));
            }
        }


    }
}

?>
