<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
* This should be run every day in the early AM 
*/

//App::import('Core', array('Controller', 'Router'));
//App::import('Core', 'Router');
//config('routes');

class CheckAndNoAgainsEmailShell extends Shell {
    var $uses = array("User", "Clinic", "Patient");

    function main() {
       
        $nonString = '';
        if (!Configure::read('isProduction')) $nonString = 'NON-';

        // kludgy, but Router not fxnl here
        $url = '';
        if (!Configure::read('isProduction')) $url = '-dev';
        $url = 
            "https://esrac$url.cirg.washington.edu/sme/patients/checkAgainCalendar";

        $this->out("Output written to " . LOGS . "check_and_no_agains_email.log\n"); 
        $log = fopen(LOGS . "check_and_no_agains_email.log", "a");
        if (!$log) {
            die('Failed to open check_and_no_agains_email.log file');
        }    
        fwrite($log, date(DATE_RFC822) . 
            ", " . $nonString . "production system - evaluating no/check-again counts...\n"); 

        $clinics = $this->Clinic->find('all', array('recursive' => -1));
        foreach ($clinics as $clinic){
            $numCheckAgains = 0;
            $numNoCheckAgains = 0;
            
            $clinicId = $clinic['Clinic']['id'];

            $clinicStaff = 
                $this->User->getClinicStaffForClinic($clinicId);
            if (sizeof($clinicStaff) == 0){
                fwrite($log, "No clinic staff for clinic " . $clinicId . "\n");
                continue;
            }
            else {
                // a bit kludgy, but Patient.findCheckAgains has odd params
                $firstStaff = $clinicStaff[0];
                $today = date('Y-m-d');
                $patients = $this->Patient->findCheckAgains(
                                    $firstStaff['User']['id'], 
                                    false, false, 
                                    $today, $today);
                $numCheckAgains = sizeof($patients);
                $patients = $this->Patient->findNoCheckAgains(
                                    $firstStaff['User']['id'], 
                                    false, false, 
                                    $today);
                $numNoCheckAgains = sizeof($patients);
                fwrite($log, "For clinic $clinicId, numCheckAgains = " .
                    $numCheckAgains . "; numNoCheckAgains = " . 
                    $numNoCheckAgains . "\n");
                foreach($clinicStaff as $staff){
                    fwrite($log, 'emailing ' . $staff['User']['email'] . "\n");
                    mail($staff['User']['email'],
                        'ESRA-C SME (' . $nonString . 'production system) ' . $today . ' Check-Agains / No Check-Agains report',
                        "Greetings, this is an auto-generated email from the ESRA-C SME " . $nonString . "production system. \r\n\r\nAmong your clinic's patients, there are $numCheckAgains who need to be checked again today, and $numNoCheckAgains who do not have a check-again date set to today or sometime in the future.\r\n\r\nPlease visit $url for more information",
                        "From: " . ADMIN_EMAIL_ADDRESS . "\n" .
                            "Reply-To: " . ADMIN_EMAIL_ADDRESS . "\n" .
                            "X-Mailer: PHP/" . phpversion());
                }
            }

        }

        fwrite($log, date(DATE_RFC822) . 
            " - Done emailing clinic staff w/ no/check-again counts\n"); 
    }
}

?>
