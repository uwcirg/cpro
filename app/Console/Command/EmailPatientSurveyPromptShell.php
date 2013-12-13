<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * crontab runs this hourly (easier to set up), but this should only search for appts every two hours, so this script bails if hour %2 == 1
*
*/
// FIXME UPGRADE FROM CAKE 1.3 TO 2.0
App::import('Vendor', 'PHPMailer', array('file' => 'class.phpmailer.php'));
App::import('Vendor', 'html2text', array('file' => 'html2text.php'));
class EmailPatientSurveyPromptShell extends Shell {
    var $uses = array("Appointment", "Clinic");

    function main() {

        $nonString = '';
        if (!Configure::read('isProduction')) $nonString = 'NON-';

        // kludgy, but Router not fxnl here
        $url = '';
        if (!Configure::read('isProduction')) $url = '-dev';
        $url = 
            "https://esrac$url.cirg.washington.edu/sme";

        $this->out("Output written to " . LOGS . "email_patient_survey_prompt.log\n"); 
        $log = fopen(LOGS . "email_patient_survey_prompt.log", "a");
        if (!$log) {
            die('Failed to open email_patient_survey_prompt.log file');
        }
    
        //crontab runs this hourly, but it should only be run every other hour
        $hour = date('g');
        if ($hour % 2 == 1) {
            fwrite($log,  date(DATE_RFC822) .
            ", " . $nonString . "production system - bailing on email script, since hour ($hour) % 2 == 1\n"); 
            return;               
        }

        fwrite($log, date(DATE_RFC822) . 
            ", " . $nonString . "production system - emailing survey prompt to patients with appointments between 24-26, or 46-48 hours hence, if the appt doesn't have a session, or its session is not finished\n"); 
        
        $timeRangeConditions = array(
            array('Appointment.datetime >=' => gmdate(MYSQL_DATETIME_FORMAT, strtotime("+24 hours")),
                    'Appointment.datetime <=' => gmdate(MYSQL_DATETIME_FORMAT, strtotime("+26 hours"))),
            array('Appointment.datetime >=' => gmdate(MYSQL_DATETIME_FORMAT, strtotime("+46 hours")),
                    'Appointment.datetime <=' => gmdate(MYSQL_DATETIME_FORMAT, strtotime("+48 hours")))
        );

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = Configure::read('mailServer'); 
        //$mail->SMTPDebug  = 2;
        $mail->SMTPAuth = true; 
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;      
        $mail->Username = Configure::read('mailUsername');
        $mail->Password = Configure::read('mailPassword');
        $mail->Subject = 'The ESRA-C symptom survey is now available';


        foreach ($timeRangeConditions as $timeRangeCondition){

            $appts = $this->Appointment->find('all', 
                                array('conditions' => $timeRangeCondition,
                                        'recursive' => 2));
            //fwrite($log, "appts for condition " . print_r($timeRangeCondition, true) . " : " . print_r($appts, true) . "\n");
            foreach($appts as $appt){

                if ((!isset($appt['SurveySession']['finished']) ||
                    $appt['SurveySession']['finished'] == 0)
                    && ($appt['Patient']['consent_status'] != 'off-project') ){

                    $email = $appt['Patient']['User']['email'];
                    $clinic = $this->Clinic->find('first', array(
                                'conditions' => array('Clinic.id' => 
                                    $appt['Patient']['User']['clinic_id'])));
                    $datetime = $appt["Appointment"]{"datetime"}; // afterFind adjusts per timzone
                    $datetime = date('l, M jS \a\\t g:i A', strtotime($datetime));

                    fwrite($log, "emailing patient id " . $appt["Appointment"]["patient_id"] . " at $email to prompt for appt id " . $appt["Appointment"]["id"] . ", which has (patient timezone) datetime " . $datetime . "\n");

                    $mail->SetFrom($clinic['Clinic']['support_email'], 
                                    $clinic['Clinic']['friendly_name']);
                    $mail->ClearReplyTos();
                    $mail->AddReplyTo($clinic['Clinic']['support_email'], 
                                    $clinic['Clinic']['friendly_name']);
                    $body = "The ESRA-C symptom survey is now available at:<br/> $url  <br/><br/>Please remember to complete it before our next scheduled appointment on $datetime.";

                    $mail->MsgHTML($body);
                    $html2text = new html2text($body);
                    $mail->AltBody = $html2text->get_text();
                    $mail->ClearAddresses();
                    $mail->AddAddress($email);
                    if(!$mail->Send()) {
                        fwrite($log, "Mailer Error: " . $mail->ErrorInfo ."\n");
                    } else {
                        fwrite($log, "Message sent successfully.\n");
                    }

                    /** 
                    mail($email,
                        'The ESRA-C symptom survey is now available',
                        $body, 
                        "From: " . $clinic['Clinic']['friendly_name'] . 
                            " <" . $clinic['Clinic']['support_email'] . ">\n" .
                        "Reply-To: " . $clinic['Clinic']['support_email'] . 
                            "\n" .
                        "X-Mailer: PHP/" . phpversion());
                    */
                }
                    
            }        

        }

        fwrite($log, date(DATE_RFC822) . 
            " - Done emailing survey prompts\n"); 
    }
}

?>
