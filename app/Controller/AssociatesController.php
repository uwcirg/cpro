<?php
/** 
    *  Associates controller
    *
    *  Associates have a user account controlling their login,
    *  PatientAssociate records linking them to patients they have
    *  have been authorized to view, and PatientAssociateSubscales
    *  determining which data they can view for that patient.
    *
    *  Associates are authenticated in a two-step process:
    *  1. Creating an account to ensure their email address is correct
    *      When the associate record is created, we generate a random
    *      key which is emailed to them as part of a url to click in
    *      order to set up their account. This allows us to look up 
    *      the appropriate associate record without exposing the 
    *      associate's email address. 
    *  2. Entering a secret phrase to gain acces to a patient's data
    *      The patient must set a secret phrase which the associate
    *      must enter to gain access to their data. The PatientAssociate
    *      record is created with its verified field set to false,
    *      true indicates that they have entered the secret phrase.
    *      None of the patient's data except initials should be displayed
    *      until the associate is verified.
    *
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class AssociatesController extends AppController {
    var $uses = array("Associate", "User", "Patient", "PatientAssociate", "Scale");
    var $components = array('Password', 'EsracEmail');
    //var $helpers = array("Html", "Plural");
    var $helpers = array("Html");

    function beforeFilter() {
        parent::beforeFilter();
    }

    function beforeRender() {
        $this->TabFilter->show_normal_tabs();
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('jquery.flot.js', 
                'cpro.controllers.js', 'excanvas.js',
                'jquery.jeditable.js',
                'ui.datepicker.js', 
                'jquery.template.js', 'ui.tabs.js', 'cpro.jquery.js',
                'jquery.validate.js', 'cpro.jquery.validate.js'));
        parent::beforeRender();
    }

    function delete($p_a_id) {
        $success = $this->PatientAssociate->deleteBy($p_a_id, $this->authd_user_id);

        if($success) {
            $this->Session->setFlash("The associate was successfully removed.");
        } else {
            $this->Session->setFlash("The associate could not be removed.");
        }
        $this->redirect("/associates/edit_list");
    }

    function create() {
        // add / update secret word
        $this->patient = $this->Patient->updateSecretPhrase($this->authd_user_id, $this->request->data['Patient']['secret_phrase']);

        if ($this->request->data["User"]["email"] == $this->user['User']['email']){
            $message = "Sorry, you can't share charts with your own email address. To see these charts, simply click the \"View My Reports\" tab.";
        }

        else {
        # find or create all appropriate models for the new associate
            $associateUser = $this->User->findOrCreateAssociateUserForEmail(
                                                $this->request->data["User"]["email"]);
            $associateUser = $this->Associate->findOrCreate($associateUser);
            $patientAssociate = $this->PatientAssociate->createOrFalse(
                                $associateUser, $this->patient, $this->request->data);
            if($patientAssociate) {
                $this->EsracEmail->emailNewPatientAssociate($patientAssociate);
                $message = "You are now sharing charts with " . 
                                    $associateUser["User"]["username"];
            } else {
                $message = "You are already sharing charts with " . 
                                    $associateUser["User"]["username"];
            }
        }

        $this->Session->setFlash($message);
        $this->redirect("/associates/edit_list");
    }

    // This action takes the PatientAssociate record id,
    // not just the associate or patient id.
    function edit($pa_id) {
        //$this->log('pa_id: ' . $pa_id , LOG_DEBUG );
        //$this->DhairLogging->logArrayContents($this->request->data, "this->data");
        $patientAssociate = $this->PatientAssociate->findById($pa_id);

        // check that the id belongs to the patient
        if ($patientAssociate['PatientAssociate']['patient_id'] != 
            $this->Auth->user('id')) 
        {
            $this->log("associates.edit: illegal attempt to edit $pa_id by user {$this->Auth->user('id')}"); 
            $this->Session->setFlash('Not a valid associate id!');
            $this->redirect("/associates/edit_list");
        }

        $this->PatientAssociate->allowSubscales($pa_id, $this->request->data);
        $this->PatientAssociate->id = $pa_id;

        if (isset($this->request->data['PatientAssociate']['share_journal'])){
            $this->PatientAssociate->saveField('share_journal', 
                $this->request->data['PatientAssociate']['share_journal']);
        } else {
            $this->PatientAssociate->saveField('share_journal', '0'); 
        }
        $message = "Your sharing settings have been changed."; # FIXME: could be more specific.

        $this->Session->setFlash($message);
        $this->redirect("/associates/edit_list");
    }

    /**
    *  Used by ParticipantTreatment to mod their list of associates
    *
    */
    function edit_list(){
        $this->initShareMyReportsTabNav();
        $patient = $this->Patient->find('first',
            array(
                'conditions'
                => array('Patient.id'
                => $this->Auth->user('id')),
                'recursive' => 0
            ));
        //$this->DhairLogging->logArrayContents($patient, "patient");
        $this->set('patient', $patient);

        $patntAssociates = $this->PatientAssociate->forPatient(
            $patient["Patient"]["id"]);
        //$this->DhairLogging->logArrayContents($patntAssociates, "patntAssociates");
        $this->set('patntAssociates', $patntAssociates);

        $project = 1; // Calculate for this user if additional projects added
        $scales = $this->Scale->sAndSubscalesForProject($project);
        $this->set('scales', $scales);
    }

    function initShareMyReportsTabNav(){
        $this->TabFilter->selected_tab("Share My Reports");
        $this->TabFilter->show_normal_tabs();

        $quickLinks = array();

        $quickLinks["Share your reports"] =
                            array("controller" => "associates",
                                  "action" => "edit_list");

        $this->addToQuickLinks($quickLinks);
    }

    /**
    *   URL sent to associates points at this actioni
    *   Gives the associate the opportunity to:
    *       change their loginID (defaults to e-mail address)
    *       specify a password
    *   If user is alrady registered (), they'll be redirected to their home page.
    */
    function register($webkey){

        $this->set('tabs_for_layout', array('Welcome'));
        $this->TabFilter->selected_tab("Welcome");

        $patientAssociate = 
            $this->PatientAssociate->findByWebkey($webkey);
            /**
            $this->PatientAssociate->
                        find('first',
                            array(
                            'conditions' => array(
                                'PatientAssociate.webkey' => $webkey),
                            //'recursive' => 1) // didn't retrieve User
                            'recursive' => 2) // brought in way too much
                        );
            */
        //$this->DhairLogging->logArrayContents($patientAssociate, 'patientAssociate');
       
        //TODO Need to give error msg if no webkey match, and forward to contact page

        $user = $this->User->findById(
                                    $patientAssociate['Associate']['id']);
        //$this->DhairLogging->logArrayContents($user, 'user');

        // see if associate is already registered
        if ($user['User']['password'] == ''){
            //treat this associate as unregistered
            $this->set('user', $user);
            $this->set('patientAssociate', $patientAssociate);
            $patient = $this->Patient->findById(
                                        $patientAssociate['Patient']['id']);
            $this->set('patient', $patient);

        }
        else {
            // forward to login page
            $this->Session->setFlash("Your registration has been completed. Please log in now using your login ID and password.");
            $this->redirect("/users/");
        }
    }

    function registerFinish(){
        //$this->log('top of registerFinish()' , LOG_DEBUG );
        //$this->DhairLogging->logArrayContents($this->request->data, 'data');

        $this->set('tabs_for_layout', array('Welcome'));
        $this->TabFilter->selected_tab("Welcome");

        $patientAssociate = 
            $this->PatientAssociate->findByWebkey(
                                        $this->request->data['Associate']['webkey']);
        $this->PatientAssociate->set($patientAssociate);
        //$this->DhairLogging->logArrayContents($patientAssociate, 'patientAssociate');
        
        if ($patientAssociate['Patient']['secret_phrase'] !=
                $this->request->data['Associate']['secret_phrase']) 
        {

            //$this->log('secret phrase doesnt match.' , LOG_DEBUG );
            $this->Session->setFlash("Sorry, the secret phrase that you entered does not match the patient's secret phrase. Please ask the patient what their current secret phrase is.");
            $this->redirect("/associates/register/" . 
                            $this->request->data['Associate']['webkey']);
        }
	
	$password = $this->request->data['User']['password_confirm'];
        $this->request->data['User']['password_confirm'] =
           $this->Auth->password($this->request->data['User']['password_confirm']);

        if ($this->request->data['User']['password'] != 
            $this->request->data['User']['password_confirm']) 
        {
            $this->Session->setFlash("Sorry, those passwords did not match.");
            $this->redirect("/associates/register/" . 
                                $this->request->data['Associate']['webkey']);
        } else {
	    $pwSecurity = $this->Password->checkAssociatePassword($password);

            if (empty($pwSecurity['isSecure'])) {
                if ($pwSecurity['short']) {
                    $message = 'New password is too short.';
                } else { // not enough character groups
                    $message = 'New password does not contain enough different types of characters.  Please use numbers, letters (upper and lower case) and other characters.';
                }

                $this->Session->setFlash($message);
                $this->redirect("/associates/register/" . 
                                $this->request->data['Associate']['webkey']);
            }
        }

        $user = $this->User->findById(
                                    $patientAssociate['Associate']['id']);
        //$this->DhairLogging->logArrayContents($user, 'user');
	$this->request->data['User']['username'] = 
	    strip_tags($this->request->data["User"]["username"]);
        $user["User"]["username"] = $this->request->data["User"]["username"];
        $user["User"]["password"] = $this->request->data['User']['password'];
        // first validate, to get any failure messages
        $this->User->set($user);
        if (! $this->User->validates()){
            $errString = '';
            $invalidFields = $this->User->invalidFields();
            foreach ($invalidFields as $invalidField){
                $errString .= $invalidField . "<br>";    
            }
            $this->Session->setFlash($errString);
            $this->redirect("/associates/register/" . 
                                $this->request->data['Associate']['webkey']);
        }
        $this->User->save($user["User"]);

        $this->PatientAssociate->saveField('has_entered_secret_phrase', true);

        $this->Session->setFlash("Thank you for completing your registration. Please log in now using your new login ID and password.");
        $this->redirect("/users/index");
    }

    function phraseEntry(){
        //$this->DhairLogging->logArrayContents($this->request->data, 'data');
        
        $patientAssociate = 
            $this->PatientAssociate->findById(
                $this->request->data['PatientAssociate']['id']);
        
        //$this->DhairLogging->logArrayContents($patientAssociate, 'patientAssociate');
        
        $this->PatientAssociate->set($patientAssociate);
        //$this->DhairLogging->logArrayContents($patientAssociate, 'patientAssociate');
        
        if ($patientAssociate['Patient']['secret_phrase'] !=
                $this->request->data['Associate']['secret_phrase']){
            $this->Session->setFlash("Sorry, the secret phrase that you entered does not match the patient's secret phrase. Please ask the patient what their current secret phrase is.");
            //$this->log('secret phrase doesnt match.' , LOG_DEBUG );
        }
        else {
            $this->PatientAssociate->saveField(
                                        'has_entered_secret_phrase', true);
            $this->Session->setFlash(
                                "You may now view that patient's reports.");
        }
        $this->redirect("/results/others");
    }

}
?>
