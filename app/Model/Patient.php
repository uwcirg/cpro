<?php

/** 
    * Patient class
    *
    * Store data related to an actual patient in the survey, who may log in
    * by him/herself as a user, or may be logged in by a research/clinic user
    *
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

//require_once(APPLIBS . 'PatientProjectState.php');

class Patient extends AppModel {

    public $_schema = array(
        'external_study_id' => array(
            'type' => 'string',
            'length' => 10
        ),
    );

    var $hasMany = array(
            'SurveySession' => array('dependent' => true), 
            'Consent' => array('dependent' => true), 
            'Note' => array('order' => 'Note.created DESC',
                            'dependent' => true),
            'PatientViewNote' => 
                array('order' => 'PatientViewNote.lastmod DESC',
                        'dependent' => true),
            "Appointment" => 
                array('order' => "Appointment.datetime ASC", 
                       'dependent' => 'true')
            );

    var $hasOne = array(
        'PatientExtension' => array('dependent' => true)
    );   
 
    var $belongsTo = array(
        'User' => array(
            'className'    => 'User',
            'foreignKey'    => 'id'
        )
    ); 

    // TODO add more fields here... look at PatientsController.checkAndFixForEdit 
    public $validate = array(
        'consent_status' => array(
            'rule' => 'consentStatus',
            'message' => 
                'Consent status can\'t be changed from it\'s current state'),
        'consent_date' => array(
            'rule' => 'consentDatePerStatus',
            'message' => 'consent_status doesn\'t allow editing this')
    );
  
    public function consentStatus($check){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's this->data: " . print_r($this->data, true) . ", and heres check: " . print_r($check, true), LOG_DEBUG);
       
        if (is_array($this->data['Patient']['consent_status'])){

            $consentStatuses = $this->data['Patient']['consent_status'];
            if ($consentStatuses[0] == Patient::PRECONSENT 
                || $consentStatuses[0] == Patient::ELEMENTS_OF_CONSENT){
                return true;
            } 
            else return false;
        } 
        else return true;
    }
 
    public function consentDatePerStatus($check){
        //TODO Invalidate attempts to set consent_date if consent_status 'usual care' or 'pre-consent'
        return true;
    }
 
    /** Value for study_group field that indicates patient is in the treatment
       group */
    const TREATMENT = 'Treatment';

    /** Value for study_group field that indicates patient is in the control
       group */
    const CONTROL = 'Control';

    /** Default / initial consent_status indicates patient has not yet qualified for pre-consent*/
    const USUAL_CARE = 'usual care';

    /** consent_status indicates patient will not be put thru consent process*/
    const OFF_PROJECT = 'off-project';

    /** consent_status indicating that the patient has a FINS score at or above threshold, but has not yet consented or declined to join the study */
    const PRECONSENT = 'pre-consent';

    /** Value for consent_status field that indicates patient has indicated willingness to consent to join the study, but paperwork not done yet */
    const ELEMENTS_OF_CONSENT = 'elements of consent';

    /** Value for consent_status field that indicates patient has consented 
       to join the study */
    const CONSENTED = 'consented';

    /** Value for consent_status field that indicates patient has declined
       to join the study */
    const DECLINED = 'declined';

    var $projectsStates = array();

    var $bindModels = true;

    /**
     *
     */
    function beforeFind($queryData){
        // $this->log(__CLASS__ . "->" . __FUNCTION__ . '('.print_r($queryData, true).')', LOG_DEBUG);

        // Replace erroneous references to fields in Patient model with correct PatientExtension fields
        $replaceMap = array();
        foreach(array_keys((array)$this->PatientExtension->schema()) as $field)
            $replaceMap["Patient.$field"] = "PatientExtension.$field";

        unset($replaceMap[$this->name.'.'.$this->PatientExtension->primaryKey]);
        foreach($queryData as $optionKey => &$optionValue){
            if (is_array($optionValue)){
                foreach($optionValue as $tableField => $value){
                    $replacementField = strtr($tableField, $replaceMap);

                    // for 'order' field
                    if (is_string($value))
                        $value = strtr($value, $replaceMap);
                    // for 'conditions'
                    $optionValue[$replacementField] = $value;
                    if ($replacementField != $tableField)
                        unset($optionValue[$tableField]);
                }
            }
        }

        // if bindModels hasn't been disabled for perf reasons
        if ($this->bindModels and Configure::check('modelsInstallSpecific')){

            $models = Configure::read('modelsInstallSpecific');
            if (in_array('coded_items', $models))
                $this->bindModel(array('hasOne' => array(
                    'AudioFile' => array('dependent' => true),
                    'Chart' => array('dependent' => true)
                )), false);

            if (in_array('journals', $models))
                $this->bindModel(array('hasMany' => array(
                    'JournalEntry' => array('dependent' => true)
                )), false);

            if (in_array('associates', $models))
                $this->bindModel(array('hasAndBelongsToMany' => array(
                    'Associate' => array(
                        'joinTable' => 'patients_associates',
                        'foreign_key' => 'patient_id',// TODO key OK???
                        // 'conditions' => array('Associate.verified = true'),
                        'associationForeignKey' => 'associate_id'
                ))), false);

            if (in_array('activity_entries', $models))
                $this->bindModel(array('hasMany' => array(
                    'ActivityDiaryEntry' => array('dependent' => true)
                )), false);

            if (in_array('medications', $models))
                $this->bindModel(array('hasMany' => array(
                    'Medday' => array('dependent' => true)
                )), false);

        }
        return $queryData;
    }


    /** 
     * Return the allowed values for setting the consent_status field
     * @param patient cakephp data array 
     * @return array, or null if the field should be disabled
     */
    function getConsentStatusSelections($patient) {

        $returnVal;

        if (!defined('STUDY_SYSTEM') || !STUDY_SYSTEM){
            $returnVal = null;
        }
        else {

            $consent_status = $patient["Patient"]['consent_status'];
            if ($consent_status == Patient::PRECONSENT){
                $returnVal = array(self::PRECONSENT => self::PRECONSENT,
                            self::CONSENTED => self::CONSENTED,
		                    self::DECLINED => self::DECLINED);
            }
            elseif ($consent_status == Patient::ELEMENTS_OF_CONSENT){
                $returnVal = array(self::ELEMENTS_OF_CONSENT 
                                        => self::ELEMENTS_OF_CONSENT,
                                    self::CONSENTED => self::CONSENTED,
		                            self::DECLINED => self::DECLINED);
            }
            elseif ($consent_status == Patient::USUAL_CARE){
                $returnVal = array(Patient::USUAL_CARE => Patient::USUAL_CARE,
                                Patient::OFF_PROJECT => Patient::OFF_PROJECT);
            }
            else $returnVal = array($consent_status);
        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), returning: " . print_r($returnVal, true), LOG_DEBUG);
        return $returnVal;
    }

    /** Value for the user_type field */
    const HOME = 'Home/Independent';

    /** Value for the user_type field */
    const CLINIC = 'Clinic/Assisted';

    /** 
      Return the allowed values for the user_type field 
      This should also be a class constant
     */
    function getUserTypes() {
       return array(self::HOME => self::HOME,
                    self::CLINIC => self::CLINIC);
    }

    /** Value for clinical_service field */
    const MEDONC = 'MedOnc';

    /** Value for clinical_service field */
    const RADONC = 'RadOnc';

    /** Value for clinical_service field */
    const TRANSPLANT = 'Transplant';

    /** Value for clinical_service field */
    const SURGERY = 'Surgery';

    /** 
      Return the allowed values for the clinical_service field 
      This should also be a class constant
     */
    function getClinicalServices() {
       return array(self::MEDONC => self::MEDONC);
    }

    function firstSession($patient_id) {
        return $this->sessions($patient_id) < 1;
    }

    function sessions($patient_id) {
        $sessions = $this->query(
         "SELECT count( * ) AS count
             FROM `patients`
             LEFT JOIN `survey_sessions` ON `patients`.`id` = `survey_sessions`.`patient_id`
             AND `patient_id` = $patient_id");
        return $sessions[0][0]["count"];
    }


    function finishedSurveySessions($patient_id) {
        $patient = $this->findById($patient_id);
		$sessions = $patient["SurveySession"];
		
		return $this->SurveySession->filterFinished($sessions);
    }

    function isParticipant($patient_id){
        $patient = $this->findById($patient_id);
        return ($patient['Patient']['consent_status'] 
                    == self::CONSENTED);
    }


    /**
     *
     */
    function randomize($test_flag){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        return self::TREATMENT;
    }

    /**
     * Consent patent and randomize to a study group based on their user 
     * type and whether they are a test patient
     * @param patientId Id of the patient
     * @param userType User type (see getUserTypes)
     * @param test_flag Is the user a test patient
     */
    function setToParticipantAndRandomize($patientId, $userType, $test_flag) {
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $this->id = $patientId;
	    $success = false;
        $studyGroup = null;

        while (empty($success)) {
	        // for optimistic concurrency:  read highest consent id
    	    $nextConsentId = $this->Consent->getLastId() + 1;

            $studyGroup = $this->randomize($test_flag);

            $this->data['Patient']['id'] = $patientId;
	        $this->data['Patient']['study_group'] = $studyGroup;
	        $this->data['Consent'][0]['id'] = $nextConsentId;
            $this->data['Consent'][0]['patient_id'] = $patientId;

            /* Optimistic concurrency:  Consent.id is unique, so this 
	       should fail if someone else added a consenting patient after 
	       we read the highest consent id */
	        $success = $this->saveAll($this->data);
        }

        /* change existing acl_alias from aclPatient to 
	   aclParticipantControl/Treatment */
        $this->swapPatientsAclLeaf(
                        $patientId, 
                        'aclPatient', 
                        'aclParticipant' . $studyGroup);
    }

    function setConsentStatus($patientId, $studyGroup){
        
        $this->data['Patient']['study_group'] = $studyGroup;
        $this->data['Patient']['id'] = $patientId;

        $this->id = $patientId;
        $this->swapPatientsAclLeaf(
            $patientId, 
            'aclPatient', 
            'aclParticipant' . $studyGroup
        );    
    }

    /** 
     * Check whether an appointment-based survey session can be started or continued right now.
     * Applies time-based rules.
     * @param patient Patient to check
     * @return The ID of the appointment, or NULL if no session is allowed per time=based rules
     */
    function appt_for_session_init_or_resume($patient) {

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patient), here's patient: " . print_r($patient, true), LOG_DEBUG);


//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patient_id), returning null because no appts matched time criteria", LOG_DEBUG);
        return;

    } // function appt_for_session_init_or_resume($patient) {

    /** 
     * Given a patient, determine what survey session to administer.
     * @param $patient a cakephp data arrayy
     */
     function analyzeCurrentApptAndSessionState($patient) {

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); here's the patient arg:" . print_r($patient, true), LOG_DEBUG);

        if (empty($patient)) {   // not a patient
//            $this->log( __CLASS__ . "." . __FUNCTION__ . "(); patient empty so returning NULL", LOG_DEBUG);
            return;
        }
            
        $patientId = $patient['Patient']['id'];

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); for patient $patientId, stack trace: " . Debugger::trace(), LOG_DEBUG);

        $thisProject = ClassRegistry::init('Project');
        $projects = $thisProject->find('all', array('recursive' => -1));

        foreach ($projects as $project){

            $projectId = $project['Project']['id'];
            $this->projectsStates[$projectId]
                = new PatientProjectState($patientId, $project);

            // eg eligibility_session_rules, simple_session_rules, interval_based_session_rules
            if (!isset($project['Project']['session_rules_fxn'])) 
                $project['Project']['session_rules_fxn'] = 'simple';
            call_user_func(array($this, $project['Project']['session_rules_fxn']
                    . '_session_rules'), $patient, $project); 

            if ($project['Project']['elective_sessions']
                && !$this->projectsStates[$projectId]->apptForResumableSession
                && !$this->projectsStates[$projectId]->apptForNewSession){
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); !apptForResumableSession && !apptForNewSession, but ELECTIVE_SESSIONS", LOG_DEBUG);

                $lastSession = $this->lastSession($patientId, $projectId);

                if ($lastSession 
                    && $lastSession['SurveySession']['type'] == ELECTIVE){

                    if ($lastSession['SurveySession']['finished'] != 1)
                        $this->projectsStates[$projectId]->resumableNonApptSession = $lastSession;
                    else 
                        $this->projectsStates[$projectId]->finishedNonApptSession = $lastSession;
                }
                else{
                    $this->projectsStates[$projectId]->initableNonApptSessionType = ELECTIVE; 
                }
            }

            $linkSettings = array('controller' => 'surveys');

            if ($this->projectsStates[$projectId]->resumableNonApptSession){
                $linkSettings += array(
                    'action' => 'restart',
                    $this->projectsStates[$projectId]->resumableNonApptSession['SurveySession']['id']
                );
            }
            else if ($this->projectsStates[$projectId]->initableNonApptSessionType 
                    || $this->projectsStates[$projectId]->apptForNewSession){
                $linkSettings += array(
                    'action' => 'new_session',
                    $projectId
                );
            }
            else if ($this->projectsStates[$projectId]->apptForResumableSession){
                $linkSettings += array(
                    'action' => 'restart',
                    $this->projectsStates[$projectId]->apptForResumableSession['SurveySession']['id']
                );
            }
            $this->projectsStates[$projectId]->sessionLink = $linkSettings;

        }// foreach ($projects as $project){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); projects:" . print_r($projects, true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); projectsStates:" . print_r($this->projectsStates, true), LOG_DEBUG);


