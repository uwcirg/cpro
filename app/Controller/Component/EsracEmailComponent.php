<?php
/**
    * Email for ESRAC
    *
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

// Should we be using CakePHP email's component instead of PHP's mail() 
// function?
class EsracEmailComponent extends Component {
    /** Email address for ESRA-C help */
    const ESRAC_HELP = "esrachelp@rt.cirg.washington.edu";

    /** startup: called by cakePHP automatically when included into a controller
     * @ param AppController: current controller 
     */
    function startup(Controller $controller)
    {
    	$this->controller = $controller;
    }

    /**
     * Generate the 'From:' headers for a given email address
     * @param fromAddress email Address
     * @return Appropriate 'From:' headers
     */
    private function fromHeaders($fromAddress) {
        return "From: " . $fromAddress . "\n" .
               "Reply-To: " . $fromAddress . "\n" .
               "X-Mailer: PHP/" . phpversion();
    }

    /**
     * Send email inviting a new patient associate
     * @param patientAssociate The patient associate
     */
    function emailNewPatientAssociate($patientAssociate) {
        $emailBody = $patientAssociate['Associate']['invitationBody'];
        $emailBody .= "\r\n\r\nLink for website: " .
                        Router::url('/', true) .
                        'associates/register/' .
                        $patientAssociate['PatientAssociate']['webkey'];
        $emailSubject = $patientAssociate['Associate']['invitationSubject'];

        mail($patientAssociate["User"]["email"],
             $emailSubject,
             $emailBody,
             $this->fromHeaders(self::ESRAC_HELP));
    }

    /** Subject for a demographic survey link email */
    const SURVEY_SUBJECT = 'ESRA-C II clinician demographic survey';

    /** Survey link email body, part 1 */
    const SURVEY_BODY1 = 
        "Thank you for choosing to participate in the research study, \"Computerized Assessment for Patients with Cancer (ESRA-C II)\". As part of the study we are collecting demographic data from clinicians. Please follow the secure link below to submit your data confidentially. It should not take long -- there are only six questions.";
	
    /** Survey link email body, part 2 */
    const SURVEY_BODY2 = 
	"If you prefer not to complete the form online, please reply to this email and indicate your preference (over the phone, via paper and campus mail, etc). \r\n\r\n Thank you";

    /**
     * Email a clinician their demographic survey link
     * @param clinician The clinician
     * @param site Clinician's site
     * @param url URL for the survey
     * @return null if success, a message if failed
     */
    function emailSurveyLink($clinician, $site, $url) {
        $clinicianEmail = $clinician['Clinician']['email'];
        $staffEmail = $site['Site']['research_staff_email_alias'];
        $staffSig = $site['Site']['research_staff_signature'];

	if (empty($staffSig)) {
	    $staffSig = "\r\n";
        } else {
	    $staffSig = ",\r\n\r\n $staffSig \r\n";
        }

        if (empty($clinicianEmail)) {
	    return ('Clinician has no e-mail address');
        } else if (empty($staffEmail)) {
	    return ("No staff email address for site {$site['Site']['name']}");
        } 

	$emailBody = "Dear {$clinician['Clinician']['first_name']} " .
	             "{$clinician['Clinician']['last_name']}, \r\n\r\n" .
		     self::SURVEY_BODY1 . "\r\n\r\n$url\r\n\r\n" .
		     self::SURVEY_BODY2 . $staffSig;

        if (mail($clinicianEmail, self::SURVEY_SUBJECT, $emailBody, 
	         $this->fromHeaders($staffEmail))) 
        {
	    return null;
        } else {
	    return 'Email failed';
        }
    }
}
?>
