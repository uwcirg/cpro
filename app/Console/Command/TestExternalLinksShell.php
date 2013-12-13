<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    *   Shell for testing links to external resources, eg those listed in teaching tipe and P3P teachings
    *   Does not check local files
    *   run like: app/Console/cake test_teaching_links 
*/

App::uses('Controller', 'Controller');
App::uses('SurveysController', 'Controller');
App::uses('InstrumentsComponent', 'Controller/Component');
App::import('Vendor', 'PHPMailer', array('file' => 'class.phpmailer.php'));
App::import('Vendor', 'html2text', array('file' => 'html2text.php'));

class TestExternalLinksShell extends Shell {
    var $uses = array("Project", "Questionnaire", "Scale");

    var $regexps = array("<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>",
                        //simpler = "<a href=\"([^\"]*)\">(.*)<\/a>";
                        // eg: javascript:openNewWindow("http://www.cancer.net")
                        "openNewWindow(\s*)\(\"([^\"]*)\"\)");

    function startup() {

        Configure::write('debug', 3);
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

    }

    function main() {

        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $this->out("Testing links to external sites...\n");
     
        $errorMsg = ''; 

        if (in_array("teaching_tips", 
                Configure::read('modelsInstallSpecific'))){
            $this->uses[] = "TeachingTip";
            $this->_loadModels();

            $tips = $this->TeachingTip->find('all',
                                        array(/*'conditions' => $tipConditions,*/
                                            'order' => 'text ASC',
                                            'recursive' => -1));
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), found tips: " . print_r($tips, true), LOG_DEBUG);

            foreach($tips as $tip){
                $errorMsg .= $this->findLinkAndTest(
                                $tip['TeachingTip']['text'],
                                'teaching tip ' . $tip['TeachingTip']['id']);
            }// foreach($tips as $tip){
        }

        if (strstr(INSTANCE_ID, 'p3p') !== false){
            $this->uses[] = "P3pTeaching";
            $this->_loadModels();
 
            $p3pteachings = 
                $this->P3pTeaching->find('all',
                                        array('recursive' => 0));
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), found p3pteachings: " . print_r($p3pteachings, true), LOG_DEBUG);

            foreach($p3pteachings as $p3pteaching){
                $errorMsg .= 
                    $this->findLinkAndTest(
                            $p3pteaching['P3pTeaching']['intervention_text'],
                            'P3P Teaching ' . $p3pteaching['P3pTeaching']['id']
                                 . ' intervention_text');
            }// foreach($p3pteachings as $p3pteaching){
        }


        if (!empty($errorMsg)){

            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->Host = Configure::read('mailServer');
            //$mail->SMTPDebug  = 2;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->Port = 465;
            $mail->Username = Configure::read('mailUsername');
            $mail->Password = Configure::read('mailPassword');
            $mail->Subject = "cPRO (instance '" . INSTANCE_ID . "') has broken links";

            $mail->SetFrom(ADMIN_EMAIL_ADDRESS,
                            "Site administrator");
            $mail->ClearReplyTos();
            $mail->AddReplyTo(ADMIN_EMAIL_ADDRESS,
                                'Site administrator');

            $mail->MsgHTML($errorMsg);
            $html2text = new html2text($errorMsg);
            $mail->AltBody = $html2text->get_text();
            $mail->ClearAddresses();
            $mail->AddAddress(ADMIN_EMAIL_ADDRESS);
            if(!$mail->Send()) {
                $this->log("Mailer Error: " . $mail->ErrorInfo ."\n", LOG_DEBUG);
            } else {
                $this->log("Message sent successfully.\n", LOG_DEBUG);
            }
        }

        $this->log(__CLASS__ . "." . __FUNCTION__ . "() done", LOG_DEBUG);
    } // main()

    /**
     *
     */
    function findLinkAndTest($text, $ignoredDescription){
        $errorMsg = '';

        foreach ($this->regexps as $regexp){
            // search for links
            preg_match_all("/" . $regexp . "/siU", 
                                $text, 
                                $matches);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), matches: " . print_r($matches, true), LOG_DEBUG);
 
            $hrefs = $matches[2];

            $descriptions = null;
            if (array_key_exists(3, $matches)){
                $descriptions = $matches[3];
            }

            foreach($hrefs as $key => $href){
   
                if (strpos($href, 'http') !== 0){
                    continue;
                }
 
                $friendlyDesc = $href;
                if (isset($descriptions)){
                    $friendlyDesc = "<a href =\"" . $href . "\">" 
                                        . $descriptions[$key] . "</a>";
                }

                $handle = curl_init($href);
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($handle,  CURLOPT_FAILONERROR, TRUE);
                curl_setopt($handle,  CURLOPT_FOLLOWLOCATION, TRUE);

                /* Get the HTML or whatever is linked in $href. */
                $response = curl_exec($handle);

                if ($response === FALSE){
                    $msg = "LINK BROKEN (curl exec failure) for $ignoredDescription; uri $href aka " . ": $friendlyDesc";
                    $this->out($msg);
                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(): " . $msg, LOG_DEBUG);
                    $errorMsg .= $msg . "<br/>\n<br/>\n";
                    curl_close($handle);
                    return $errorMsg;
                }

                $handle = curl_init($href);
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

                /* Get the HTML or whatever is linked in $href. */
                $response = curl_exec($handle);

                /* Check for 404 (file not found). */
                $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                curl_setopt($handle,  CURLOPT_FOLLOWLOCATION, TRUE);

                if(($httpCode >= 400) || ($httpCode === 0)) {

                    $msg = "LINK BROKEN (HTTP code $httpCode) for $ignoredDescription; uri $href aka " . ": $friendlyDesc";
                    $this->out($msg);
                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(): " . $msg, LOG_DEBUG);
                    $errorMsg .= $msg . "<br/>\n<br/>\n";
                }
                elseif($httpCode > 200) {
                    // eg 302 temp redirect
                    
                    $msg = "Link suspect (HTTP code $httpCode) for $ignoredDescription; uri $href aka $friendlyDesc . ";

                    $lastUrl = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
                    $msg .= "Last effective URL: $lastUrl . ";

                    $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
                    $msg .= "Content-Type of requested document: $contentType . ";

                    $redirects = curl_getinfo($handle, CURLINFO_REDIRECT_COUNT);
                    $msg .= "Redirect count: $redirects . ";

                    $this->out($msg);
                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(): " . $msg, LOG_DEBUG);
                }
                else {
                    $msg = "Link fine (HTTP code $httpCode) for $ignoredDescription; uri $href aka $friendlyDesc";
                    $lastUrl = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
                    $msg .= "Last effective URL: $lastUrl . ";

                    $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
                    $msg .= "Content-Type of requested document: $contentType . ";

                    $redirects = curl_getinfo($handle, CURLINFO_REDIRECT_COUNT);
                    $msg .= "Redirect count: $redirects . ";

//                    $this->out($msg);
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(): " . $msg, LOG_DEBUG);
                }

                curl_close($handle);

            }// foreach($hrefs as $href){
        }// foreach ($this->regexps as $regexp){

        return $errorMsg; 
    } // function findLinkAndTest($text, $ignoredDescription){

}

?>