//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning at bottom", LOG_DEBUG);
    }// function analyzeCurrentApptAndSessionState($patient) {

    

    /**
     *
     */
    function simple_session_rules($patient, $project){

        $projectId = $project['Project']['id'];

        foreach ($patient['SurveySession'] as $session){
            if ($session['project_id'] == $projectId){

                if ($session['finished'] != 1){
                    $this->projectsStates[$projectId]->resumableNonApptSession
                        = array('SurveySession' => $session); 
                }
                else {
                    $this->projectsStates[$projectId]->finishedNonApptSession
                        = array('SurveySession' => $session); 
                }
                return; 
            }
        }
        $this->projectsStates[$projectId]->initableNonApptSessionType 
            = NON_APPT; 
    }// function simple_session_rules(){


    function elective_session_rules($patient, $project){
        $states = $this->projectsStates[$project['Project']['id']];

        $sessions = Hash::extract(
            $patient,
            "SurveySession.{n}[project_id={$project['Project']['id']}]"
        );

        foreach($sessions as $session){
            // Set the latest finished session only
            if ($session['finished'] and !$states->finishedNonApptSession)
                $states->finishedNonApptSession = array('SurveySession' => $session);
            elseif (!$session['finished'])
                $states->resumableNonApptSession = array('SurveySession' => $session);
        }
        // Only set the next session if there isn't one to resume
        if (!$states->resumableNonApptSession)
            $states->initableNonApptSessionType = ELECTIVE;

        return;
    }

    /**
     *
     */
    function eligibility_session_rules($patient, $project){
        if ($patient['Patient']['eligible_flag'] == null) {
            $this->simple_session_rules($patient, $project);
        }
    }// function eligibilitySessionRules(){

    /**
     *
     */
    function patient_designated_appts_session_rules($patient, $project){
        $this->appointments_relevant_session_rules($patient, $project);
    }// function eligibilitySessionRules(){

    function randomized_session_rules($patient, $project){
        if ($patient['Patient']['study_group'] !== null)
            $this->simple_session_rules($patient, $project);
    }

    /**
     *
     */
    function appointments_relevant_session_rules($patient, $project){
        $projectId = $project['Project']['id'];
        $patientId = $patient['Patient']['id'];
        $timezone = $this->User->getTimeZone($patientId);
        // look at each appt to see whether its datetime permits survey now
        foreach($patient["Appointment"] as $appt) {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(" . $patientId . "); here are some vars: MIN_SECONDS_BETWEEN_APPTS:" . MIN_SECONDS_BETWEEN_APPTS . ". looking at appointment " . $appt['id'], LOG_DEBUG);

            $apptTime = $appt['datetime'];
            $secondsUntilAppt = 
                $this->secondsAfterNow($apptTime, $timezone);
            $session = null;

            // find this appt's session, if any
            foreach ($patient['SurveySession'] as $aSession){
                if ($aSession['appointment_id'] == $appt['id']){
                    $session = $aSession;
                    break;
                }
            }
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(" . $patientId . "); vars for the appt in considerationi (" . $appt['id'] . "): apptTime:" . $apptTime . "; secondsUntilAppt:" . $secondsUntilAppt . ". timezone:" . $timezone, LOG_DEBUG);
 
            if ((($secondsUntilAppt < MIN_SECONDS_BETWEEN_APPTS) 
                 && $this->currentlyBeforeSomeTime(
                        $apptTime, $timezone,
                        $project['Project']['initializable_until']))
                ||
                (($secondsUntilAppt < MIN_SECONDS_BETWEEN_APPTS)
                 && $this->currentlyBeforeSomeTime(
                        $apptTime, $timezone,
                        $project['Project']['resumable_until'])))
            {
                // this appt meets time-based criteria
                //$this->current_session_appt = $appt; 
                //$timezone = parent::getTimeZone($patientId);
                $apptDate = $this->gmtToLocal($appt['datetime'], 
                                        $timezone, false, 'F jS'); 
                $appt['Appointment']['dateUserTZ'] = $apptDate;

                if ($session) {
                    if ($session['finished'] != 1){
                        $this->projectsStates[$projectId]->apptForResumableSession['Appointment'] 
                            = $appt;
                        $this->projectsStates[$projectId]->apptForResumableSession['SurveySession'] 
                            = $session;
                    }
                    else{ 
                        $this->projectsStates[$projectId]->apptForFinishedSession['Appointment'] 
                            = $appt;
                        $this->projectsStates[$projectId]->apptForFinishedSession['SurveySession'] 
                            = $session;
                    }
                }
                else $this->projectsStates[$projectId]->apptForNewSession['Appointment'] = $appt;
                break; // appt iteration
                //return;
            }
            // elseif appt in the past but w/in MIN_SECONDS_BETWEEN_APPTS and finished, set $this->apptForFinishedSession = appt 
            elseif($secondsUntilAppt > (0 - MIN_SECONDS_BETWEEN_APPTS)
                    && $session){
                if ($session['finished'] == 1){
                    $this->projectsStates[$projectId]->apptForFinishedSession['Appointment'] = $appt;
                    $this->projectsStates[$projectId]->apptForFinishedSession['SurveySession'] 
                        = $session;
                }
                else {
                    $this->projectsStates[$projectId]->apptForResumableSession['Appointment'] 
                        = $appt;
                    $this->projectsStates[$projectId]->apptForResumableSession['SurveySession'] 
                        = $session;
                } 
                break; // appt iteration
                //return;
            } 

        } // foreach($patient["Appointment"] as $appt) {

    }// function baselineSessionRules(){

    /**
     *
     */
    function interval_based_session_rules($patient, $project){
        $projectId = $project['Project']['id'];

        $this->_initializeIntervalSessions($patient);

        $this->projectsStates[$projectId]->currentSession = $this->currentWindow;
        $this->projectsStates[$projectId]->nextSession = $this->nextWindow;

        if ($this->currentWindow)
            $this->projectsStates[$projectId]->initableNonApptSessionType = $this->currentWindow['type'];

        $lastSession = $this->lastSession($patient['Patient']['id'], $projectId);
        // $this->log(__CLASS__ . '.' . __FUNCTION__ . '(); here\'s the lastSession:' . print_r($lastSession, true), LOG_DEBUG);

        if ($lastSession){
            if (!$lastSession['SurveySession']['finished'])
                $this->projectsStates[$projectId]->resumableNonApptSession = $lastSession;
            else
                $this->projectsStates[$projectId]->finishedNonApptSession = $lastSession;
        }
    }// function baselineClinicalRules(){

    /**
     * @param $patient a cakephp data arrayy
    */
    function getCurrentIntervalSessionType($patient){
        return null;
    }

    /*
     *
     */
    function lastSession($patientId, $projectId = null) {

        $conditions = array("SurveySession.patient_id" => $patientId);
        if (isset($projectId)){
            $conditions['SurveySession.project_id'] = $projectId;
        } 

        $session = $this->SurveySession->find('first',
                    array(
                        'conditions' => $conditions,
                        'recursive' => -1,
                        'order' => "SurveySession.id DESC"));
        //$this->log("lastSession : " . print_r($session, true), LOG_DEBUG);

        return $session;
    }// function lastSession($id) {


    /** 
      * Try to find a patient with a given MRN at a particular site
      * @param mrn MRN
      * @param clinicId Id of the clinic (used to determine the site)
      * @return A patient that matches, or null if there are none
     */
    function findPatient($mrn, $clinicId) {
        $candidates = $this->findAllByMrn($mrn);

        if (!empty($candidates)) {
            $site1 = $this->User->Clinic->findById($clinicId);

            foreach($candidates as $candidate) {
                $site2 = $this->User->Clinic->findById(
                                            $candidate['User']['clinic_id']);
	            if ($site1['Clinic']['site_id'] == $site2['Clinic']['site_id'])
                {
    	            return $candidate;
                }
            }
        }

        return null;
    }
    
    /**
     * Get a query to find all patients a particular staff member can see
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @param noTest True if test patients should be excluded
     * @param count true if a count should be included
     * @return an array ($select, $join, $whereClause)
     */
    function getAccessiblePatientsQuery3($id, $centralSupport, $researchStaff, 
                                         $noTest = false, $count = false) 
    {
        $id = intval($id);
        $countTxt = '';
        if ($count){$countTxt = "COUNT(DISTINCT(Patient.id)) AS count, ";}
	    $select = 'SELECT '.$countTxt.'User.first_name, User.last_name, 
          Patient.consent_status, Patient.id, Clinic.name, Patient.MRN,
          Patient.check_again_date,
          ' . //Appointment.id, Appointment.patient_id, Appointment.datetime, 
          //Appointment.location, Appointment.staff_id,
          '
          Patient.off_study_status,
          Patient.off_study_timestamp, Patient.off_study_reason,
          Patient.consenter_id, Patient.clinical_service,
          PatientExtension.*,
          User.email, User.username, Patient.phone1, Patient.phone2';
	    $fromAndJoin = 
            ' FROM patients AS Patient' 
            . ' LEFT JOIN patient_extensions AS PatientExtension ON (PatientExtension.patient_id = Patient.id),'
            . ' clinics AS Clinic, users AS User';
            //' appointments AS Appointment';
	    $whereClause = 
            ' WHERE User.id = Patient.id AND Clinic.id = User.clinic_id';
            //' AND Appointment.patient_id = Patient.id';

        if ($noTest) {
	        $whereClause .= ' AND Patient.test_flag <> 1';
        }

        // above query works for centralSupport as is
        if ($centralSupport) {
        } else if ($researchStaff) {
	        // researchStaff needs to add restriction that site ids match
            $fromAndJoin .= ' JOIN clinics AS clinics2, users AS users2';
            $whereClause .= " AND clinics2.id = users2.clinic_id
                            AND Clinic.site_id = clinics2.site_id
                            AND users2.id = $id";
        } else {
	    // everyone else needs to add restriction that clinic ids match
            $fromAndJoin .= ' JOIN users AS users2';
            $whereClause .= " AND User.clinic_id = users2.clinic_id
	                      AND users2.id = $id";
        }

	    return array($select, $fromAndJoin, $whereClause);
    } // function getAccessiblePatientsQuery3

    /**
     * Get the first part (select + join) and second part (where clause) 
     * of the query to find all patients a particular staff member can
     * see
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @param noTest True if test patients should not be included
     * @param count true if a count should be included
     * @return an array ($selectAndJoin, $whereClause)
     */
    function getAccessiblePatientsQuery($id, $centralSupport, $researchStaff, 
                                        $noTest = false, $count = false) 
    {
        $query = $this->getAccessiblePatientsQuery3($id, $centralSupport, 
	                                            $researchStaff, $noTest, 
                                                $count);
        return array($query[0] . $query[1], $query[2]);
    }

    /**
     * Return all patients a particular staff member can see
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @return all patients the staff member can see, in an array
     */
    function findAccessiblePatients($id, $centralSupport, $researchStaff) {
        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff);
        $patients = $this->query($query[0] . $query[1]);

        $thisProject = ClassRegistry::init('Project');
        $allProjects = 
            $thisProject->find('all', 
                            array('recursive' => -1));
        // re-key to Project.id
        $allProjects = Hash::combine($allProjects, '{n}.Project.id', '{n}');
//        $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), allProjects: ' . print_r($allProjects, true), LOG_DEBUG);

        $currentGmt = $this->currentGmt();

        // retrieve appts and add to data model
        foreach ($patients as &$patient){

            $appt = $this->Appointment->find('first', array(
                'conditions' => array(
                    'Appointment.patient_id' => $patient['Patient']['id'],
                    'Appointment.datetime > "' . $currentGmt . '"'
                ),
                'order' => 'Appointment.datetime DESC',
                'recursive' => -1));
            if ($appt) {
                $patient['Patient']['next_appt_dt'] 
                            = $appt['Appointment']['datetime'];
//                $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found upcoming appt for patient id ' . $patient['Patient']['id'] . ': ' . print_r($appt, true), LOG_DEBUG);
            }
            else $patient['Patient']['next_appt_dt'] = '';

            $patient['SurveySession'] = array();
            $patient['SurveySession']['last_session_proj'] = '(no session)';
            $patient['SurveySession']['last_session_date'] = '(no session)';
            $patient['SurveySession']['last_session_status'] = '(no session)';

            $lastSession = $this->SurveySession->find('first', array(
                'conditions' => array(
                    'SurveySession.patient_id' => $patient['Patient']['id']),
                'order' => 'SurveySession.id DESC',
                'recursive' => -1));
//            $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), lastSession for patient id ' . $patient['Patient']['id'] . ': ' . print_r($lastSession, true), LOG_DEBUG);

            if (!$lastSession)
                continue;

            $lastSession = $lastSession['SurveySession'];

            $lastSessionsProjectId = $lastSession['project_id'];
//            $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), lastSessionsProjectId for patient id ' . $patient['Patient']['id'] . ': ' . print_r($lastSessionsProjectId, true), LOG_DEBUG);

            $patient['SurveySession']['last_session_proj'] 
                = $allProjects[$lastSessionsProjectId]['Project']['Title'];
            $patient['SurveySession']['last_session_date'] 
                = $lastSession['reportable_datetime'];
            
            if ($lastSession['finished'] == 1)
                $patient['SurveySession']['last_session_status'] = 'finished';
            elseif ($lastSession['partial_finalization'] == 1)
                $patient['SurveySession']['last_session_status'] 
                    = 'partially finished';
            else
                $patient['SurveySession']['last_session_status'] = 'in process';

        }// foreach ($patients as &$patient){

        return $patients;
        //return $this->afterFind($patients); // not needed for the fields the calling fxn cares about
    }// function findAccessiblePatients()

    /**
      * Check that a date or datetime is in the proper 
      * format
      * @param date date or datetime
      * @return whether the date/datetime is in the proper format
      */
    private function checkDate($date) {
        // date/time
	$datetimePattern1 = '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/';
        // date
	$datetimePattern2 = '/^\d\d\d\d-\d\d-\d\d$/';
        return (preg_match($datetimePattern1, $date) ||
                preg_match($datetimePattern2, $date));
    }

    /**
      * Check that a start and end dates or datetimes are in the proper 
      * format
      * @param startdate Start date or datetime
      * @param enddate End date or date/time
      * @return whether the two parameters are in the proper format
      */
    private function checkDates($startdate, $enddate) {
        return $this->checkDate($startdate) && $this->checkDate($enddate);
    }

    /**
     * Return all patients a particular staff member can see that
     * have a check again date in a particular date range.
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @param startdate Start of the datetime range
     * @param enddate End of the datetime range
     * @return all patients the staff member can see whose check-again date
     *    is in the datetime range, in an array
     */
    function findCheckAgains($id, $centralSupport, $researchStaff, $startdate,
                             $enddate) 
    {
        if (!$this->checkDates($startdate, $enddate)) {
	    $this->log(
	        "findCheckAgains: bad date parameters $startdate $enddate");
            return array();
        }

        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff);
        $checkAgainWhere = " AND Patient.check_again_date >= '$startdate'
	                     AND Patient.check_again_date <= '$enddate'
                         AND Patient.no_more_check_agains != 1";
        $orderBy = " ORDER BY Patient.check_again_date";
	                   
        $results = $this->query($query[0] . $query[1] . $checkAgainWhere .
	                        $orderBy);
        return $this->afterFind($results);
    }

    /**
     * Return all participants a particular staff member can see that
     * have no check again date after a particular date
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @param startdate Start of the datetime range
     * @return all patients the staff member can see who don't have a 
     *    check-again date after the startdate, in an array
     */
    function findNoCheckAgain($id, $centralSupport, $researchStaff, $startdate) {
        if (!$this->checkDate($startdate)) {
	    $this->log(
	        "findNoCheckAgain: bad date parameter $startdate");
            return array();
        }

        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff);
        $noCheckAgainWhere = " AND (Patient.check_again_date < '$startdate'
	                            OR Patient.check_again_date is null)
			       AND Patient.study_group is not null 
			       AND Patient.no_more_check_agains <> 1";
        $orderBy = " ORDER BY User.last_name";
	                   
        $results = $this->query($query[0] . $query[1] . $noCheckAgainWhere .
	                        $orderBy);
        return $this->afterFind($results);
    }

    /**
     * Return all patients a particular staff member can see that
     * expressed interest in participating, but who have not yet
     * consented or declined
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @return all patients the staff member can see who have expressed interest in participating, but who have not yet consented or declined, in an array
     */
    function findInterested($id, $centralSupport, $researchStaff) 
    {
        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff);
        $interestedWhere = " AND Patient.study_participation_flag = 1
	                     AND Patient.consent_status= 'pre-consent'";
	                   
        $results = $this->query($query[0] . $query[1] . $interestedWhere);
        return $this->afterFind($results);
    }

    /**
     * Return all off-study patients a particular staff member can see 
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @return all off-study patients the staff member can see 
     */
    function findOffStudy($id, $centralSupport, $researchStaff) 
    {
        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff, true);
        $offStudyWhere = " AND Patient.off_study_status is not NULL";
	                   
        $results = $this->query($query[0] . $query[1] . $offStudyWhere);
        return $this->afterFind($results);
    }

    /**
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @return array of off-study reason counts 
     */
    function countOffStudyEnums($id, $centralSupport, $researchStaff) 
    {
        $offStudyEnums = $this->getEnumValues('off_study_status');

        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff, true, true);
        
        $offStudyEnumsCount = array();
        foreach($offStudyEnums as $enum){
            $offStudyWhere = " AND Patient.off_study_status = '" . $enum . 
                "' GROUP BY Patient.off_study_status";

            $results = $this->query($query[0] . $query[1] . $offStudyWhere);
       
            if (isset($results[0][0]['count']))
                $offStudyEnumsCount[$enum] = $results[0][0]['count'];
 
            //$this->log('results = ' . print_r($results, true), LOG_DEBUG);

        }
        
        
        return $offStudyEnumsCount;

        /**
        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff, true);
        $offStudyWhere = " AND Patient.off_study_status is not NULL";
	                   
        $results = $this->query($query[0] . $query[1] . $offStudyWhere);
        return $this->afterFind($results);
        */
    }

    /**
     * Return all patients a particular staff member can see that
     * match a search criteria
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @param searchParams patient fields to match
     * @return all patients the staff member can see who match the search criteria
     */
    function search($id, $centralSupport, $researchStaff, $searchParams) 
    {
        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff);
        $searchWhere = '';
       
        foreach($searchParams['User'] as $paramName => $paramVal){
            if ($paramVal != ''){
                $searchWhere .= " AND User.$paramName LIKE '%$paramVal%'";
            }
        }
        foreach($searchParams['Patient'] as $paramName => $paramVal){
            if ($paramVal != ''){
                $searchWhere .= " AND Patient.$paramName LIKE '%$paramVal%'";
            }
        }
        $phone = $searchParams['Phone']['phone'];
        if ($phone != ''){
            $searchWhere .= " AND (Patient.phone1 LIKE '%$phone%' OR "
                        . "Patient.phone2 LIKE '%$phone%')";
        }
	                   
        if ($searchWhere == ''){
            return array();
        }
        $results = $this->query($query[0] . $query[1] . $searchWhere);
        return $this->afterFind($results);
    }

    /**
     * Get timezone for a patient during a save operation
     * @param data passed in data
     */
    function getTimeZone($data) {
        /* try to get timezone based on clinic_id in the data 
	   (in case it was just changed) */
	if (!empty($data['User'])) {
            $timezone = $this->User->Clinic->getTimeZone(
	        $data['User']['clinic_id']);
        }
	
	/* if that doesn't work, get it from the clinic_id in the db */
	if (empty($timezone)) {
            //$timezone = $this->User->getTimeZone($data['Patient']['id']);
            $timezone = parent::getTimeZone($data['Patient']['id']);
        }

	return $timezone;
    }


    /**
    *  Add Clinic data to find results, since Clinic is associated with User, not Patient
    */
    function addClinicToFindResult(&$data){

        $clinic = $this->User->Clinic->find('first',
                    array('recursive' => -1,
                        'conditions' =>
                        array('Clinic.id' => 
                            $data['User']['clinic_id'])));

        $data['Clinic'] = $clinic['Clinic'];
    }


    function updateSecretPhrase($patient, $phrase) {
        $phrase = strip_tags($phrase);
        $patient = $this->getRecord($patient);
        $patient["Patient"]["secret_phrase"] = $phrase;
        $this->save($patient);
        return $patient;
    }

    /*
     * Callback function to get a site id from a row of a db query
     * @param row Array containing the row
     * @return The site id from the row
     */
    private function getSiteId($row) {
        return $row['sites']['id'];
    }

    /**
     * Callback function to get site data from a row of a db query
     * @param row Array containing the row
     * @return The entire row
     */
    private function getSiteData($row) {
        return $row;
    }


    /** Value for patientType parameter indicating we only want 
        non-participants */
    const PATIENT = 'Patient';

    /** Value for patientType parameter indicating we only want participants */
    const PARTICIPANT = 'Participant';

    /** Value for the off_study_status field */
    const ON_STUDY = 'On study';

    /** Value for the off_study_status field */
    const COMPLETED = 'Completed all study requirements';

    /** Value for the off_study_status field */
    const OSINELIGIBLE = 'Ineligible';

    /** Value for the off_study_status field */
    const WITHDRAWN = 'Voluntary withdrawal';

    /** Value for the off_study_status field */
    const LOST = 'Lost to follow-up';

    /** Value for the off_study_status field */
    const ADVERSE_EVENTS = 'Adverse events';

    /** Value for the off_study_status field */
    const OTHER = 'Other';

    /** 
      Return the allowed values for the off_study_status field 
      This should be a class constant
     */
    function getOffStudyStatuses() {
       return array(self::ON_STUDY => self::ON_STUDY,
                    self::COMPLETED => self::COMPLETED,
                    self::OSINELIGIBLE => self::OSINELIGIBLE,
                    self::WITHDRAWN => self::WITHDRAWN,
                    self::LOST => self::LOST,
                    self::ADVERSE_EVENTS => self::ADVERSE_EVENTS,
                    self::OTHER => self::OTHER);
    }

    /**
     * Get a basic count query for one of the accrual functions
     * @param startdate Start of the month to check, as a Unix timestamp
     * @param siteId Id of the site
     * @param table Name of the table whose foreign key is the patient_id
     * @param timeField Name of the field with the timestamp we need to check
     */
    private function basicCountQuery($startdate, $siteId, $table, $timeField) {
        $startString = gmdate(MYSQL_DATETIME_FORMAT, $startdate);
        $oneMonthLater = gmdate(MYSQL_DATETIME_FORMAT, 
	                        strtotime('+1 month', $startdate));
        return "SELECT count(DISTINCT(patients.id)) AS count
                FROM patients
                JOIN users, clinics " . 
               // join with $table unless it is 'patients'
               ($table == 'patients' ? 
                "WHERE " : 
                ", $table WHERE patients.id = $table.patient_id AND ") .
	        "   patients.id = users.id
	        AND users.clinic_id = clinics.id
	        AND clinics.site_id = $siteId
		AND patients.test_flag <> 1
	        AND $table.$timeField >= '$startString'
	        AND $table.$timeField < '$oneMonthLater'";
    }

    /**
     * Get the number of patients who went off-study during a given month at a 
     *   given site
     * @param startdate Start of the month to check, as a Unix timestamp
     * @param siteId Id of the site
     * @return the number of patients who went off-study
     */
    function countOffStudy($startdate, $siteId) {
        $query = $this->basicCountQuery($startdate, $siteId, 'patients', 
                                        'off_study_timestamp');
        $query .= " AND patients.off_study_status IS NOT NULL 
	            AND patients.off_study_status <> ''";
        $patients = $this->query($query);
        return $patients[0][0]['count'];
    }

    /**
     * Return all patients a particular staff member can see that are
     * consented but whose consent has not been checked (verified)
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @return all patients the staff member can see that are consented
     *    but whose consent has not been verified
     */
    function findUncheckedConsents($id, $centralSupport) {
        $query = $this->getAccessiblePatientsQuery3($id, $centralSupport, true);
        $select = ", Patient.consent_checked, Patient.hipaa_consent_checked, 
                   Clinic.site_id";
        $hipaaSiteClause = '';
        if (defined('HIPAA_CONSENT_SITE_ID')){
            $hipaaSiteClause = " OR (Patient.hipaa_consent_checked != 1" . 
                    " AND Clinic.site_id = " . HIPAA_CONSENT_SITE_ID . ")";
        }
        $uncheckedConsents = " AND Patient.consent_status = '" . 
	                           self::CONSENTED . "'
	                     AND (Patient.consent_checked != 1" 
                             . $hipaaSiteClause . ")";
        $orderBy = " ORDER BY User.last_name";
	                   
        $results = $this->query($query[0] . $select . $query[1] . $query[2] .
                                $uncheckedConsents . $orderBy);
        return $this->afterFind($results);
    }

    // change T-times to GMT before save
    function beforeSave($options = Array()) {
//        $this->log(__FUNCTION__.'(), this->data: '.print_r($this->data, true), LOG_DEBUG);
        if (!empty($this->data['Patient']['off_study_timestamp'])) {
            $timezone = $this->getTimeZone($this->data);
            $this->data['Patient']['off_study_timestamp'] = 
	        $this->localToGmt(
		    $this->data['Patient']['off_study_timestamp'], $timezone);
        }

        if (!empty($this->data['Patient']['consent_status'])
            && $this->data['Patient']['consent_status'] == self::CONSENTED) {
            $this->data['Patient']['off_study_status'] = self::ON_STUDY; 
        }

        // Check if we have PatientExtension data
        if (isset($this->data['PatientExtension'])){

            // Get the PatientExtension subset of data from Patient
            $intersect = array_intersect_key(
                $this->data['Patient'],
                $this->data['PatientExtension']
            );

            // Add back in patient_id that would be missing from intersect
            $intersect['patient_id'] = $this->data['Patient']['id'];

            ksort($intersect);
            ksort($this->data['PatientExtension']);

            // Check if the data we previously added to the Patient array during Patient->afterFind() has been changed
            if ($intersect != $this->data['PatientExtension'])
                $this->PatientExtension->save($intersect);
        }
        else{
            $patient_ext_keys =  array_diff(
                array_keys($this->data['Patient']),
                // It may make sense to define and use $this->_schema instead
                array_keys($this->schema())
            );
            if ($patient_ext_keys){
                $patient_ext = array_intersect_key(
                    $this->data['Patient'],
                    array_flip($patient_ext_keys)
                );
                $patient_ext['patient_id'] = $this->data['Patient']['id'];
                $this->PatientExtension->save($patient_ext);
            }
        }

        return true;
    }// function beforeSave

    // change T-times back to local time after save
    function afterSave($created) {

        if (!empty($this->data['Patient']['off_study_timestamp'])) {
            $timezone = $this->getTimeZone($this->data);
            $this->data['Patient']['off_study_timestamp'] = 
	        $this->gmtToLocal(
		    $this->data['Patient']['off_study_timestamp'], $timezone);
        }

        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '(), heres all session data before deleting the patient from it: ' . print_r($_SESSION, true), LOG_DEBUG);
        // Delete the patient session var so it's requeried at next request
        //unset($_SESSION['patient' . $this->data['Patient']['id']]);
        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '(), heres session data after deleting the patient from it: ' . print_r($_SESSION, true), LOG_DEBUG);

        return true;
    }// function afterSave($created) {

    function afterFind($results, $primary=false) {
        $results = parent::afterFind($results, $primary);
        if (array_key_exists(0, $results) and is_array($results[0])) {
            foreach ($results as $key => &$val) {
                // change T-times to local time after retrieved
                // If there is no patient id (e.g., find('count')), timezone conversion is irrelevant
                if (!empty($val['Patient']) && !empty($val['Patient']['id'])) {
                        $timezone = parent::getTimeZone($val['Patient']['id']);

                        if (isset($val['Patient']['off_study_timestamp']))
                            $results[$key]['Patient']['off_study_timestamp'] =
                                $this->gmtToLocal(
                                    $val['Patient']['off_study_timestamp'], $timezone
                                );
                }
            }
        }
        return $results;
    }

    /**
     *
     */
    function next_page_clicked($pageId, $patient){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning", LOG_DEBUG);
        return;
    }

    /*
     *
     */
    function swapPatientsAclLeaf($patientID, $fromLeaf, $toLeaf){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for patientID $patientID fromLeaf:$fromLeaf and toLeaf:$toLeaf", LOG_DEBUG);

        $this->User->swapUsersAclLeaf($patientID, $fromLeaf, $toLeaf);  
 
        App::import('Component', 'SessionComponent');
        $authd_user_id = SessionComponent::read('Auth.User.id');
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for patientID $patientID, and authd_user_id from session = " . $authd_user_id, LOG_DEBUG);
        if ($patientID == $authd_user_id){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), before deleting CONTROLLERS_ACTIONS_AUTHZN, heres a check on it: " . SessionComponent::check(CONTROLLERS_ACTIONS_AUTHZN), LOG_DEBUG);
            SessionComponent::delete(CONTROLLERS_ACTIONS_AUTHZN);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), deleted CONTROLLERS_ACTIONS_AUTHZN, heres a check on it: " . SessionComponent::check(CONTROLLERS_ACTIONS_AUTHZN), LOG_DEBUG);
        }  
//        else $this->log(__CLASS__ . "." . __FUNCTION__ . "(), NOT deleting CONTROLLERS_ACTIONS_AUTHZN", LOG_DEBUG);

    }// function swapPatientsAclLeaf($patientID, $fromLeaf, $toLeaf){

    /**
     * List of email templates that staff should be able to send to the patient from patients/edit, filtered by the patient's current state.
     *  @param $userPatient User and Patient data
     *  @return array of permitted filenames from Views/Emails/html, keyed by friendly text.
     */
    function getEmailTemplateList($patient){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), patient:" . print_r($patient, true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), patient projectsStates:" . print_r($this->projectsStates, true), LOG_DEBUG);

        $emailTemplates = array();

        if ($patient['User']['email'] &&
            (!defined('ELIGIBILITY_WORKFLOW') || !ELIGIBILITY_WORKFLOW)
                ||
            (ELIGIBILITY_WORKFLOW && 
                ($patient['Patient']['eligible_flag'] == '1')))
        {
            if (
                PATIENT_SELF_REGISTRATION and
                $patient['User']['email'] and
                $patient['User']['first_name'] and
                $patient['User']['last_name'] and
                $patient['Patient']['birthdate'] 
            ){

                $sendable = !$patient['User']['registered'];

                $emailTemplates += array(
                    'self_register' => 
                        array('text' => 
                                'Registration Invitation for ' . SHORT_TITLE,
                            'sendable' => $sendable),
                    'registration_reminder' => 
                        array('text' => 
                                'Registration Reminder for ' . SHORT_TITLE,
                            'sendable' => $sendable),
                );
            }
            foreach($this->projectsStates as $projId => $projectState){

                if (isset(
                        $projectState->project['Project']['email_reminder'])
                    && isset(
                        $projectState->sessionLink['action'])){

                    $templateFileName
                        = CProUtils::getInstanceSpecificEmailName(
                            $projectState->project['Project']['email_reminder']);
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), templateFileName for proj $projId: " . $templateFileName, LOG_DEBUG);
                    if (isset($templateFileName)){
                        $emailTemplates[$templateFileName]
                            = array('text' => 
                                    $projectState->project['Project']['Title'] . " reminder",
                                'sendable' => true);
                            
                    }
                }

            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), returning: " . print_r($emailTemplates, true), LOG_DEBUG);
        return $emailTemplates;
    } // function getEmailTemplateList

    /**
     * Return all eligible, consented participants that a particular staff 
     * member can see that have $fieldName within a date range
     * @param $staff_id id of the staff member
     * @param $centralSupport True if they are CentralSupport
     * @param $researchStaff True if they are research staff
     * @param $fieldName eg "Patient.birthdate", or "PatientExtension.primary_randomization_date"
     * @param $startdate Start of the datetime range (optional, if not included no lower limit applied)
     * @param $enddate End of the datetime range (optional, if not included no end date limit applied)
     * @param $exclude Array of patient id's to exclude
     * @return all patients the staff member can see whose randomization date
     *    is in the datetime range, in an array
     */
    function findWFieldInDateRange($staff_id, $centralSupport, $researchStaff, 
                    $fieldName, $startdate = null, $enddate = null, $exclude) 
    {

        $query = $this->getAccessiblePatientsQuery($staff_id, $centralSupport, 
	                                           $researchStaff);
        $findNMonthFuWhere = '';

        if (isset($startdate)) 
            $findNMonthFuWhere 
                .= (' AND ' . $fieldName . ' > \'' 
                    . $startdate->format(MYSQL_DATETIME_FORMAT) . '\'');

        if (isset($enddate)) 
            $findNMonthFuWhere 
                .= (' AND ' . $fieldName . ' < \'' 
                    . $enddate->format(MYSQL_DATETIME_FORMAT) . '\'');

        if (sizeof($exclude) > 0){
            $findNMonthFuWhere 
                .= ' AND Patient.id NOT IN (' . implode($exclude, ',') . ')';
        }

        $requireActiveParticipants = ' AND Patient.off_study_status = \'' . self::ON_STUDY . '\' AND Patient.eligible_flag <> \'0\'';

        $orderBy = " ORDER BY " . $fieldName . " ASC";

        $results = $this->query($query[0] . $query[1] 
                                . $requireActiveParticipants 
                                . $findNMonthFuWhere . $orderBy);
        return $this->afterFind($results);
    }

    /**
     * Retrieve patients for the P3P one week f/u report 
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param researchStaff True if they are research staff
     * @param days Max number of days in the past the most recent appt can be
     * @return all patients the staff member can see, in an array
     */
    function oneWeekFollowup($id, $centralSupport, 
        $researchStaff, $days = null) {

        $query = $this->getAccessiblePatientsQuery($id, $centralSupport, 
	                                           $researchStaff);
        $patients = $this->query($query[0] . $query[1] . ' AND PatientExtension.wtp_status IS NULL');

        $GMT = new DateTimeZone('GMT');

        $windowClose = new DateTime('now', $GMT);
        $windowClose->sub(
            new DateInterval('P'. $days .'D'));

        $sixDays = new DateInterval('P6D');
        // note that we want to look 14 days after the appt, but since DateTime->add acts on the object, we only need to add 8 more days here.
        $eightDays = new DateInterval('P8D');

        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found patients ' . print_r($patients, true), LOG_DEBUG);

        // retrieve appts and add to data model
        foreach ($patients as $key => &$patient){

            $appt = $this->Appointment->find('first', array(
                'conditions' => array(
                    'Appointment.patient_id' => $patient['Patient']['id'],
                    'Appointment.datetime < "' . $windowClose->format(MYSQL_DATETIME_FORMAT) . '"'
                ),
                'order' => 'Appointment.datetime DESC',
                'limit' => '1',
                'recursive' => -1));
            if ($appt) {
                $timezone = new DateTimeZone(
                    parent::getTimeZone($patient['Patient']['id']));

                $apptDateTime = new DateTime(
                    $appt['Appointment']['datetime'], $GMT);

                $apptDateTime->setTimeZone($timezone);

                $apptDateTime->add($sixDays);
                $patient['Patient']['window_open'] = $apptDateTime->format('m/j/Y'); 
                $apptDateTime->add($eightDays);
                $patient['Patient']['window_close'] = $apptDateTime->format('m/j/Y'); 
                $patient['Patient']['appt_dt'] = $appt['Appointment']['datetime'];
//                $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found past appt for patient id ' . $patient['Patient']['id'], LOG_DEBUG);
//                $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found upcoming appt for patient id ' . $patient['Patient']['id'] . ': ' . print_r($appt, true), LOG_DEBUG);
            }
            else unset($patients[$key]);
        }
        return $this->afterFind($patients);
    }// function oneWeekFollowup

} // class Patient extends AppModel

