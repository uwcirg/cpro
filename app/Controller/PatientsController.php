<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
App::uses('CakeEmail', 'Network/Email');
class PatientsController extends AppController
{
    var $uses = array('User', 'UserAclLeaf', 'Clinic', 'SurveySession', 'Note',
        'PatientViewNote', 'Answer', 'Site', 'Target', 'Appointment', 'Webkey',
        'Project', 'PatientExtension');
    var $components = array('Acl', 'DhairDateTime', 'Password');
    var $helpers = array('Html', 'Form', 'Csv', 'Cache');

    // needed for pdf generation
    var $cacheAction = array(
        // 900 = 15 min
        'callbacks' => true
    );

    /** Status that indicates basic data is missing */
    const MISSING_DATA = 'missing data';

    /** Status that indicates a patient exists with the same basic info */
    const PATIENT_EXISTS = 'patient exists';

    /** Status that indicates a username already exists */
    const USERNAME_EXISTS = 'username exists';

    /** Status that indicates a username needs to be approved */
    const NEW_USERNAME = 'new username';

    /** Status that indicates a patient has consented without specifying
        a user type */
    const BAD_USERTYPE = 'bad user type';

    /** Status that indicates patient's appointment times too close together */
    const BAD_APPOINTMENT_TIMES = 'bad appointment times';

    /** Status that indicates there is no T1 for the patient */
    const NO_T1 = 'no t1';

    /** holds the old default timezone */
    private $oldTimeZone;

    private $centralSupport;
    private $researchStaff;

    // Override this with Config 'PATIENT_FIELDS_ORDERED'
    private $fieldsInOrder = array(
                'User.first_name', 'User.last_name', 'User.username',
                'User.clinic_id', 
                'Patient.MRN', 'Patient.birthdate', 
                'Patient.test_flag', 'Patient.phone1', 'Patient.phone2',
                'User.email',
                'Patient.mailing_address', 
                'Patient.user_type',
                'Patient.clinical_service',
                'Patient.treatment_start_date'
    );

    /** Default fields for a new patient.  Override with Config 
     *  'NEW_PATIENT_FIELDS_ORDERED'
     */
    private $newFieldsInOrder = array(
        'User.first_name' => true, 
        'User.last_name' => true,
        'Patient.birthdate' => true,
        'Patient.MRN' => true,
        'Patient.test_flag' => false,
        'Appointment.0' => true
    );

/** TODO remove this, should be done at a higher level eg STUDY_SYSTEM & MANUAL_RANDOMIZATION
    private $studyFields = array(
        'Patient.consent_status',
        'Patient.consent_date',
        'Patient.consenter_id',
        'Patient.consent_checked',
        'Patient.off_study_status',
        'Patient.off_study_reason',
        'Patient.off_study_timestamp',
        'Patient.decline_reason',
        'Patient.eligible_flag',
        'Patient.hipaa_consent_checked',
        'Patient.study_group'
    );
*/

    private $checkAgainFields = array(
        'Patient.check_again_date', 'Patient.no_more_check_agains'
    );

    function __construct($request = null, $response = null){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        parent::__construct($request, $response);
    }

    /**
     *
     */
    function constructClasses(){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()" , LOG_DEBUG);
        if (in_array("medications", 
                $this->modelsForThisDhairInstance)) {
            $this->uses[] = "Medication";
            $this->uses[] = "Medday";
            $this->uses[] = "MeddayMedication";
            $this->uses[] = "MeddayNonopioid";
            $this->uses[] = "Option";
            //$this->loadModel("Medication");
            //$this->loadModel("Medday");
            $this->components[] = 'Meds';
        }
        parent::constructClasses();
    }

    /**
     *
     */
    function beforeFilter() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        parent::beforeFilter();
        global $oldTimeZone;

        $this->centralSupport = $this->DhairAuth->centralSupport();
        $this->researchStaff = $this->DhairAuth->researchStaff();

        $this->initPatientsTabNav();
        $oldTimeZone = date_default_timezone_get();

        // Needed to get Activity Diary data
        if (in_array("activity_diary_entries", 
                $this->modelsForThisDhairInstance)) {
            $this->loadModel("ActivityDiaryEntry");
        }

        if (!is_null(Configure::read('PATIENT_FIELDS_ORDERED'))){
            $this->fieldsInOrder = Configure::read('PATIENT_FIELDS_ORDERED');
        }

        if (!is_null(Configure::read('NEW_PATIENT_FIELDS_ORDERED'))){
            $this->newFieldsInOrder = 
                Configure::read('NEW_PATIENT_FIELDS_ORDERED');
        }

        /**
        if (!is_null(Configure::read('STUDY_FIELDS'))){
            $this->studyFields = 
                Configure::read('STUDY_FIELDS');
        }
        */

        $this->set('fieldsInOrder', $this->fieldsInOrder);
        $this->set('newFieldsInOrder', $this->newFieldsInOrder);
//        $this->set('studyFields', $this->studyFields);

    }// function beforeFilter() 

    /**
     * formerly beforeRender(); now, some rendered actions don't have tabs
     */
    function preRender() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        
        $this->TabFilter->show_normal_tabs();
        $this->jsAddnsToLayout = array_merge(
            $this->jsAddnsToLayout,
            array(
                'bootstrap.js',
                'cpro.jquery.js',
                'jquery.dataTables.js',
                'cpro.datatables.js',
                'ui.position.js',
                'ui.widget.js',
                'ui.menu.js',
                'ui.autocomplete.js',
                'highcharts.src.js'
            )
        );

        $instanceJS = CProUtils::get_instance_specific_js_name('cpro');
        if ($instanceJS)
            array_push($this->jsAddnsToLayout, $instanceJS.'.js');
        // Check if there's SurveySession data 
        // Then check if MED info can be entered up to 24 hours after 
        if (
            in_array('medications', Configure::read('modelsInstallSpecific')) and
            isset($this->request->data) and
            array_key_exists('SurveySession', $this->request->data)
        ) {
            $finishedSurveySessions = $this->Patient->finishedSurveySessions($this->request->data['Patient']['id']);
            if ($finishedSurveySessions){
                $lastFinished = new DateTime($finishedSurveySessions[count($finishedSurveySessions)-1]['reportable_datetime']);
                $current = $this->DhairDateTime->usersCurrentTime();

                if (date_diff($current, $lastFinished)->format('%a') <= 2)
                    $this->set('sessionToday', true);
                else
                    $this->set('sessionToday', false);
            }
        }

        if (isset($this->Patient->currentWindow['start']))
            $this->set('currentWindow', $this->Patient->currentWindow);
        if (isset($this->Patient->nextWindow['start']))
            $this->set('nextWindow', $this->Patient->nextWindow);

        //parent::beforeRender();
    }

    /**
     *
     */
    function afterFilter() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        parent::afterFilter();
        global $oldTimeZone;

        // reset the timezone (in case it changed)
        date_default_timezone_set($oldTimeZone);
    }

    /** Index action
     *
     * before filter: loggedIn redirects to Home if logged in
     *
     * otherwise, display main layout with login screen
     */
    function index()
    {
        $this->preRender();
    }

    /**
      Check whether a given username already exists
      @param $username name to check
      @return true if there is a user with that name
     */
    private function usernameExists($username) {
        $users = $this->User->findByUsername($username);
        return !empty($users);
    }

    /**
     * Create a new, untaken username for a user
     * @param firstname First name
     * @param lastname Last name
     * @return The new username
     */
    private function generateUsername($firstname, $lastname) {
        // change to lowercase and remove all but the letters
        $fnameNormalize = preg_replace('/[^a-z]/', '', strtolower($firstname));
        $lnameNormalize = preg_replace('/[^a-z]/', '', strtolower($lastname));

        $basename = substr($fnameNormalize, 0, 1) .
            substr($lnameNormalize, 0, 5);

        if (!$this->usernameExists($basename)) {
            return $basename;
        } else { // basename taken; just start adding numbers
            $i = 1;

            while ($this->usernameExists($basename . $i)) {
                $i++;
            }

            return $basename . $i;
        }
    }


    
    /**
     * Set the language of a user by POSTing JSON 
     * @param $userId, the user to change it for
     * @param data[User][id], the user to change it for, if not defined in the path
     * @param data[User][locale], the locale to change to, eg en_US
     */
    function setLanguage($userId=null){
        $this->autoRender = false;
        $this->layout = 'ajax';
        $this->header('Content-Type: application/json');  
    
        // If locale is not configurable, exit
        if (! in_array('locale_selections', Configure::read('modelsInstallSpecific')))
            return false;
        
        if (! $userId)
            $userId = $this->request->data['User']['id'];

        $validStaff = array();
        foreach ($this->User->getStaff($userId) as $staff )
            array_push($validStaff, $staff['users1']['id']);
        // $this->log('validStaff: '.print_r($validStaff, true), LOG_DEBUG);
        
        // Check if action is allowable for current user
        if (! in_array($this->user['User']['id'], $validStaff)) {
            return false;
        }

        $result = array();
        
        $this->loadModel('LocaleSelection');
        if ($this->request->isGet()){
            $lang = $this->LocaleSelection->find(
                'first', 
                array(
                    'conditions' => array('LocaleSelection.user_id' => $userId),
                    'recursive' => -1,
                    'order' => array('LocaleSelection.time DESC'),
            ));
        }
        
        else if ($this->request->isPost()){
            $selectedLanguage = $this->request->data['User']['locale'];

            $languages = Configure::read('i18nLanguages');
            if ($languages && in_array($selectedLanguage, array_values($languages))) {
                $lang = array(
                    'LocaleSelection' => array(
                        'user_id' => $userId,
                        'locale' => $selectedLanguage,
                        'time' => $this->DhairDateTime->usersCurrentTimeStr(),
                ));
            
                $lang = $this->LocaleSelection->save($lang);
            }        
        }
        
        $result['data'] = $lang;
        $result = json_encode($result);
        echo $result;    
    
        // $this->log('posted result: '.print_r($result, true), LOG_DEBUG);
        // $this->log('sent data: '.print_r($this->request->data, true), LOG_DEBUG);

    }
    

    /**
     * Construct an appointment out of the 4 fields in the form
     * @param $dataAppointment form data, Appointment array elem
     * @param $name name of the array in $dataAppointment with the 4 fields
     * @return The appointment time, as a reasonably formatted string
     */
    private function constructAppointmentTime($dataAppointment) {
       // $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);
        if (empty($dataAppointment)) {
            return null;
        }

        /* don't check for empty [hour][hour] or [minute][min], as these
       can be zero (which is 'empty') */
        if (empty($dataAppointment['date']) || 
            empty($dataAppointment['hour']) || 
            empty($dataAppointment['minute']))
        {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(); some time element tmpty so returning null", LOG_DEBUG);
            return null;
        }

        /* for some bizarre reason, Cake does not zero-pad minute dropdown
            values (but does zero-pad hour dropdown value).  So fix this 
        */
        $min = $dataAppointment['minute']['min'];

        if (strlen($min) < 2) {
            $min = '0' . $min;
        } 

        $timestamp = strtotime($dataAppointment['date'] . ' ' . 
                           $dataAppointment['hour']['hour'] . 
                            ':' . $min . ':00');

        if ($timestamp == 0) {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(); timestamp construction failed, so returning null", LOG_DEBUG);
            return null;
        } else {
            $returnDate = date('Y-m-d H:i:s', $timestamp);
            //$this->log('constructAppointmentTime, returning ' . $returnDate, LOG_DEBUG);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning " . $returnDate, LOG_DEBUG);
            return $returnDate;
        }
    }

    // validation steps
    /* one could argue that this belongs in a cakephp 'validates' Model
       method, but these rules seem too complex for that API 
     */

    /**
      * clinic id may be set to a value it shouldn't.  If this is the 
      *   case, set it to the authorized user's clinic_id
      * @param data Data with clinic id
      * @param authUser Authorized user who is adding/editing the patient
      */
    private function fixClinicId(&$data, $authUser) {
        if (empty($data['User']['clinic_id']) || 
            !$this->DhairAuth->validClinicId($data['User']['clinic_id'], 
                                                $authUser))
        {
            $data['User']['clinic_id'] = $authUser['User']['clinic_id'];
        }
    }

    /**
     * Modifies form data for save elsewhere
     * Constructs appointment times out of their disparate form data parts
     * Only attempts to change the first appt it finds with form-mod'd data.
     * If SurveySession exists for the appt,
     *      If test patient, sets form data SurveySession, Answer, and Medday datetimes
     *      If non-test patient, TODO
     * @param data cakephp form data 
     * @param patient_id 
     */
    private function prepAppointmentTimesForSave(&$data, $patient_id) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() just entered; here's data[Appointments]: " . print_r($data['Appointment'], true), LOG_DEBUG);

        if (!array_key_exists('Appointment', $data)){
            return;
        }

        //$apptsFormData = $data['Appointment'];

        $patient = $this->Patient->findById($patient_id);
        //$data['SurveySession'] = $patient['SurveySession'];
        // 'modified' field included here and will be passed to save so cake won't auto-set it

        // only one appt's datetime can be edited per form submission; unset other appts' datetimes 
        //$apptToEdit = -1;
        $apptDatetimeEdited = false;

        foreach($data['Appointment'] as $key => $apptFormData){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts top", LOG_DEBUG);
            $apptFormConstructedTime = 
                $this->constructAppointmentTime($apptFormData);

            if (array_key_exists('id', $apptFormData) &&
                isset($apptFormData['id'])){
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, appt id " . $apptFormData['id'] . " exists, so this might be an edit", LOG_DEBUG);
                // Appointment exists, so this might be an edit
                // check whether this appointment has a session
                $apptInDB = 
                    $this->Appointment->findById($apptFormData['id']);
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, here's apptInDB: " . print_r($apptInDB, true), LOG_DEBUG);

        // no need to translate across TZ, it's done in models, so this is all user TZ
                if (($apptFormConstructedTime != 
                        $apptInDB['Appointment']['datetime'])
                    && !$apptDatetimeEdited) {
//                  $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; (apptFormConstructedTime != apptInDB['Appointment']['datetime']) && !apptDatetimeEdited", LOG_DEBUG);

                  $log['Log'] = array(
                      "user_id" => $this->Auth->user('id'),
                      "controller" => strip_tags($this->request->params['controller']), 
                      "action" => __FUNCTION__,
                      "params" => "Changing appt date: "
                                . "will affect Medday if it exists, "
                                . "apptId:"
                                    . $apptFormData['id'] 
                                    . ",oldDateTime(user time zone):" 
                                    . $apptInDB['Appointment']['datetime'] 
                                    . ",newDateTime(user time zone):" 
                                    . $apptFormConstructedTime,
                      "time" => gmdate("Y-m-d G-i-s"),
                      "ip_address" => $this->request->clientIp(),
                      "user_agent" => $_SERVER['HTTP_USER_AGENT']
                  );
                  $this->Log->create();
                  $this->Log->save($log);

                  if (array_key_exists('SurveySession', $apptInDB) 
                      && (isset($apptInDB['SurveySession']['id'])))
                  {
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; session exists for this appt", LOG_DEBUG);
                    // session exists for this appt
                    if ($patient['Patient']['test_flag'] == 1){
//                        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; session exists for this appt, but this is a test patient so following through with edit of datetimes", LOG_DEBUG);
                        // follow through w/ edit
                        $data['Appointment'][$key]['datetime'] = 
                            $apptFormConstructedTime; 
                        $apptDatetimeEdited = true;

                        $this->adjustSurveySessionDatesPerApptDateChange(
                                $data, $apptInDB, $patient, $key); 

                        // No need to modify Medday dates, as they are read from the appt.

                    }//if ($patient['Patient']['test_flag'] == 1){
                    else {
//                        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; session exists for this appt and this is not a test patient, so wont change this appt datetime", LOG_DEBUG);
                        //session exists - cannot change appointment
                        $data['Appointment'][$key]['datetime'] = 
                            $apptInDB['Appointment']['datetime'];
                        
                        //continue;
                    }
                  }//if (array_key_exists('SurveySession', $apptInDB) 
                  //    && (isset($apptInDB['SurveySession']['id'])))
                  else {
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; session does not exist for this appt", LOG_DEBUG);
                    if (!$apptDatetimeEdited) {
//                      $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; !apptDatetimeEdited, so will write the form-constructed datetime of $apptFormConstructedTime", LOG_DEBUG);
                      // no survey session for this appt, ok to save date
                      $data['Appointment'][$key]['datetime'] = 
                        $apptFormConstructedTime; 
                      $apptDatetimeEdited = true;
                    }
                    else {
//                      $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; apptDatetimeEdited, so will leave datetime unchanged at " . $apptInDB['Appointment']['datetime'], LOG_DEBUG);
                      $data['Appointment'][$key]['datetime'] = 
                        $apptInDB['Appointment']['datetime']; 
                    }
                  }// else   
                } // if ($apptFormConstructedTime != 
                //        $apptInDB['Appointment']['datetime']
                //    && !$apptDatetimeEdited) {
                else {
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating appts, at id " . $apptFormData['id'] . "; either apptFormConstructedTime == apptInDB[Appointment][datetime], or apptDatetimeEdited, so will leave datetime unchanged at " . $apptInDB['Appointment']['datetime'], LOG_DEBUG);
                    $data['Appointment'][$key]['datetime'] = 
                        $apptInDB['Appointment']['datetime']; 
                }
            }//if (array_key_exists('id', $apptFormData) && isset($apptFormData['id'])){
            else {
                // TODO check this
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); iterating data['Appointment']; no id match for this appt - is this hit for new appts? ", LOG_DEBUG);
                $data['Appointment'][$key]['datetime'] = 
                    $apptFormConstructedTime; 
            }   
        }// foreach($data['Appointment'] as $key => $apptFormData){

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); now returning, having set data[Appointment] to: " . print_r($data['Appointment'], true) . ", and data[SurveySession] to :" . print_r($data['SurveySession'], true) . ", and data[Answer] to :" . print_r($data['Answer'], true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); now returning, having set data to: " . print_r($data, true), LOG_DEBUG);

        return;
    }// prepAppointmentTimesForSave(&$data, $patient_id = null) {

    /**
     * TODO move to AppointmentModel
     * Are a patient's appointment datetimes reasonable?
     *   successive appointments should be at least MIN_SECONDS_BETWEEN_APPTS apart
     * @param $date Data with appointment datetimes
     * @return Whether the appointment datetimes are reasonable
     */
    private function _suitableAppointments($data) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(data) w/ data:" . print_r($data, true), LOG_DEBUG);
        $timezone = $this->User->getTimeZone($data['User']['id']);

        /* It's easiest just to compare successive appointment datetimes, but appointment datetimes can 
        be empty (except t1).  So create dummy appointment datetimes MIN_SECONDS_BETWEEN_APPTS later
        for the empty ones */
        $dummyDateTimes = array();

        foreach ($data['Appointment'] as $i => $appointment) {
            if ($i == 0) continue;

            if (empty($appointment['datetime'])) {
                $dummyDateTimes[$i] = 
                    $this->DhairDateTime->addPeriodToTime(
                        $data['Appointment'][$i-1]['datetime'], 
                        MIN_SECONDS_BETWEEN_APPTS,
                        $timezone);
            } else {
                $dummyDateTimes[$i] = $appointment['datetime'];
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); dummyDateTimes:" . print_r($dummyDateTimes, true), LOG_DEBUG);

        foreach ($data['Appointment'] as $i => $appointment) {
            if ($i == 0) continue;
   
            if (strtotime($data['Appointment'][$i]['datetime']) 
                    <= strtotime($data['Appointment'][$i - 1]['datetime'])){ 
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning false because dates are out of temporal sequence.", LOG_DEBUG);
                return false;
            }

            $result = 
              $this->DhairDateTime->compareDifferenceToPeriod(
                $data['Appointment'][$i-1]['datetime'], 
                $dummyDateTimes[$i], MIN_SECONDS_BETWEEN_APPTS, $timezone);
            if ($result == '<') {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning false because .", LOG_DEBUG);
                return false;
            }
        }

        // FIXME if appts are not in temporal sequence, return false

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning true.", LOG_DEBUG);

        return true;
    } // private function _suitableAppointments($data) {

    /**
     *  If an Appointment needs to have its date changed after a SurveySession has already been started for it, call this function to change its dates by the same amount.
     */
    private function adjustSurveySessionDatesPerApptDateChange(
                        &$data, $apptInDB, $patient, $apptKeyInFormData){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $apptId = $data['Appointment'][$apptKeyInFormData]['id'];


        // no need to translate across TZ, it's done in models, so this is all user TZ
        $data['SurveySession'] = array();
        $data['SurveySession'][0] = array();
        $data['SurveySession'][0]['id'] = 
            $apptInDB['SurveySession']['id'];

        $datetimeOrig = new DateTime($apptInDB['Appointment']['datetime']);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's datetimeOrig: " . $datetimeOrig->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
        //$datetimeNew = new DateTime($newDatetime);
        $datetimeNew = 
            new DateTime($data['Appointment'][$apptKeyInFormData]['datetime']);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's datetimeNew: " . $datetimeNew->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
        $datetimeDiff = $datetimeOrig->diff($datetimeNew);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's datetimeDiff: " . $datetimeDiff->format("%R%d days, %h hours, & %i seconds"), LOG_DEBUG);

        $log['Log'] = array(
                "user_id" => $this->Auth->user('id'),
                "controller" => strip_tags($this->request->params['controller']),
                "action" => __FUNCTION__,
                "params" => "apptId:" . $apptId 
                                  . ",oldDateTime(user time zone):" 
                                  . $datetimeOrig->format(MYSQL_DATETIME_FORMAT) 
                                  . ",newDateTime(user time zone):" 
                                  . $datetimeNew->format(MYSQL_DATETIME_FORMAT),
                "time" => gmdate("Y-m-d G-i-s"),
                "ip_address" => $this->request->clientIp(),
                "user_agent" => $_SERVER['HTTP_USER_AGENT']
        );
        $this->Log->create();
        $this->Log->save($log);

        $sessionStarted = 
            new DateTime(
                $apptInDB['SurveySession']['started']);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's sessionStarted: " . $sessionStarted->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
        $sessionStarted->add($datetimeDiff);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's sessionStarted after adding diff: " . $sessionStarted->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
        $data['SurveySession'][0]['started'] = 
            $sessionStarted->format(MYSQL_DATETIME_FORMAT);                        

        $sessionModified = 
            new DateTime($apptInDB['SurveySession']['modified']);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's sessionModified: " . $sessionModified->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
        $sessionModified->add($datetimeDiff);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); appt id " . $apptId . ", survey session id " . $apptInDB['SurveySession']['id'] . ", here's sessionModified after adding diff: " . $sessionModified->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
        $data['SurveySession'][0]['modified'] = 
            $sessionModified->format(MYSQL_DATETIME_FORMAT);
 
        $answers = $this->Answer->find('all',
            array('recursive' => -1, 
                    'conditions' => array('survey_session_id' => 
                         $apptInDB['SurveySession']['id'])));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); found and populated answers: " . print_r($answers, true), LOG_DEBUG);
        $data['Answer'] = array();
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); found and populated data[Answer]: " . print_r($data['Answer'], true), LOG_DEBUG);
        foreach ($answers as $key => $answer){
            $answer = $answer['Answer'];
            $data['Answer'][$key] = $answer;   
            $answerModified = new DateTime(
                                    $answer['modified']);
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); survey session id " . $apptInDB['SurveySession']['id'] . ", answer ID " . $answer['id'] . "; here's answerModified: " . $answerModified->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
            $answerModified->add($datetimeDiff);
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); survey session id " . $apptInDB['SurveySession']['id'] . ", answer ID " . $answer['id'] . "; here's answerModified after adding diff: " . $answerModified->format(MYSQL_DATETIME_FORMAT), LOG_DEBUG);
            $data['Answer'][$key]['modified'] =
                $answerModified->format(MYSQL_DATETIME_FORMAT);
        } 

    } // adjustSurveySessionDatesPerApptDateChange(...)


    /**
     * Check whether an entered username exists for a different user
     * @param username The username
     * @param id The id of the current user
     * @return null if the username does not exist, an array of 
     *         (status, message) if it does
     */
    private function checkUsername($username, $id) {
        // a user with the same username (possibly this one)
    $oldUser = $this->User->findByUsername($username);

    if (!empty($oldUser) && $oldUser['User']['id'] != $id) {
        return array('status' => self::USERNAME_EXISTS,
                     'message'=> "Username $username is already taken.  
                          Please pick another name.");
        }

    return null;
    }

    /**
     * Check the basic data common to both add and edit actions
     * @param data Data to check
     * @param id If edit, the id of the patient we are editing, otherwise null
     * @return null if the data is ready to save, an array of 
     *         (status, message) if it is not
     */
    private function checkBasicData(&$data, $id=null) {
//      $this->log(__CLASS__ . "." . __FUNCTION__ . "; data: " . print_r($data, true), LOG_DEBUG);
        $firstName = Hash::extract($data, 'User.first_name');
        $lastName = Hash::extract($data, 'User.last_name');
        $mrn = Hash::extract($data, 'Patient.mrn');

        // Iterate through fields on patients/add form, checking if they are required and set
        foreach($this->newFieldsInOrder as $field => $isRequired){
            if (
                // array_key_exists($field, $this->newFieldsInOrder) and
                $isRequired and
                !Hash::extract($data, $field)
            )
                return array(
                    'status' => self::MISSING_DATA,
                    'message'=> "Missing basic data: $field",
                );
        }
        // check that there is not another user with the same basic info
        if ($mrn){
            $oldPatient = $this->Patient->findPatient($mrn, $data['User']['clinic_id']);

            if (!empty($oldPatient) && $oldPatient['User']['id'] != $id) {
                // patient already exists
                return array(
                    'status' => self::PATIENT_EXISTS,
                    'message'=>
                        "Patient {$oldPatient['User']['username']} already exists with same MRN at this site."
                );
            }
        }

        // If the username is empty, create one from the first/last name
        if (
            array_key_exists('User.first_name', $data) and
            array_key_exists('User.last_name', $data) and
            empty($data['User']['username'])
        ){
            $data['User']['username'] = $this->generateUsername($firstName, $lastName);

            return array(
                'status' => self::NEW_USERNAME,
                'message' => 'Unique username generated'
            );
        } else {
            return $this->checkUsername($data['User']['username'], $id);
        }
    } // private function checkBasicData(&$data, $id=null) {

    /**
     * &$arr reference to patient record to change
     * @param settableFields fields that can be set, 
     *      eg User.last_name, Patient.mailing_address
     */
    private function unsetAndSanitizeFields(&$arr, $settableFields) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        foreach (array_keys($arr) as $key) {
            if (!in_array($key, $settableFields)) {
                unset($arr[$key]);
            } 
            elseif (!empty($arr[$key]) && !is_array($arr[$key])) {
                    $arr[$key] = strip_tags($arr[$key]);
            }
        }
    }

    /**
     * Change any empty dates to null
     * @param arr Array of data
     * @param dateFields Names of date fields in $arr
     */
    private function unsetDates(&$arr, $dateFields) {
    foreach (array_keys($arr) as $key) {
        if (in_array($key, $dateFields)) {
            if (empty($arr[$key]) || strtotime($arr[$key]) == 0) {
                $arr[$key] = null;
                }
            }
        }
    }

    /**
      * Unset any Patient and User fields that should not be set
      * and sanitize those that can be set
      * @param data Data to modify
      * @param settableFields fields that can be set, 
      *      eg User.last_name, Patient.mailing_address
      */
    private function unsetAndSanitizeDataFields(&$data, $settableFields)
    {
       // $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $modelsFields = array('User' => array(), 'Patient' => array());
        foreach($settableFields as $field){
            $pieces = explode('.', $field);

            $model = $pieces[0];

            $field = $pieces[1];
            $modelsFields[$model][] = $field;
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; modelsFields: " . print_r($modelsFields, true), LOG_DEBUG);

        $this->unsetAndSanitizeFields($data['User'], $modelsFields['User']);
        $this->unsetAndSanitizeFields($data['Patient'], 
                                        $modelsFields['Patient']);
    $this->unsetDates($data['Patient'], array('treatment_start_date', 
                                              'check_again_date'));
    }

    /**
     * Check new data for problems, fix those that can be fixed
     * @param data Data to check (presumably $this->request->data from a form)
     * @param authUser Authorized user who is adding/editing the patient
     * @return null if the data can be saved, a array of
     *         (status, message) if it cannot
     */
    private function checkAndFixForAdd(&$data, $authUser) {
        $this->fixClinicId($data, $authUser);

        // Check if all the fields are filled in correctly
        if (array_key_exists('Appointment', $data)){
            $datetime = $this->constructAppointmentTime($data['Appointment'][0]);
            if ($datetime)
                $data['Appointment'][0]['datetime'] = $datetime;
        }
        //$this->prepAppointmentTimesForSave($data);

        // don't unset required fields, or clinic id or username
        $this->unsetAndSanitizeDataFields(
            $data,
            array_keys(array_merge(
                array('User.clinic_id'=>true, 'User.username'=>true),
                $this->newFieldsInOrder
            ))
        );

        $returnVal = $this->checkBasicData($data, null);

        // Try model validation
        $this->Patient->set($data);
        $this->User->set($data);

        if (!$this->Patient->validates() or !$this->User->validates()){
            $validationErrors = Set::flatten(array_merge(
                $this->User->validationErrors,
                $this->Patient->validationErrors
            ));

            // If there are existing errors, add them to the end
            if (isset($returnVal['message']) and $returnVal['status'] != self::NEW_USERNAME)
                array_push($validationErrors, $returnVal['message']);

            $returnVal['message'] = join('<br>', $validationErrors);
            $returnVal['status'] = 'Model validation error';
        }

        // $this->log('returnval: '.print_r($returnVal, true), LOG_DEBUG);
        // $this->log('validationErrors: '.print_r($validationErrors, true), LOG_DEBUG);
        return $returnVal;

    }

    /**
     * Check edited data for problems, fix those that can be fixed
     * @param data Data to check (presumably $this->request->data from a form)
     * @param id The id of the patient we are editing.
     * @param authUser Authorized user who is adding/editing the patient
     * @return null if the data can be saved, a array of 
     *         (status, message) if it cannot
     */
    private function checkAndFixForEdit(&$data, $id, $authUser) {
        $this->fixClinicId($data, $authUser);
        $patient = $this->Patient->findById($id);

        if ($data['Patient']['no_more_check_agains'] == 1){
            $data['Patient']['check_again_date'] = null;
        }

        $fieldsToKeep = array_merge(array('User.id'), 
                                            $this->fieldsInOrder, 
                                            //$this->studyFields, 
                                            $this->checkAgainFields);
        // $this->log('fieldsToKeep: '.print_r($fieldsToKeep,true), LOG_DEBUG);
        $this->unsetAndSanitizeDataFields($data, $fieldsToKeep);

        if (empty($data['Patient']['off_study_status'])) {
            $data['Patient']['off_study_status'] = null;
        }

        if (empty($data['Patient']['off_study_reason']))
            $data['Patient']['off_study_reason'] = null;

        // don't let staff 'unconsent', or change type, test_flag, 
        // consent date or consenter_id after consenting
        if ($this->Patient->isParticipant($id)) {
            $data['Patient']['consent_status'] = Patient::CONSENTED;
            $data['Patient']['user_type'] = 
                $patient['Patient']['user_type'];
            $data['Patient']['consenter_id'] = 
                $patient['Patient']['consenter_id'];
            $data['Patient']['consent_date'] = 
                $patient['Patient']['consent_date'];
            $data['Patient']['test_flag'] = 
                $patient['Patient']['test_flag'];

            // set off-study timestamp iff status was changed
            if ($data['Patient']['off_study_status'] != 
                $patient['Patient']['off_study_status']) 
            {
                $data['Patient']['off_study_timestamp'] = 
                    $this->DhairDateTime->usersCurrentTimeStr();
            } 
            else {
                $data['Patient']['off_study_timestamp'] = 
                    $patient['Patient']['off_study_timestamp'];
            }
        } 
/** Don't auto-set these anymore
        elseif ($data['Patient']['consent_status'] == Patient::CONSENTED) {
            $data['Patient']['consent_date'] = date('Y-m-d');
            $data['Patient']['consenter_id'] = $authUser['User']['id'];
        }
*/
        // Cake seems to reset boolean flags if we unset them above,
        // so reset them to their old values
        $data['Patient']['study_participation_flag'] = 
            $patient['Patient']['study_participation_flag'];
        $data['Patient']['eligible_flag'] = 
            $patient['Patient']['eligible_flag'];
        $data['User']['change_pw_flag'] = 
            $patient['User']['change_pw_flag'];

        
        $this->prepAppointmentTimesForSave($data, $id);
    
        $returnVal;

        if (!$this->Appointment->suitableAppointments($data)) {
            $hours = MIN_SECONDS_BETWEEN_APPTS / 60 / 60; 
            $errorMessage = "Appointments must be at least $hours apart, and their dates in sequence";
            $returnVal = array('status' => self::BAD_APPOINTMENT_TIMES,
                        'message' => $errorMessage); 
        }
        if (!isset($returnVal))
            $returnVal = $this->checkBasicData($data, $id);

        if (isset($returnVal)){
                  $log['Log'] = array(
                      "user_id" => $this->Auth->user('id'),
                      "controller" => strip_tags($this->request->params['controller']), 
                      "action" => __FUNCTION__,
                      "params" => "patient ID:"
                                . $patient['Patient']['id']
                                . ": returning false; if 1 or 2 Log entries immediately previous indicate a change to survey or medday datetimes they should be ignored!",
                      "time" => gmdate("Y-m-d G-i-s"),
                      "ip_address" => $this->request->clientIp(),
                      "user_agent" => $_SERVER['HTTP_USER_AGENT']
                  );
                  $this->Log->create();
                  $this->Log->save($log);
        }

        return $returnVal;
    } // private function checkAndFixForEdit(&$data, $id, $authUser) {

    /**
     * Add a new patient.
     */
    /* This is generally done in a few steps:
       1.  Form is displayed, and basic patient data entered
       2.  If the data is not unique, a message is displayed saying this
       patient already exists under username X.

           If patient data is unique, a default username is generated
           and the form is redisplayed for the staffer to approve

       3.  Once a form is submitted with unique data and a username, the
           patient is created, a default password is generated and
       we go to the 'view patient' page.
     */
    function add(){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's request->data:" . print_r($this->request->data, true), LOG_DEBUG);
        $this->helpers[] = 'PatientData';
        $patientNotExist = false;
        // some data entered
        if (!empty($this->request->data['User']) or !empty($this->request->data['Patient'])) {
            $problem = $this->checkAndFixForAdd($this->request->data, $this->user);
            if (
                (
                    !empty($problem)
                    and $problem['status'] != self::NEW_USERNAME
                ) or (
                    $problem['status'] == self::NEW_USERNAME and (
                        !array_key_exists('User.username', $this->newFieldsInOrder)
                        or $this->newFieldsInOrder['User.username']
                    )
                )
            ) {
                if ($problem['status'] == self::NEW_USERNAME) {
                    // this is the 'no error' case
                    $patientNotExist = true;
                } else {
                    $this->Session->setFlash($problem['message']);
                }
            } else {
                $patientNotExist = true;

                // set password
                $username = $this->request->data['User']['username'];

                if ($this->User->hasField('dt_created'))
                    $this->request->data['User']['dt_created'] = $this->DhairDateTime->usersCurrentTimeStr();

                $this->User->create();

//                $this->log(__CLASS__ . "." . __FUNCTION__ . "($id), just before User->save, here's request->data:" . print_r($this->request->data, true), LOG_DEBUG);
                $this->User->save($this->request->data);
                $id = $this->User->id;

                if (!defined('PATIENT_SELF_REGISTRATION')) {
                    $tempPassword = $this->Password->getTempPassword($username);
                    $this->request->data['User']['password'] =
                        $this->Auth->password($tempPassword);
                    //require password to be changed on next login
                    $this->request->data['User']['change_pw_flag'] = 1;
                    $this->User->save($this->request->data);
                }

                $this->request->data['Patient']['id'] = $id;
                
                $initialPatientRole = 'Patient';
                if (defined('INITIAL_CONSENT_STATUS')){
                    $consentStatus = INITIAL_CONSENT_STATUS;
                }
                else $consentStatus = Patient::USUAL_CARE;

                if (defined('INITIAL_PATIENT_ROLE')){
                    $initialPatientRole = INITIAL_PATIENT_ROLE;
                    if ($initialPatientRole == 'ParticipantTreatment'){
                        $consentStatus = Patient::CONSENTED; 
                        $this->request->data['Patient']['study_group'] 
                                                    = Patient::TREATMENT;
                    }
                }

                $this->request->data['Patient']['consent_status'] = $consentStatus;

                // enums are init'd strangely since they're no longer supported 
                foreach (array('user_type', 'clinical_service', 'gender', 
                               'off_study_status') as $enumField) 
                {
                    if (!array_key_exists("Patient.$enumField", 
                                  $this->newFieldsInOrder)) 
                    {
                         $this->request->data['Patient'][$enumField] = null;
                    }
                }
                
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "($id), just before Patient->save, here's request->data:" . print_r($this->request->data, true), LOG_DEBUG);
                $patient = $this->User->Patient->save($this->request->data);
                
                $this->PatientExtension->create();
                $this->request->data['PatientExtension'] = array();
                $this->request->data['PatientExtension']['patient_id'] = $id;
                $this->PatientExtension->save($this->request->data);
 
                $this->UserAclLeaf->create();
                $this->UserAclLeaf->save(array(
                                            'user_id'=>$this->User->id,
                                            'acl_alias'=>
                                                'acl' . $initialPatientRole));

                // Only create appointment if a valid datetime was created by constructAppointmentTime()
                if (
                    isset($this->request->data['Appointment'][0]['datetime']) and
                    $this->request->data['Appointment'][0]['datetime']
                ){
                    $this->Appointment->create();
                    $this->request->data['Appointment'][0]['patient_id'] = $id;
                    $this->request->data['Appointment'][0]['created_staff_id'] = $this->user['User']['id'];
                    
                    $this->Appointment->save($this->request->data['Appointment'][0]);
                }


            // Send registration email when adding a new patient for ESRA-C Sarcoma
            if (
                defined('AUTO_EMAIL_REGISTRATION') and
                AUTO_EMAIL_REGISTRATION and
                array_key_exists(
                    'User.email',
                    Configure::read('NEW_PATIENT_FIELDS_ORDERED')
                ) and
                $patient['User']['email'] and
                $patient['User']['first_name'] and
                $patient['User']['last_name'] and
                $patient['Patient']['birthdate']
            ){
                // Generate and save a webkey
                $user = $this->User->saveField('webkey', mt_rand());
                $email = new CakeEmail();
                $email->template(CProUtils::getInstanceSpecificEmailName('self_register', 'html'))
                    ->emailFormat('html')
                    ->from(array($patient['Clinic']['support_email'] => SHORT_TITLE))
                    ->to($patient['User']['email'])
                    ->subject(__('Registration for %s', SHORT_TITLE));
                $email->viewVars(array('patient' => $patient, 'user' => $user));
                $email->send();
            }

            // Generate and save webkey for anonymous access
            if (defined('PATIENT_ANONYMOUS_ACCESS') and PATIENT_ANONYMOUS_ACCESS){
                $this->Webkey->create();
                $this->Webkey->save(array(
                    'Webkey'=>array(
                        'user_id' => $id,
                        'purpose' => 'anonymous_access',
                        'sent_on' => gmdate(MYSQL_DATETIME_FORMAT),
                    )
                ));
            }

                // set up info for final message
                $this->Session->setFlash("Patient $username added.");
                $this->redirect(array('action' => "edit/$id"));
            }
        }// if (!empty($this->request->data['User'])) {  // some data entered

        $this->set('patientNotExist', $patientNotExist);
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout, 
            array('ui.datepicker.js', 
                    'jquery.validate.js', 'cpro.jquery.validate.js'));
        $this->preRender();
    } // function add(){


    /**
     * Is an id a valid patient id for the authorized user
     * @param id Id to check
     * @authUser authorized user
     * @return true if the id corresponds to a patient and the authUser
     *     can access them
     */
    private function validPatientId($id, $authUser) {
        if (empty($id)) {
            return false;
        } else {
            $patient = $this->Patient->findById($id);

            return !empty($patient) && 
                $this->DhairAuth->validClinicId($patient['User']['clinic_id'], 
                                                                    $authUser);
        }
    }
    
    /**
     * Check for a valid patient id; abort if it is not
     * @param id Id to check
     * @authUser authorized user
     */
    private function checkPatientId($id, $authUser) {
        if (!$this->validPatientId(intval($id), $authUser)) {
        $this->Session->setFlash('Not a valid patient id!');
            $this->redirect($this->referer());
        }
    }

    /**
     * Create an array of 4 booleans, one for each T-time , indicating
     * whether the T-time is represented in a given array of SurveySessions
     * @deprecated SurveySessions are no longer bound to a fixes set of T times 
     * @param $sessions Array of SurveySessions
     * @return an array with 4 members, where 'T<n>' => 1 if one of the
     *     sessions in the array had type 'T<n>', 0 otherwise
     */    
    private function createTArray($sessions) {
    $result = array('T1' => 0, 'T2' => 0, 'T3' => 0, 
                    'T4' => 0);

    foreach ($sessions as $session) {
        if ($session['type'] != SurveySession::NONT) {
            $result[$session['type']] = 1;
            }
        }

    return $result;
    }

    /**
     * Determine whether the patient's T sessions have been started
     * @deprecated SurveySessions are no longer bound to a fixes set of T times 
     * @param An array of SurveySessions
     * @return An array of 4 booleans, indicating whether the corresponding
     *   sessions have been started
     */
    private function startedTs($sessions) {
        /* The existence of a session of type T<n> indicated T<n> was
       started */
        return $this->createTArray($sessions);
    }

    /**
     * Determine whether the patient's T sessions were finished
     * @deprecated SurveySessions are no longer bound to a fixes set of T times 
     * @return An array of 4 booleans, indicating whether the corresponding
     *   sessions have been finished
     */
    private function finishedTs() {
        return $this->createTArray($this->User->SurveySession->filterFinished(
        $this->request->data['SurveySession']));
    }

    /**
     * Does the patient have a finished session?
     * @param patient
     * @return true if the patient has a finished session
     */
    private function finishedSession($patient, $projectId = null) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), here's patient:" . print_r($patient, true), LOG_DEBUG);
        if (empty($patient) || empty($patient['SurveySession'])) {
            return false;
        }

        $fs = $this->SurveySession->filterFinished($patient['SurveySession'], $projectId);
        return !empty($fs);
    }
    
    private function medicationsPatient($patient) {
        if (in_array("medications", $this->modelsForThisDhairInstance)){
            $medications_patient = array();
            // TODO
            $this->set('medications_patient', $medications_patient);
    }
    }

    /**
     * Return a session finalized today for this patient.
     * @param patient
     * @return A session that was finalized today, or null if there was none
     */
    private function todaysSession($patient) {
        if (empty($patient) || empty($patient['SurveySession'])) {
            return false;
        }

        $today = date('Y-m-d');

        foreach ($patient['SurveySession'] as $session) {
            if (strpos($session['modified'], $today) === 0 && 
                $session['finished']) 
            {
                return $session;
            }
        }

        return null;
    }

    /**
     * Was there a session finalized today for this patient?
     * @param patient
     * @return true if the patient has a finished session with today's date
     */
    private function sessionToday($patient) {
        $session = $this->todaysSession($patient);
        return !empty($session);
    }

    /**
     * Login as a patient (that you have access to)
     * @param id Id of the patient
     */
    function loginAs($id = null) {

        $patient = $this->Patient->findById($id);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($id), here's the patient record:" . print_r($patient, true), LOG_DEBUG);

        if (defined('LOGIN_AS_PATIENT_ALLOWED') && LOGIN_AS_PATIENT_ALLOWED
            && (!defined('ELIGIBILITY_WORKFLOW')
                || (defined('ELIGIBILITY_WORKFLOW') && !ELIGIBILITY_WORKFLOW)
                || ($patient['Patient']['eligible_flag'] == '1')))
        {
            $authUser = $this->user;
            $this->checkPatientId($id, $authUser);
            $this->deleteVariablesOnLogout();
            $this->Auth->login(array("id" => $id));
     
            $this->Session->write(self::ID_KEY, mt_rand());
            /* set a variable so the staff member isn't forced to change 
               the patient's password */
            $this->Session->write(self::STAFF_LOGIN_AS_PATIENT, 'true');

            $user = $this->Auth->user();
            // $this->log("patients.loginAs " . $authUser['User']['username'] . " logged in as " . $user['User']['username'], LOG_DEBUG);
            $this->redirect($this->Auth->redirect());
        } 
        else {
            $this->Session->setFlash('Invalid action: can\'t log in as an ineligible patient.');
            $this->redirect($this->referer());
        }
    }

    /**
     * FIXME this should be renamed 'assessPatient' 
     * Take a survey as a patient (that you have access to)
     * @param id Id of the patient
     */
    function takeSurveyAs($id, $projectId) {

        $project = $this->Patient->projectsStates[$projectId]->project;

        if (strstr($project['Project']['roles_that_can_assess'], 'aroClinicStaff')){
            $authUser = $this->user;
            $this->checkPatientId($id, $authUser);

            if (empty($this->Patient->projectsStates[$projectId]->sessionLink)) {
                $this->Session->setFlash('Patient has no survey to take');
                $this->redirect($this->referer());
            }

//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), " . $authUser['User']['username'] . " assessing patient " . $id, LOG_DEBUG);
            $this->Session->write(self::TAKING_SURVEY_AS, $id);
            $this->redirect($this->Patient->projectsStates[$projectId]->sessionLink);
        } else {
            $this->Session->setFlash('Invalid action');
            $this->redirect($this->referer());
        }
    }


    /**
     * View Activity Diary
     * @param id Patient's id
    */
    function activityDiary($id = null, $startdate = null) {
        $this->checkPatientId($id, $this->user);

        $this->request->data = $this->Patient->findById($id);
        
        if ( isset($startdate) ) {
            $this->set("startdate", $startdate);
            if ( $startdate > date('Y-m-d',strtotime("-6 days")) ) {
                $oneweek = date('Y-m-d',strtotime("-6 days"));
                $endofweek = date('Y-m-d');
            } else {
                $oneweek = date("Y-m-d",strtotime($startdate));
                $endofweek = date("Y-m-d",strtotime($startdate."+6 days"));
            }
        } else {
            $oneweek = date('Y-m-d',strtotime("-6 days"));
            $endofweek = date('Y-m-d');
        }

        // view will choke if given all fields
        $fields = array('ActivityDiaryEntry.id','ActivityDiaryEntry.date',
                    'ActivityDiaryEntry.fatigue','ActivityDiaryEntry.type',
                    'ActivityDiaryEntry.typeOther','ActivityDiaryEntry.minutes',
                    'ActivityDiaryEntry.steps','ActivityDiaryEntry.note');

        $entries = $this->ActivityDiaryEntry->getData(
                                            $id, $oneweek, $endofweek, $fields);
        //$this->log("ActivityDiaryEntriesController index(patient_id: " . $patientId . "); entries:" . print_r($entries, true), LOG_DEBUG); 
        $this->set('entries', $entries);
                
                // allEntries for "My Results" totals - calls everything instead of limiting to one week - maybe be a way to combine these two queries?
        $allEntries = $this->ActivityDiaryEntry->getData($id, null, null, $fields);
        //$this->log("ActivityDiaryEntriesController index(patient_id: " . $patientId . "); entries:" . print_r($entries, true), LOG_DEBUG); 
        $this->set('allEntries', $allEntries);
        // End Activity Diary controllers
        $this->preRender();
    }
    
    /**
     * Callback function to get a staff id from a row of a db query
     * @param row Array containing the row
     * @param return The id field from the row
     */
    private function getStaffId($row) {
        return $row['users1']['id'];
    }

    /**
     * Callback function to get a staff name from a row of a db query
     * @param row Array containing the row
     * @param return The name field from the row
     */
    private function getStaffName($row) {
        return $row['users1']['username'];
    }

    /**
     * Create a new note or patient view note
     * @param id Id of the patient
     * @param authorId Author's user id
     * @param text text of the note
     * @param timestampField field name for the timestamp 
     *        ('lastmod' or 'created')
     * @param flagged whether the note is flagged or not (irrelevant for
     *        patient view notes
     * @return the note
     */
    private function createNote($id, $authUserId, $text, $timestampField, 
                                $flagged = null) 
    {
        $note['patient_id'] = $id;
        $note['author_id'] = $authUserId;
        // cake protects against sql injection since we're using save(); it will be sanitized when it is displayed in the page though
        $note['text'] = $text;
        $note[$timestampField] = $this->DhairDateTime->usersCurrentTimeStr();
        $note['flagged'] = $flagged;
        return $note;
    }

    /**
     * Save a patient view note
     * @param data Data with note
     * @param authUser user who wrote note
     */
    private function savePatientViewNote(&$data, $authorId) {
        $data['PatientViewNote'] = $this->createNote($data['User']['id'], 
            $authorId, $data['PatientViewNote']['text'], 'lastmod');
        $this->PatientViewNote->save($data);
    }

    /**
     * Create an array of staffid/staff name pairs for a patient
     * @param id Patient id
     * @return array of staffid/staff name
     */
    private function getStaffArray($id) {
        $staff = $this->User->getStaff($id);
    return array_combine(
        array_map(array('PatientsController', 'getStaffId'), $staff), 
        array_map(array('PatientsController', 'getStaffName'), $staff));
    }

    /**
     * Get the PatientViewNote from an array
     * @param arr array
     * @return the value
     */
    private function getPatientViewNote($arr) {
        return $arr['PatientViewNote'];
    }

    /**
     * Get the Note from an array
     * @param arr array
     * @return the value
     */
    private function getNote($arr) {
        return $arr['Note'];
    }

    function addStaffNote(){
        if (!$this->request->isAjax()) return;

        $result = array(
            'ok' => false,
            'message' => 'error saving note',
            'debug' => &$data,
        );
        $this->viewVars = &$result;
        $this->set(array('_serialize' => array_keys($result)));

        $data = $this->data;
        $data['Note']['created'] = gmdate(MYSQL_DATETIME_FORMAT);

        // Try to save new record
        if ($this->Note->save($data)){
            $result['ok'] = true;
            $result['message'] = 'note saved successfully';
        }
    }

    function flagStaffNote(){
        // 
        if (!$this->request->isAjax()) return;

        $result = array(
            'ok' => false,
            'message' => 'error adding note flag',
            // 'debug' => $this->data,
        );
        $this->viewVars = &$result;
        $this->set(array('_serialize' => array_keys($result)));

        // Remove id since we're adding a new record
        $data = $this->data;
        $this->Note->id = $data['Note']['id'];

        if (is_array($data['Note']['flag_type']))
            $data['Note']['flag_type'] = join(', ', $data['Note']['flag_type']);
        // Try to save record
        if ($this->Note->saveField('flag_type', $data['Note']['flag_type'])){
            $result['ok'] = true;
            $result['message'] = 'flag saved successfully';
        }

    }

    /**
     * Edit a patient
     * "RESTful" service for editing a patient
     * @param id Patient's id
    */
    function edit($id = null) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($id), here's data:" . print_r($this->request->data, true), LOG_DEBUG);

        if ($this->request->isAjax() and $this->request->is('post')){
            // $this->log(__CLASS__ . "." . __FUNCTION__ . "($id), isAjax.", LOG_DEBUG);

            $result = array();
            $id = $this->request->data['Patient']['id'];
            $otherFieldsWChanges = array();

            do { 
                if (!$this->validPatientId(intval($id), $this->user)) { 
                // in lieu of checkPatientId
                    $this->response->statusCode(403);
                    $result['ok'] = false;
                    $result['message'] = 'Not a valid patient id!';
                    break;
                }

                $patient = $this->Patient->findById($id);
                //$this->log(__CLASS__ . "." . __FUNCTION__ . "; found patient: " . print_r($patient, true), LOG_DEBUG);

                //unset fields that don't need to be saved, to avoid clashes in merge
                unset($this->request->data['Patient']['id']);
                unset($this->request->data['AppController']['AppController_id']);

                $mergedData = array_merge_recursive($patient, $this->request->data);
                //$this->log(__CLASS__ . "." . __FUNCTION__ . "; mergedData: " . print_r($mergedData, true), LOG_DEBUG);
                $this->Patient->set($mergedData);
                $validates = $this->Patient->validates();
                if (!$validates) {
                    $validateErrMsg = '';
                    foreach ($this->Patient->validationErrors as $field => $msgArray){
                        $validateErrMsg .= $field . ': ' . $msgArray[0];
                    }

                    // generated error message should be good enough
                    $this->response->statusCode(403);
                    $result['ok'] = false;
                    $result['message'] = $validateErrMsg;
                    break;
                } 
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "; No problem found in checkAndFixForEdit; next, will save: " . print_r($this->request->data, true), LOG_DEBUG);
                if (array_key_exists('User', $this->request->data)){
                    $this->User->id = $id;
                    $this->User->set($mergedData);
                    $validates = $this->User->validates();
                    if (!$validates) {
                        $validateErrMsg = '';
                        foreach ($this->User->validationErrors as $field => $msgArray){
                            $validateErrMsg .= $field . ': ' . $msgArray[0];
                        }
                        // generated error message should be good enough
                        $this->response->statusCode(403);
                        $result['ok'] = false;
                        $result['message'] = $validateErrMsg;
                        break;
                    }
                    //Patient and User validated, might as well save user now 
                    foreach ($this->request->data['User'] as $key => $val){
                        if ($key != 'id')
                            $this->User->saveField($key, $val, false); // already validated above
                    }
                }
                $this->Patient->id = $id;
                foreach ($this->request->data['Patient'] as $key => $val){
                    if ($key != 'id')
                        $this->Patient->saveField($key, $val, false); // already validated above

                    if ($key == 'consent_status' && $val == Patient::CONSENTED){
                        $this->Patient->saveField('off_study_status', Patient::ON_STUDY, false); // already validated above
                        $otherFieldsWChanges['PatientOffStudyStatus']
                            = array('newValue' => Patient::ON_STUDY);
                        // could also return enabled state, new select options
                    }
                }

                $this->response->statusCode(200);
                $result['ok'] = true;
                $result['message'] = $this->User->id;
                $result['data'] = $otherFieldsWChanges;
            } while (false);

            $this->set($result);
            $this->set('_serialize', array_keys($result));
            return;
        }// if ($this->request->isAjax()

        $this->checkPatientId($id, $this->user);

        $user = $this->User->findById($id);

        if (defined('PATIENT_SELF_REGISTRATION') and PATIENT_SELF_REGISTRATION){

            if (isset($user['Webkey'])){
                $webkey = $this->Webkey->find('first', array(
                    'conditions'=>array(
                        'Webkey.user_id'=>$id,
                        'Webkey.used_on'=>null,
                        'Webkey.purpose'=>'self-register',
                    ),
                    'recursive'=>-1,
                ));
                if ($webkey)
                    $webkey = $webkey['Webkey']['text'];
            }
            else
                $webkey = null;

            $emailTemplates 
                = $this->Patient->getEmailTemplateList($this->patient);

            $sentEmails = $this->Log->find('all', array(
                'conditions'=>array(
                    'patient_id' => $id,
                    'controller' => 'patients',
                    'action' => 'end sendEmail',
                ),
                'recursive' => -1
            ));
            $timezone = new DateTimeZone($this->Patient->getTimeZone($this->user));

            // Build list of when emails were sent
            $emailTimestamps = array();
            foreach($sentEmails as $email){
                $templateName = $email['Log']['params'];

                // Convert to localtime
                $time = new DateTime(
                    $email['Log']['time'],
                    new DateTimeZone('GMT')
                );
                $time->setTimeZone($timezone);
                $timestamp = $time->format('m/j/Y G:i');

                if (array_key_exists($templateName, $emailTimestamps))
                    array_push($emailTimestamps[$templateName], $timestamp);
                else
                    $emailTimestamps[$templateName] = array($timestamp);
            }
            $this->set('emailTimestamps', $emailTimestamps);
            $this->set('webkey', $webkey);
            $this->set('emailTemplates', $emailTemplates);
        }// if (defined('PATIENT_SELF_REGISTRATION') and PATIENT_SELF_REGISTRATION){

        $this->helpers['PatientData'] = array(
                                'user_types' => 
                                    $this->Patient->getUserTypes(),
                                'clinical_services' => 
                                    $this->Patient->getClinicalServices()); 

        $wasParticipant = $this->Patient->isParticipant($id);
        $patientViewNotes = $this->PatientViewNote->find('all', 
            array('recursive' => -1, 
              'order' => 'PatientViewNote.lastmod DESC',
              'conditions' => array('PatientViewNote.patient_id' => $id)));
        $pvns = array_map(array('PatientsController', 'getPatientViewNote'), 
                     $patientViewNotes);
        $notes = $this->Note->find('all', 
            array('recursive' => -1, 
              'order' => 'Note.created DESC',
              'conditions' => array('Note.patient_id' => $id)));
        $ns = array_map(array('PatientsController', 'getNote'), $notes);

        if (empty($this->request->data)) {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "($id), data empty", LOG_DEBUG);
            // FIXME I think we shouldn't be manipulating request data this way
            // instead, add these to $this->patient, which is created in AppController
            $this->request->data = array_replace_recursive(
                $this->patient,
                $this->User->findById($id)
            );
            $patient = $this->Patient->findById($id);
            $this->request->data['SurveySession'] = $patient['SurveySession'];
        //$this->log("patient edit; this->data[User] was empty, so this->data has been set to: " . print_r($this->request->data, true));

            $appointments = $this->Appointment->findWNullReorder('all', array(
                'conditions' => array('Appointment.patient_id' => $id),
                'recursive' => -1));
            $this->request->data['Appointment'] = array();
            foreach ($appointments as $appointment){
                // get rid of the [Appointment] array layer FIXME better way?
                $this->request->data['Appointment'][] = $appointment['Appointment'];
            }
            //$this->log("patient edit; this->data after adding appointments : " . print_r($this->request->data, true), LOG_DEBUG);

        } // if (empty($this->request->data)) {

        if ($this->centralSupport) {
            $this->set('clinics', $this->User->Clinic->find('list'));
        } else {
            $this->set('clinics', $this->User->Clinic->find('list',
                array('conditions' => 
                    array('Clinic.site_id' => $this->user['Clinic']['site_id'])
            )));
        }
        // $this->log('data: '.print_r(($this->request->data), true), LOG_DEBUG);
        // FIXME would be nice to do this in the model
        foreach ($this->request->data['Appointment'] as &$appointment) { 
            $appointment["session_finished"] = 0;
            $appointment["partial_finalization"] = 0;
            $appointment["session_started"] = 0;
            $appointmentId = $appointment["id"];
            if (array_key_exists('SurveySession', $this->request->data)){
                foreach($this->request->data['SurveySession'] as $session){ 
                    if (in_array("medications", $this->modelsForThisDhairInstance)) {
                        $meddayCount = $this->Medday->find(
                            'count', 
                            array('conditions' => array('survey_session_id' => $session['id']))
                        );
                        if ($meddayCount > 0)
                            $appointment["medday_exists"] = 1;
                        else 
                            $appointment["medday_exists"] = 0;
                    }
                    
                    if ($session['appointment_id'] == $appointmentId){
                        $appointment["session_started"] = 1;
                        $appointment['session_finished'] = $session['finished'];
                        $appointment['project_id'] = $session['project_id'];
                    }
                } 
            } 

        } 

        $this->set('patientId', $id);
        $this->set('staffs', $this->getStaffArray($id));
        $this->set('user_types', $this->Patient->getUserTypes());
        $this->set('clinical_services', $this->Patient->getClinicalServices());
        $this->set('offStudyStatuses', $this->Patient->getOffStudyStatuses());
        $this->set('researchStaff', $this->researchStaff);
        $this->set('centralSupport', $this->centralSupport);
        $this->set('wasParticipant', $wasParticipant);
        $this->set('notes', $ns);
        $this->set('patientViewNotes', $pvns);
        $this->set('authUser', $this->user['User']['username']);
        $this->set('timezone', $this->User->getTimeZone($id));
        $this->set('testUpdatable', true);
        $this->set('typeUpdatable', true);
        // set for patient_tools_links
        $this->set('sessionToday', $this->sessionToday($this->request->data));
        $this->set('finishedSession', $this->finishedSession($this->request->data));
        $this->set('finishedSession', $this->finishedSession($this->request->data));
        $this->set('medicationsPatient', $this->medicationsPatient($this->request->data));


       
        if (defined('STUDY_SYSTEM') && STUDY_SYSTEM){
            $this->set('consent_statuses', 
                        $this->Patient->getConsentStatusSelections($this->request->data));
        }// if (defined('STUDY_SYSTEM') && STUDY_SYSTEM){

        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout, 
            array('ui.datepicker.js', 
                    'jquery.validate.js', 'cpro.jquery.validate.js'));
        // $this->log("this->data at end of patient edit: " . print_r($this->request->data, true), LOG_DEBUG);

        
        // First check user language
        $locale = $this->request->data['User']['language'];

        // Override if row for user in LocaleSelection table
        if (
            array_key_exists('LocaleSelection', $this->request->data) &&
            count($this->request->data['LocaleSelection']) != 0
        )
            $locale = $this->request->data['LocaleSelection'][count($this->request->data['LocaleSelection']) - 1]['locale'];      
        if ($locale)
            $this->set('patientLocale', $locale);
        
        $showLinkToClinicianReport = false;
        if (defined('CLINICIAN_REPORT_PROJECT_ID')){
            $showLinkToClinicianReport = 
                $this->finishedSession($this->request->data, 
                                        CLINICIAN_REPORT_PROJECT_ID);
        }
        $this->set('showLinkToClinicianReport', $showLinkToClinicianReport);

        // Self-registration status (for P3P2)
        if (isset($user['User']['registered'])) {
            $registrationStatus = 'Registered';
        } else {
            $registrationStatus = 'Not registered';
        }
        $this->set('registrationStatus', $registrationStatus);
        
        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        
        $this->preRender();
    } // function edit($id = null) {


    /**
     * Create the array of sort directions used to create sortable header
     * fields for patient tables
     * @param fields Array of fields that can be sorted
     * @param sortDirection Direction to sort ('asc' or 'desc')
     * @param sortField Current field we are sorting by
     */
    private function sortDirectionsArray($fields, $sortDirection, $sortField) {
        /* each field in the display will have a link to sort by the
       field in question, all of which will be ascending sort, 
       *except* for the current field, which will be the opposite
       of the current direction.  */
        $sortDirections = array_fill_keys($fields, 'asc');

    if ($sortDirection == 'asc') {
        $sortDirections[$sortField] = 'desc';
        }

    return $sortDirections;
    }
    
    /**
     * View all patients the authenticated user can view
     * @param sortField Field to sort on (default = User.last_name)
     * @param sortDirection Direction (asc/desc, default = asc)
     * @param useCsv If true, output CSV version
     */
    function viewAll($sortField = 'User.last_name', $sortDirection = 'asc',
                     $useCsv = false) 
    {
        if ($useCsv) {   
        // make sure CSV file is not sorted by any field that may contain PHI
            $sortField = 'Patient.id';
            $sortDirection = 'asc';
        }

        $readablePatients = $this->Patient->findAccessiblePatients(
            $this->authd_user_id, $this->centralSupport, $this->researchStaff);

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "() readablePatients: " . print_r($readablePatients, true), LOG_DEBUG); // WARNING: a LOT of output with this!

        //$readablePatients['Appointment'] = 
        //    $this->Appointment->nullReorder($readablePatients['Appointment']);

        $patients = Set::sort($readablePatients, '{n}.' . $sortField, 
                          $sortDirection);

        // all fields we can sort by
        $fields = array('User.last_name', 'User.first_name', 'Patient.id', 
                        'Patient.MRN',
                    'Patient.consent_status', 'Patient.next_appt_dt', 
            'Clinic.name', 'Patient.clinical_service');
        $sortDirections = $this->sortDirectionsArray($fields, $sortDirection, 
                                                 $sortField);
    
        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        $this->set('patients', $patients);
        $this->set('sortDirections', $sortDirections);

        if ($useCsv) {
            // suppress debugging messages in output
            Configure::write('debug', 0);
            $this->layout = 'ajax';
            $this->autoLayout = false;
        $this->render('patientCsv');
        }

        $this->preRender();
    }
    // function viewAll(...)

    /**
     *
     */
    function interested_report(
                $sortField = 'User.last_name', $sortDirection = 'asc') {
        $readablePatients = 
            $this->Patient->findInterested(
                                $this->authd_user_id, $this->centralSupport, $this->researchStaff);

        $patients = Set::sort($readablePatients, '{n}.' . $sortField, 
                          $sortDirection);

        // all fields we can sort by
        $fields = array('User.last_name', 'User.first_name', 'Patient.id', 
                    'Patient.consent_status', 
            'Clinic.name');
        $sortDirections = $this->sortDirectionsArray($fields, $sortDirection, 
                                                 $sortField);
    
        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        $this->set('patients', $patients);
        $this->set('sortDirections', $sortDirections);

        $this->preRender();
    }

    /**
     * Reset a patient's password
     * @param id Id of the patient
     */
    function resetPassword($id = null) {
        $this->checkPatientId($id, $this->user);

        $tempPassword = $this->Password->resetPassword($id);
        $this->Session->setFlash(
            "Password has been changed to $tempPassword <br>
            Do not email credentials if the patient has already entered personal health information.<br>
            Patient will be prompted to change password on login.");
        $this->redirect($this->referer());
    }

    /**
     * Check that the data from the clinic staff's version of edit patient
     * is okay
     * @param data Data to check
     * @param id Id of the user
     */
    private function checkUsernameEtc(&$data, $id) {
        $patient = $this->Patient->findById($id);
        $this->prepAppointmentTimesForSave($data, $id);

        $returnVal;

        $data['Patient']['test_flag'] = $patient['Patient']['test_flag'];
    
        if (!$this->Appointment->suitableAppointments($data)) {
            $hours = MIN_SECONDS_BETWEEN_APPTS / 60 / 60; 
            $errorMessage = "Appointments must be at least $hours apart, and their dates in sequence";
            $returnVal = array('status' => self::BAD_APPOINTMENT_TIMES,
                        'message' => $errorMessage); 
        }
        else {
            $this->unsetAndSanitizeDataFields($data, 
                 array('User.id', 'User.username', 'User.first_name', 
                    'User.last_name', 'User.clinic_id',
                    'Patient.MRN', 'Patient.birthdate'
                    /**, 't1', 't2'*/));
            $returnVal = $this->checkBasicData($data, $id);
        }
        return $returnVal;
    }

    /**
     * Change a patient's username, dob, mrn, t1 or t2 (for clinic staff)
     * @param id Id of the patient
     */
    function changeUsername($id = null) {

        $this->checkPatientId($id, $this->user);
        $userPatient = $this->User->findById($id);

        if (empty($this->request->data['User'])) {  
            $this->request->data = $userPatient;
        } 
        else {
            $problem = $this->checkUsernameEtc($this->request->data, $id);
            $this->request->data['User']['id'] = $id;
            $this->request->data['User']['clinic_id'] = $userPatient['User']['clinic_id'];

            if (!empty($problem)) {
                $this->Session->setFlash($problem['message']);
            } 
            else {
                $this->User->id = $id;
                $this->User->save($this->request->data, true,
                    array('username', 'first_name', 'last_name'));
            /* need to set clinic_id so we can access timezone when
            saving appointment datetimes */
                $this->request->data['Patient']['id'] = $this->User->id;
                $this->Patient->save($this->request->data, true, 
                     array('birthdate', 'MRN', 't1', 't2'));

                $this->Session->setFlash("Patient record has been updated.");
                $this->redirect(array('action' => "edit/$id"));
            }
        }

        $ts = array('t1', 't2');

        foreach ($ts as $t) {
            $timestamps[$t] = strtotime($this->request->data['Patient'][$t]);
        }

        $this->set('timestamps', $timestamps);
        $this->set('startedTs', $this->startedTs($userPatient['SurveySession']));

        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        // set for patient_tools_links
        $this->set('sessionToday', $this->sessionToday($this->request->data));
        $this->set('finishedSession', $this->finishedSession($this->request->data));
        $this->set('medicationsPatient', $this->medicationsPatient($this->request->data));
        
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout, 
            array('ui.datepicker.js', 
                    'jquery.validate.js', 'cpro.jquery.validate.js'));

        $this->preRender();
    }// function changeUsername($id = null) {

    /**
     * Create a single T from its constituent parts
     * @param type 'T1', 'T2', etc.
     * @param type consentStatus The patient's consent status
     * @param ttime datetime, as a string
     * @param location location
     * @param staffName Staff name
     * @param timezone Timezone
     * @return The T 
     */
    private function createT($type, $consentStatus,
                                       $ttime, $location, $staffName, 
                                       $timezone) 
    {
        $day = date('D', strtotime($ttime));
    $tz = $this->DhairDateTime->tzAbbr($ttime, $timezone);
        return array('type' => 
                     ($type == 'T2' && $consentStatus != 'consented') ?
                 'T2UC' : $type,  
                 // distinguish between patients and participants
                 // at T2
                 'datetime' => $ttime,
                 'date' => substr($ttime, 0, 10) . " ($day)",
                 'time' => substr($ttime, 11) . " $tz",
                 'location' => $location,
                 'staffName' => $staffName);
    }

    /**
     * Check whether a datetime falls within a date range
     * @param $date datetime in question, as string
     * @param $start start datetime (inclusive)
     * @param $end end datetime (inclusive)
     * @return whether $date >= $start and <= $end
     */
    private function inDateRange($date, $start, $end) {
        return $date >= $start && $date <= $end;
    }

    /**
     * Show the list of upcoming appointments
     * FIXME this should only show info on patients this staff user has access to, like the other actions in this controller. It's currently bugged in that it shows all patients. 
     * @param startdate Date to start at, today if unspecified
     * @param number of days after startdate to display, 6 if unspecified
     */
    /* strtotime and other functions okay here for two reasons:
       a) We build in slack in db query to pick up any datetimes that
          might be missed due to timezone conversions.  Superfluous
          datetimes are filtered out in extractTs
       b) When we say 'show me Ts on Jan 25', we likely mean 
          Jan 25 in the local time of the T, regardless of 
      timezone.  Since
          datettimes come back from the model as strings in local
          time, we can thus compare our naively constructed datetimes
          directly with the datettimes from the model without worrying
          about timezones.  Timezone abbreviations are displayed in the 
          calendar to make this clear.
     */
    
    function calendar($startdate = null, $length = 6) {

        $timezone = $this->User->getTimeZone($this->authd_user_id);

        $timestamp = strtotime($startdate);
        $length = intval($length);

        if (empty($startdate) || empty($timestamp)) {
            $startdate = date('Y-m-d');
        }

        $enddate = date('Y-m-d', strtotime("$startdate +$length days"));

        $queryStartDT = date('Y-m-d 00:00:00', 
                               strtotime("$startdate"));
        $queryEndDT = date('Y-m-d 00:00:00', strtotime("$enddate"));

        $readablePatients = $this->Patient->findAccessiblePatients(
            $this->authd_user_id, $this->centralSupport, $this->researchStaff);

        //$this->log(__CLASS__ . "->" . __FUNCTION__ . "() readablePatients: " . print_r($readablePatients, true), LOG_DEBUG); // WARNING: a LOT of output with this!

        // re-key by Patient.id
        $readablePatients = 
            Hash::combine($readablePatients, '{n}.Patient.id', '{n}');
        
        //$this->log(__CLASS__ . "->" . __FUNCTION__ . "() readablePatients: " . print_r($readablePatients, true), LOG_DEBUG); // WARNING: a LOT of output with this!

        $readablePatientIds = 
//            Hash::extract($readablePatients, '{n}.Patient.id');
            array_keys($readablePatients);
        //$this->log(__CLASS__ . "->" . __FUNCTION__ . "() readablePatientIds: " . print_r($readablePatientIds, true), LOG_DEBUG); 

        $appts = $this->Appointment->findWNullReorder('all', array(
                'conditions' => array(
                    'Appointment.patient_id' => 
                            $readablePatientIds,
                    'Appointment.datetime >=' => 
                            $this->User->localToGmt($queryStartDT, $timezone),
                    'Appointment.datetime <=' => 
                            $this->User->localToGmt($queryEndDT, $timezone)
                    ),
                'recursive' => 2));

        $appts = $this->Appointment->getAppointmentNumberForResults($appts);

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(...), here are appts: " . print_r($appts, true), LOG_DEBUG);

        // TODO move to model?
        $clinics = $this->Clinic->find('all', array(
                                        'recursive' => -1));
        foreach($appts as &$appt){
            // populate staff_username pseudo-field
            $staff = $this->User->find('first', array(
                                    'conditions' => array(
                                        'User.id' => 
                                            $appt['Appointment']['staff_id']),
                                    'recursive' => -1)); 
            if(!empty($staff))
                $appt['Appointment']['staff_username'] = $staff['User']['username'];
            else 
                $appt['Appointment']['staff_username'] = null; 
            foreach($clinics as $clinic){
                if ($appt['Patient']['User']['clinic_id'] == 
                        $clinic['Clinic']['id']){
                    $appt['Patient']['Clinic'] = $clinic['Clinic'];
                    break;
                }
            }

            $sessionFields = array('last_session_proj', 'last_session_date', 
                                    'last_session_status');
            foreach($sessionFields as $sessionField){
                $appt['Patient'][$sessionField] 
                    = $readablePatients[$appt['Patient']['id']]['SurveySession'][$sessionField];
            }
        }

        $sortField = '{n}.Appointment.datetime';
        $sortDirection = 'asc';
        if (sizeof($appts) > 0) $appts = Set::sort($appts, $sortField, $sortDirection);
    
        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        $this->set('appts', $appts);
        $this->set('startdate', $startdate);
        $this->set('enddate', $enddate);
        $this->set('length', $length);

        $this->preRender();

        //$this->log("calendar; appts: " . print_r($appts, true), LOG_DEBUG);
    } // function calendar($startdate = null, $length = 6) {


    /**
     * Show the list of upcoming check-agains - clinicians and patients
     * @param startdate Date to start at, today if unspecified
     * @param number of days after startdate to display, 0 if unspecified
     */
  function checkAgainCalendar($startdate = null, $length = 6) {
    $timestamp = strtotime($startdate);
    $length = intval($length);

    if (empty($startdate) || empty($timestamp)) {
        $startdate = date('Y-m-d');
    }

    $enddate = date('Y-m-d', strtotime("$startdate +$length days"));

    $patients = $this->Patient->findCheckAgains($this->authd_user_id, $this->centralSupport, 
                                                $this->researchStaff, $startdate, 
                                                $enddate);
    $checkAgains = array();

    foreach ($patients as $patient) {
        $id = $patient['Patient']['id'];
        $note = $this->Note->find('first', array(
                'conditions' => array('Note.patient_id' => $id),
                'order' => 'Note.created DESC'));
        $checkAgain = $patient['Patient'];
        // Changed to check if $note exists. Was causing controller error in view
        // if there were no patient notes.
        ($note ? $checkAgain['Note'] = $note['Note'] : $checkAgain['Note'] = '');
        $checkAgain['Clinic'] = $patient['Clinic'];
        $checkAgain['first_name'] = $patient['User']['first_name'];
        $checkAgain['last_name'] = $patient['User']['last_name'];
        $checkAgain['Controller'] = 'patients';
        $checkAgains[] = $checkAgain;
    }

    if (in_array("clinicians", $this->modelsForThisDhairInstance)){

        $this->loadModel("Clinician");
        $this->loadModel("ClinicianNote");

        $clinicians = 
            $this->Clinician->findCheckAgains($this->authd_user_id, $this->centralSupport,
                                                    $startdate, $enddate);

        foreach ($clinicians as $clinician) {
            $id = $clinician['Clinician']['id'];
            $note = $this->ClinicianNote->find('first', array(
                'conditions' => array('ClinicianNote.clinician_id' => $id),
                'order' => 'ClinicianNote.created DESC'));
            $checkAgain = $clinician['Clinician'];
            $checkAgain['Note'] = $note['ClinicianNote'];
            $checkAgain['Clinic'] = $clinician['Clinic'];
            $checkAgain['Controller'] = 'clinicians';
            $checkAgains[] = $checkAgain;
        }
    }

    $sortField = '{n}.check_again_date';
    $sortDirection = 'asc';
    $Ts = Set::sort($checkAgains, $sortField, $sortDirection);

    $this->set('checkAgains', $checkAgains);
    $this->set('startdate', $startdate);
    $this->set('enddate', $enddate);
    $this->set('length', $length);

    $this->preRender();

  } // function checkAgainCalendar($startdate = null, $length = 0) {


    /**
      * Delete a Patient View Note
      */
    function deleteNote() {

        if ($this->request->data) {
        $id = $this->request->data['PatientViewNote']['id'];
            $note = $this->PatientViewNote->findById($id);

            if ($this->validPatientId($note['PatientViewNote']['patient_id'], 
                                  $this->user)) 
            {
            $this->PatientViewNote->delete($id, false);
            }
        }

    $this->render('notes', 'ajax');
    }

    /**
      * Edit a Patient View Note
      */
    function editNote() {

        if ($this->request->data) {
        $id = $this->request->data['PatientViewNote']['id'];
            $note = $this->PatientViewNote->findById($id);
            $patientId = $note['PatientViewNote']['patient_id'];

            if ($this->validPatientId($patientId, $this->user)) {
            $this->PatientViewNote->updateAll(array(
            'PatientViewNote.author_id' => $this->authd_user_id,
            // use GMT since updateAll text goes straight into the db
                'PatientViewNote.lastmod' => 
                        "'{$this->DhairDateTime->currentGmt()}'",
                /* updateAll passes strings in as they are, so use
               Sanitize::escape to quote quotes, etc. */
            'PatientViewNote.text' => "'" .
                Sanitize::escape(
                strip_tags($this->request->data['PatientViewNote']['text']))
            . "'"
            ), array('PatientViewNote.id' => $id));
            }
        }

    $this->render('notes', 'ajax');
    }

    /**
     * Delete a patient.  Only works for test patients
     * @param id Patient id
     */
    function delete($id) {

        $this->checkPatientId($id, $this->user);
        $this->request->data = $this->User->findById($id);

    if (!$this->request->data['Patient']['test_flag']) {
        $this->Session->setFlash("Only test patients can be deleted.");
            $this->redirect($this->referer());
        } else {
        /* due to cascades, deleting the user should delete the 
           corresponding Patient, 
           UserAclLeaf, Note, PatientViewNote, Consent, JournalEntry,
           SurveySession, SessionItem, SessionScale, and SessionSubscale 
           records.  PatientAssociates records are deleted due to
           HABTM relationship.  But PatientAssociateSubscales are not
           (for some reason, so do them explicitly) */
        if (in_array("associates", $this->modelsForThisDhairInstance)){
            $this->loadModel("PatientAssociateSubscale");
            $this->PatientAssociateSubscale->deleteForPatient($id);
        }
        $this->User->delete($id);
        $this->Session->setFlash("Patient $id deleted.");
            $this->redirect(array('action' => 'viewAll'));
        }
    }

    /**
     * Move a test patient's T<n> dates, treatment start date, answers dates
     * and 
     * survey session dates 1 week 
     * earlier (thus making it easier to take multiple T-sessions on the
     * same day).
     * @param id Patient id
     */
    function changeDates($id) {

        $this->checkPatientId($id, $this->user);
        $this->request->data = $this->Patient->findById($id);

        if (!$this->request->data['Patient']['test_flag']) {
            $this->Session->setFlash(
                            "Can only change dates for test patients.");
            $this->redirect($this->referer());
        } 
        else {
            $this->Patient->id = $id;

            $tsd = $this->request->data['Patient']['treatment_start_date'];
            if (!empty($tsd)) {
                $this->Patient->saveField('treatment_start_date', 
                    $this->DhairDateTime->oneWeekEarlierDate($tsd));
            }

            $sessions = $this->request->data['SurveySession'];
        
            if (!empty($sessions)) {
                $timezone = $this->User->getTimeZone($id);

                foreach ($sessions as $session) {
                    $this->SurveySession->id = $session['id'];
            /* change modified last, as any change to 'started' will
               change modified in the db */
            /* These times must be converted to GMT, as the
               model cannot do it automatically because it doesn't
               have the userid on a saveField operation */
                    $this->SurveySession->saveField('started', 
                        $this->DhairDateTime->localToGmt(
                            $this->DhairDateTime->oneWeekEarlier(
                            $session['started']),
                            $timezone));
                    $this->SurveySession->saveField('modified', 
                        $this->DhairDateTime->localToGmt(
                            $this->DhairDateTime->oneWeekEarlier(
                            $session['modified']),
                            $timezone));
                    
                    $answers = 
                        $this->Answer->findAllBySurveySessionId($session['id']);

                    foreach ($answers as $answer) {
                        $this->Answer->id = $answer['Answer']['id'];
/*%% TODO:  At the moment, retrieved answers are not converted to local
     time, but they could be in the future
 */
                        $this->Answer->saveField('modified', 
                        $this->DhairDateTime->oneWeekEarlier(
                        $answer['Answer']['modified']));
                    }
                }
            }

            $this->redirect($this->referer());
        }
    }// function changeDates($id) {
    
    /**
     * Show all patients who don't have upcoming check-agains
     * @param startdate Date to start at, today if unspecified
     */
    function noCheckAgain($startdate = null) {
        $timestamp = strtotime($startdate);

        if (empty($startdate) || empty($timestamp)) {
            $startdate = date('Y-m-d');
        }

        $patients = $this->Patient->findNoCheckAgain($this->authd_user_id, $this->centralSupport, 
                                                 $this->researchStaff, $startdate);

        foreach ($patients as $patient) {
            $id = $patient['Patient']['id'];
            $note = $this->Note->find('first', array(
                        'conditions' => array('Note.patient_id' => $id),
                        'order' => 'Note.created DESC'));
            ($note ? $notes[$id] = $note['Note'] : $notes[$id] = '');
        }
       
        $this->set('patients', $patients);
        $this->set('notes', $notes);
        $this->set('startdate', $startdate);

        $this->preRender();

    }

    /**
    *
    */
    function search(){

        if ($this->request->data) {
            //$this->DhairLogging->logArrayContents($this->request->data, "data");
            
            $patients = $this->Patient->search(
                            $this->authd_user_id, $this->centralSupport, 
                            $this->researchStaff, $this->request->data);
            //$this->DhairLogging->logArrayContents($patients, "patients");
            $this->set('patients', $patients);
            $this->set('canEdit', 
                $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        }

        $this->preRender();

    }


    /**
     * create the definition for a column in the accrual report
     * @param name Name of the column
     * @param noSites True if the column is not broken down by site; 
     *    default = false
     * @param specialTotalFunction Name of a special function to compute
     *    the value for the column in the Total array; if null, the
     *    result is just the sum of the columns for all other rows
     * @param display true if the column should be displayed
     * @return A column definition
     */
    private function createColumn($name, $noSites = false, 
                                  $specialTotalFunction = null, 
                  $display = true)
    {
        return array('name' => $name, 
                 'sites' => !$noSites,
                 'specialTotalFunction' => $specialTotalFunction,
             'display' => $display);
    }

    /**
     * Get the columns in the accrual report, as an array
     * @return The titles of the columns, ignoring the 'row title' column
     */
    private function getAccrualColumns() {
        return array($this->createColumn('t1Patients'), 
                 $this->createColumn('t1Participants'), 
                 $this->createColumn('t1Declined', false, null, false), 
             $this->createColumn('t1Target', true, 't1TargetTotal'), 
                 $this->createColumn('consentRate', true, 
                                 'consentRateTotal'),
                 $this->createColumn('t2Patients'), 
             $this->createColumn('t2Participants'), 
             $this->createColumn('t2Target', true, 't2TargetTotal'), 
             $this->createColumn('offStudyPatients'));
    }

    /** Start date for accrual report (inclusive) */
    const ACCRUAL_START_DATE = 'May 1, 2011 PST';
//    const ACCRUAL_START_DATE = 'December 1, 2007 PST';

    /** End date for accrual report (non-inclusive) */
    const ACCRUAL_END_DATE = 'May 1, 2012 PDT';

    /**
     * Get the rows in the accrual report, as an array
     * @return The rows (titles only)
     */
    private function getAccrualRows() {
        $rows = array(array('name' => 'Total'));

        $date = strtotime(self::ACCRUAL_START_DATE);
    $endDate = strtotime(self::ACCRUAL_END_DATE);

    while ($date < $endDate) {
        $dateStr = date('M Y', $date);
        $rows[] = array('name' => $dateStr, 'timestamp' => $date);
        $date = strtotime('+1 month', $date);    // add a month
    }

    return $rows;
    }

    /**
     * Get the number of completed T sessions of a particular type for a row 
     * and a site
     * @param type The type (T1, T2, ...)
     * @param row The row
     * @param site The site
     * @param patientType Patient::PATIENT if we want non-participants, 
     *                    Patient::PARTICIPANT if we want participants, 
     *                    null if we want both
     * @return The # of sessions 
     *    the row
     */
    private function tsessions($type, $row, $site, $patientType = null) {
        return $this->Patient->countTSessions($type, $row['timestamp'], 
                                          $site['Site']['id'],
                          $patientType);
    }

    /**
     * Get the number of t1 Patients (not participants) for a row and a site
     * @param row The row
     * @param site The site
     * @return The # of t1 Patients for the time period represented by the row
     */
    private function t1Patients($row, $site) {
        return $this->tsessions('T1', $row, $site, Patient::PATIENT);
    }

    /**
     * Get the number of t1 Participants for a row and a site
     * @param row The row
     * @param site The site
     * @return The # of t1 Participants for the time period represented by 
     *    the row
     */
    private function t1Participants($row, $site) {
        return $this->tsessions('T1', $row, $site, Patient::PARTICIPANT);
    }

    /**
     * Get the number of t1 patients who declined to participate for a row 
     *    and a site.  This information does not appear directly in the accrual
     *    report, but is used to compute the consent rate
     * @param row The row
     * @param site The site
     * @return The # of t1 patients who declined to participate
     */
    private function t1Declined($row, $site) {
        return $this->tsessions('T1', $row, $site, Patient::DECLINED);
    }

    /**
     * Get the percentage representing the ratio of two number, as an integer
     * @param num1 Numerator of the ratio
     * @param num2 Denominator of the ratio
     * @return num1/num2 as an integer percentage, 0 if num2 is 0
     */
    private function percentage($num1, $num2) {
        if ($num2 == 0) {
        return 0;
    } else {
            return intval(round(($num1 * 100) / $num2));
        }
    }

    /**
     * Compute the consent rate for a row
     * @param row The row
     * @return The consent rate, as an array
     */
    private function consentRate($row) {
        $participants = $row['t1Participants']['total'];
        $declined = $row['t1Declined']['total'];
    $totalPatients = $participants + $declined;
    return array('totalPercent' => 
                 $this->percentage($participants, $totalPatients));
    }

    /**
     * Compute the consent rate for the total row
     * @param row The total row
     * @param rows ignored
     * @return The consent rate, as an array
     */
    private function consentRateTotal($row, $rows) {
        return $this->consentRate($row);
    }

    /**
     * Get the number of t2 Patients (not participants) for a row and a site
     * @param row The row
     * @param site The site
     * @return The # of t2 Patients for the time period represented by the row
     */
    private function t2Patients($row, $site) {
        return $this->tsessions('T2', $row, $site, Patient::PATIENT);
    }

    /**
     * Get the number of t2 Participants for a row and a site
     * @param row The row
     * @param site The site
     * @return The # of t2 Participants for the time period represented by 
     *    the row
     */
    private function t2Participants($row, $site) {
        return $this->tsessions('T2', $row, $site, Patient::PARTICIPANT);
    }

    /**
     * Get the t1 or t2 target for a row 
     * @param row The row
     * @param type The type of target ('t1' or 't2')
     * @return The Target for the time period represented by the row
     */
    private function target($row, $type) {
        $typeUpper = strtoupper($type);
        $targetRow = $this->Target->find('first', 
        array('conditions' => array('Target.type' => $typeUpper,
                                    'Target.month' => $row['name'])));
        $target = $targetRow['Target']['target'];
        return array(
        'total' => $target, 
            'totalPercent' => 
            $this->percentage($row["{$type}Participants"]['total'], 
                          $target));
    }

    /**
     * Get the t1 target for a row 
     * @param row The row
     * @return The t1 Target for the time period represented by the row
     */
    private function t1Target($row) {
        return $this->target($row, 't1');
    }

    /**
     * Get the t2 target for a row 
     * @param row The row
     * @return The t2 Target for the time period represented by the row
     */
    private function t2Target($row) {
        return $this->target($row, 't2');
    }

    /**
     * Get a target column for the total row 
     * @param totalRow The total row
     * @param rows All other rows
     * @param type Type of target ('t1' or 't2')
     * @return The target column for the total row
     */
    private function targetTotal($totalRow, $rows, $type) {
        $sum = 0;

        foreach ($rows as $row) {
        if ($row['name'] != 'Total') {
            $sum += $row["{$type}Target"]['total'];
            }
    }

        return array(
        'total' => $sum, 
            'totalPercent' => 
            $this->percentage($totalRow["{$type}Participants"]['total'], 
                          $sum));
    }

    /**
     * Get the number of off-study participants for a row and a site
     * @param row The row
     * @param site The site
     * @return The # of off-study participants for the time period 
     *    represented by the row
     */
    private function offStudyPatients($row, $site) {
        return $this->Patient->countOffStudy($row['timestamp'], 
                                         $site['Site']['id']);
    }

    /**
     * Compute the values for a particular cell
     * @param row The row of the cell
     * @param column The column of the cell
     * @param sites All sites
     * @return The values for the cell, as an array
     */
    private function computeCellValues($row, $column, $sites) {
    $func = $column['name'];

        if (empty($column['sites'])) {
    // sites are irrelevant for this column
        return $this->$func($row);
    } else {
        $total = 0;

        foreach ($sites as $site) {
            $siteName = $site['Site']['name'];
            $result[$siteName] = $this->$func($row, $site);
        $total += intval($result[$siteName]);
            }

        $result['total'] = $total;

        return $result;
        }
    }

    /**
     * Compute all the values for a row
     * @param row The row
     * @param columns The column definitions
     * @param sites The sites
     * @return The row, all values included, as an array
     */
    private function computeRowValues($row, $columns, $sites) {
        foreach ($columns as $column) {
        $row[$column['name']] = 
            $this->computeCellValues($row, $column, $sites);
    }

        return $row;
    }

    /**
     * Compute the sum of all rows for a given column
     * @param row The rows
     * @param column The column in question
     * @param sites The sites
     * @return The sum, as an array
     */
    private function columnSum($rows, $column, $sites) {
        $name = $column['name'];
    $totalSum = 0;
    $result = array();

        foreach ($sites as $site) {
        $siteName = $site['Site']['name'];
        $sum = 0;
        
            foreach ($rows as $row) {
            if ($row['name'] != 'Total') {
                $sum += $row[$name][$siteName];
                }
        }

        $result[$siteName] = $sum;
        $totalSum += $sum;
        }

        $result['total'] = $totalSum;
    return $result;
    }

    /**
     * Compute the total row for the accrual report
     * @param rows All rows in the report
     * @param columns All columns in the report
     * @param sites All sites
     * @return The total row
     */
    private function computeTotalRow($rows, $columns, $sites) {
        $totalRow = $rows[0];

        foreach ($columns as $column) {
        if (!empty($column['specialTotalFunction'])) {  
        // special function to compute the total
        $func = $column['specialTotalFunction'];
        $result = $this->$func($totalRow, $rows);
        } else {   // just sum up the rest of the columns
            $result = $this->columnSum($rows, $column, $sites);
        }

        $totalRow[$column['name']] = $result;
    }

    return $totalRow;
    }

    /**
     * Get an array of headings for a particular statistic for all sites
     * @param name Base name of the statistic
     * @param sites Array of sites 
     */
    private function siteHeadings($name, $sites) {
        $headings = array();

        foreach ($sites as $site) {
            $headings[] = "$name [{$site['Site']['name']}]";
        }
  
        $headings[] = "$name [total]";

        return $headings;
    }
   
    /**
     * Get an array of headings for a target statistic 
     * @param name Base name of the statistic
     */
    private function targetHeadings($name) {
        return array($name, "$name [%]");
    }

    /**
     * get the headings for the accrual report in CSV
     * @param columns columns in the report
     * @param sites site in the report
     */
    private function getHeadings($columns, $sites) {
        $headings = array();

        // headings
        $headings[] = 'Month';
        $headings = array_merge($headings, 
                                $this->siteHeadings('T1s (UC)', $sites));
        $headings = array_merge($headings, 
                                $this->siteHeadings('T1s (Consent)', $sites));
        $headings = array_merge($headings, 
                                $this->targetHeadings('T1s (Target)'));
        $headings[] = 'Consent Rate';
        $headings = array_merge($headings, 
                                $this->siteHeadings('T2s (UC)', $sites));
        $headings = array_merge($headings, 
                                $this->siteHeadings('T2s (Consent)', $sites));
        $headings = array_merge($headings, 
                                $this->targetHeadings('T2s (Target)'));
        $headings = array_merge($headings, 
                                $this->siteHeadings('T2 Audio', $sites));
        $headings = array_merge($headings, $this->siteHeadings('T3s', $sites));
        $headings = array_merge($headings, $this->siteHeadings('T4s', $sites));
        $headings = array_merge($headings, 
                                $this->siteHeadings('Off-study', $sites));

        return $headings;
    }

    /**
     * Show the accrual report
     * @param useCsv If true, output CSV version
     */
    function accrualReport($useCsv = false) {
        $columns = $this->getAccrualColumns();
    $sites = $this->Site->find('all', array('fields' => array('Site.name'),
                                            'order' => array('Site.name')));
        $rows = $this->getAccrualRows();

    foreach ($rows as $key => $row) {
        if ($key != 0) {  // skip total row
            $rows[$key] = $this->computeRowValues($row, $columns, $sites);
            }
    }

    $rows[0] = $this->computeTotalRow($rows, $columns, $sites);

        $this->set('columns', $columns);
        $this->set('rows', $rows);
        $this->set('sites', $sites);
        $this->set('homeCounts', 
            array(Patient::CONTROL => 
                $this->Patient->getCount(Patient::HOME, Patient::CONTROL),
                Patient::TREATMENT => 
                $this->Patient->getCount(Patient::HOME, Patient::TREATMENT)));
        $this->set('clinicCounts', 
            array(Patient::CONTROL => 
                $this->Patient->getCount(Patient::CLINIC, Patient::CONTROL),
                Patient::TREATMENT => 
                $this->Patient->getCount(Patient::CLINIC, Patient::TREATMENT)));

        if ($useCsv) {
            $this->set('headings', $this->getHeadings($columns, $sites));

            // suppress debugging messages in output
            Configure::write('debug', 0);
            $this->layout = 'ajax';
            $this->autoLayout = false;
        $this->render('accrualCsv');
        }

        $this->preRender();

    }// function accrualReport($useCsv = false) {

    /**
     * Show the off-study report
     * @param sortField Field to sort on (default = User.last_name)
     * @param sortDirection Direction (asc/desc, default = asc)
     */
    function offStudy($sortField = 'User.last_name', $sortDirection = 'asc') {

        $patients = $this->Patient->findOffStudy($this->authd_user_id, $this->centralSupport, 
                                             $this->researchStaff);

        $patients = Set::sort($patients, '{n}.' . $sortField, $sortDirection);

        // all fields we can sort by
        $fields = array('User.last_name', 'User.first_name', 'Patient.id', 
            'Clinic.name', 'Patient.off_study_status', 
            'Patient.off_study_timestamp');
        $sortDirections = $this->sortDirectionsArray($fields, $sortDirection, 
                                                 $sortField);

        $offStudyEnumsCount = 
            $this->Patient->countOffStudyEnums($this->authd_user_id, $this->centralSupport,
                                             $this->researchStaff);
    
        $this->set('patients', $patients);
        $this->set('sortDirections', $sortDirections);
        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        $this->set('offStudyEnumsCount', $offStudyEnumsCount);

        $this->preRender();

    }

    /**
     * Save an array of patient consents to the database
     * @param consentArray Array of consents (as patientId => value, 
     *        hopefully value = 1)
     * @param the authorized user
     * @param consentField Name of the consent flag to modify
     */
    private function saveConsents($consentArray, $authUser, $consentFlag) {
    foreach ($consentArray as $id => $value) 
        {
            if (is_int($id) && $value == 1 && 
            $this->validPatientId($id, $authUser)) 
            {
            $this->Patient->id = $id;
        $this->Patient->saveField($consentFlag, 1);
            }
        }
    }

    /**
     * Show/update the list of consented patients whose consent has not been
     * verified
     */
    function consents() {

        if (!empty($this->request->data['Patient']['consent_checked']))
            $this->saveConsents(
                $this->request->data['Patient']['consent_checked'],
                $this->user, 'consent_checked'
            );

        if (!empty($this->request->data['Patient']['hipaa_consent_checked']))
            $this->saveConsents(
                $this->request->data['Patient']['hipaa_consent_checked'],
                $this->user, 'hipaa_consent_checked'
            );

    $patients = $this->Patient->findUncheckedConsents(
        $this->authd_user_id, $this->centralSupport);

        $this->set('patients', $patients);
        $this->set('showHipaaColumn', 
            $this->centralSupport || 
            (defined('HIPAA_CONSENT_SITE_ID') 
                && $this->user['Clinic']['site_id'] == HIPAA_CONSENT_SITE_ID));

        $this->preRender();

    }


    /**
     *
     */
    function reset_step_last_visited($patientId){

        $this->checkPatientId($patientId, $this->user);

        $patient = $this->Patient->findById($patientId);

        if (empty($patient['Patient']['test_flag'])){
            $this->Session->setFlash("This is not a test patient, therefore the intervention cannot be reset.");
            $this->redirect("/patients/edit/" . $patientId);
        }

        $this->Session->delete('factorsForPatient-' . $patientId);
        $this->Session->delete('factorSubscalesToDisplayForPatient-' 
                                    . $patientId);

        $patient['Patient']['farthestStepInIntervention'] = null;
        $this->Patient->save($patient["Patient"]);
        $this->Session->setFlash("Intervention has been reset.");
        $this->redirect("/patients/edit/" . $patientId);
    } 

    /**
     * Display form for medications for a patient
     * @param patientId Id of the patient (null if the form is being submitted)
     */
    // we can only edit/create medications for the current day/survey
    function medications($patientId = null) {
        if (!empty($this->request->data['User'])) {
            $patientId = $this->request->data['User']['id'];
        }

        $this->checkPatientId($patientId, $this->user);
        $patient = $this->Patient->findById($patientId);
        $finishedSurveySessions = $this->Patient->finishedSurveySessions($patientId);
        $session = $finishedSurveySessions[count($finishedSurveySessions)-1];
        
        if (empty($session)) {
            $this->Session->setFlash('You cannot enter medications without first finishing a survey session.');
            $this->redirect('/patients/edit/' . $patientId);
        }

        $appointmentId = $session['appointment_id'];

        // $medday = $this->Medday->findByAppointmentId($appointmentId);
        $medday = $this->Medday->find(
            'first', 
            array('conditions' => array('Medday.survey_session_id' => $session['id']))
        );
        $medications = $this->Meds->getAllMeds();

        if (empty($this->request->data['User'])) {
            $this->request->data = $this->request->data + $patient;
        // print the form
        } else {
        // save data and redirect
            $meddayId = empty($medday) ? null : $medday['Medday']['id'];

            $md = array();
            $md['id'] = $meddayId;
            $md['patient_id'] = $patientId;
            $md['survey_session_id'] = $session['id'];
            $now = date('Y-m-d H:i:s');
            $md['dt_created'] = empty($medday) ? 
                $now : $medday['Medday']['dt_created'];
      
            if (!empty($medday)) {
                $md['dt_modified'] = $now;
            } else {
                $md['dt_modified'] = null;
            }

            $mms = array();

            $meds = empty($medday) ? $medications : $medday['MeddayMedication'];

            foreach ($meds as $medication) {
                $mm = array();

                if (empty($medday)) {
                    $id = $medication['Medication']['id'];
                } else {
                    $id = $medication['medication_id'];
                    $mm['id'] = $medication['id'];
                }

                $mm['medication_id'] = $id;
                $amount = $this->request->data['User']['amount' . $id];
                $mm['amount'] = empty($amount) ? 0 : $amount;
                $mms[] = $mm;
            }

            $mnos = array();

            foreach($this->request->data['MeddayNonopioid'] as $mno) {
                if (!empty($mno['name'])) {
                    $mnos[] = $mno;
                }
            }

            $newMedday['Medday'] = $md;
            $newMedday['MeddayMedication'] = $mms;
           
            if (!empty($mnos)) {
                $newMedday['MeddayNonopioid'] = $mnos;
            }

            if (!empty($medday)) {
                $this->Medday->id = $meddayId;
                $this->MeddayNonopioid->deleteAll("medday_id = $meddayId");
            }

            // Add "Other Medications"
            if (
                isset($this->request->data['MeddayOtherMedication']) and
                is_array($this->request->data['MeddayOtherMedication'])
            ){
                // Filter out entries without a name
                $otherMeds = Hash::extract(
                    $this->request->data['MeddayOtherMedication'],
                    '{s}[name=/\S+/]'
                );

                if ($otherMeds){
                    // Add to array so saved during saveAll()
                    $newMedday['MeddayOtherMedication'] = $otherMeds;

                    if (isset($medday['MeddayOtherMedication'])){
                        $this->Medday->id = $meddayId;
                        $this->Medday->MeddayOtherMedication->deleteAll("medday_id = $meddayId");
                    }
                }
            }

            $this->Medday->saveAll($newMedday);

            // set MED_value
            $newId = $this->Medday->id;
            $MED = $this->Medday->calculate_MED_val($newId);
            $this->Medday->id = $newId;
            $this->Medday->saveField('MED_value', $MED);

            $this->redirect(array('action' => "edit/$patientId"));
        }

        $this->set('medications', $medications);
        $this->set('medday', $medday);
        $this->set('takeAsOptions', $this->MeddayNonopioid->getTakeAs());

        $this->set('canEdit', 
            $this->DhairAuth->isAuthorizedForUrl('Patients', 'edit'));
        // set for patient_tools_links
        $this->set('sessionToday', $this->sessionToday($patient));
        $this->set('finishedSession', $this->finishedSession($patient));
        $this->set('medicationsPatient', $this->medicationsPatient($this->request->data));

        if (in_array('other_medications', Configure::read('modelsInstallSpecific'))){
            $this->set('otherMedTypes', $this->Medday->MeddayOtherMedication->getEnumValues('type'));

            if (
                isset($medday['MeddayOtherMedication']) and
                count($medday['MeddayOtherMedication']) > 4
            )
                $this->set('otherMedMaxRows', count($medday['MeddayOtherMedication']));
            else
                $this->set('otherMedMaxRows', 4);
        }
        $this->set('maxRows', 4);
        $this->preRender();

    }// function medications($patientId = null) {


    /**
     * Build up the medday subcomponent for the appointments array
     * @param appointments Appointments array
     * @param sessions Array of sessions
     * @param meddays Array of meddays
     * @return The new appointments array
     */
    private function buildMeddaySubcomponent($appointments, $sessions, 
                                             $meddays) 
    {

        // Build an array of medday data keyed with Survey Session ids
        $meddaySessionIds = array();
        foreach ($meddays as $medday) {
            $meddaySessionIds[$medday['Medday']['survey_session_id']] = $medday;
        }

        // Associate medday data into appointments array
        foreach ($appointments as &$appointment){
            $surveySessionId = $appointment['session']['SurveySession']['id'];
            if (array_key_exists($surveySessionId, $meddaySessionIds))
                $appointment['medday'] = $meddaySessionIds[$surveySessionId];
        }

        return $appointments;
    }

    /**
     * Get all the checkboxes checked for a question in a session
     * @param sessionId Id of the session
     * @param questionId Id of the question
     * @return The checkboxes checked, as an array of Options
     */
    private function getCheckedChoices($sessionId, $questionId) {
        $checked = $this->Answer->forSessionAndQuestion($sessionId, 
                                                        $questionId); 
        $result = array();

        foreach ($checked as $optionId => $junk) {
            $result[] = $this->Option->findById($optionId);
        }

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning " . print_r($result, true), LOG_DEBUG);
        return $result;
    }

    /**
     * Get the option chosen for a question in a session
     * @param sessionId Id of the session
     * @param questionId Id of the question
     * @return The option chosen 
     */
    private function getRadioChoice($sessionId, $questionId) {
        $radio = $this->Answer->forSessionAndQuestion($sessionId, $questionId);
        $returnVal = $this->Option->findById($radio);

        // $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning " . print_r($returnVal, true), LOG_DEBUG);
        return $returnVal;
    }


    /**
     * Build an appointments array for the dashboard
     * @param sessions array of Sessions, newest to oldest
     * @param meddays array of meddays, newest to oldest
     * @return The data above, organized by appointment
     */
    private function buildAppointments($sessions, $meddays) {
        $appointments = array();

        for ($i = 0; $i < DASHBOARD_POINTS; $i++) {
            if (!empty($sessions[$i])) {
                $appointments[$i]['session'] = $sessions[$i];
            }
        }
        // build the medday part by matching appt info
        $appointments = $this->buildMeddaySubcomponent($appointments, 
                            $sessions, $meddays);

        /* add painsite, worst pain site, badday, important activity,
           treatment satisfaction info */
        foreach ($appointments as $key => $appointment) {
            
            $sessionId = $appointment['session']['SurveySession']['id'];
            $appointments[$key]['badDay'] = 
                $this->Answer->analysisValueForSessionAndQuestion($sessionId, 
                                                                  1053);
            $appointments[$key]['importantActivity'] = 
                $this->Answer->analysisValueForSessionAndQuestion($sessionId, 
                                                                  1043);
            $appointments[$key]['importantActivityDifficulty'] = 
                $this->Answer->analysisValueForSessionAndQuestion($sessionId, 
                                                                  1044);
            $appointments[$key]['painTreatmentSatisfaction'] = 
                $this->Answer->analysisValueForSessionAndQuestion($sessionId, 
                                                                  1054);

            $appointments[$key]['painSites'] = 
                $this->getCheckedChoices($sessionId, 1035);
            $appointments[$key]['worstPainSite'] = 
                $this->getRadioChoice($sessionId, 1036);
        }

        return array_reverse($appointments);
    }


    /**
     * RESTful action for opting-out and associated landing page
     * If successful, returns new opt-out status, otherwise 0
     * @param webkey
     */
    function optOut($webkey = null){

        $user = $this->User->findByWebkey($webkey);
        $patient = $this->Patient->findById($user['User']['id']);

        // If not logged in, write a session id
        if (!(isset($this->user)) && !($this->request->isAjax()))
            $this->Session->write(self::ID_KEY, mt_rand());

        if (
            empty($webkey) ||
            empty($user['User']) ||
            $webkey != $user['User']['webkey']
        ){
            $this->Session->setFlash('Not a valid Webkey or User');
            $this->redirect(array('controller' => 'users','action' => 'login'));
        }

        if ($this->request->isAjax()){
            $result = array();
            if (
                array_key_exists('response', $this->request->data) &&
                $this->request->data['response'] &&
                $patient['Patient']['off_study_status'] != Patient::WITHDRAWN
            ){
                $patient['Patient']['off_study_status'] = Patient::WITHDRAWN;
                $patient['Patient']['off_study_timestamp'] = $this->DhairDateTime->currentGmt();
                $patient = $this->Patient->save($patient);
                $result['message'] = $patient['Patient']['id'];
                $result['ok'] = true;
            }
            else {
                $this->response->statusCode(403);
                $result['ok'] = false;
                $result['message'] = 'Patient already opted-out';
            }
            $this->set($result);
            $this->set('_serialize', array('ok', 'message'));
        }        
        else
            $this->set('user', $user);

    }


    /**
     *
     */
    function sendEmail($patientId=null){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); data:" . print_r($this->data, true), LOG_DEBUG);
        if (!$this->request->isAjax()) return;
        // $this->layout = 'ajax';
        $result = array(
            'ok' => false,
            'message' => 'Failure sending email',
            // 'debug' => $this->data,
        );
        $data = $this->data;

        $this->User->bindModel(array(
            'hasMany' => array(
                'Appointment' => array('foreignKey' => 'patient_id')),
            'hasOne' => array(
                'PatientExtension' => array('foreignKey' => 'patient_id'))
        ), false);
        $data = Hash::merge(
            $data,
            $this->User->find('first', array(
                'conditions' => array('Patient.id' => $patientId),
                'contain'=>array(
                    'Patient',
                    'PatientExtension',
                    'User',
                    'Clinic'
                ),
            ))
        );

        $this->Patient->analyzeCurrentApptAndSessionState($data);
        // Find or generate Webkey
        if (
            defined('PATIENT_SELF_REGISTRATION') and
            PATIENT_SELF_REGISTRATION and
            !isset($data['Webkey']['text']) and

            // Only need webkey for registration emails ATM
            strpos($data['Patient']['emailTemplate'], 'regist') !== false
        ){
            // Extract unused self-register if possible
            $webkey = array(
                'Webkey' =>
                    array_shift(Hash::extract(
                        $data,
                        'Webkey.{n}[purpose=self-register][used_on=/$^/]'
                    ))
            );

            if (!isset($webkey['Webkey']['text'])){
                $this->Webkey->create();
                $webkey = $this->Webkey->save(array(
                    'Webkey' => array(
                        'purpose' => 'self-register',
                        'user_id' => $patientId,
                    )
                ));
            }
            if (!isset($webkey['Webkey']['text']))
                $result['message'] = 'webkey could not be found or generated';

        }
        // If user is unregistered provide anonymous access webkey
        elseif (!$data['User']['registered']){

            $webkey = array(
                'Webkey' =>
                    array_shift(Hash::extract(
                        $data,
                        'Webkey.{n}[purpose=anonymous_access]'
                    ))
            );
        }

        // Assume input is sane, only correct template will be sent from front-end
        $email = new CakeEmail();

        // Log email template name in 'params' field
        $this->paramsToLog = $this->data['Patient']['emailTemplate'];
        $this->patientIdToLog = $patientId;

        try {
            $email->from(array(
                $data['Clinic']['support_email'] => SHORT_TITLE
            ))
                ->to($data['User']['email'])
                ->subject(__($data['Patient']['emailName']))
                ->viewVars(array(
                    'user' => $data,
                    'patient' => $data,
                    'webkey' => '',
                    'patientProjectsStates' => null,
                ))
                ->emailFormat('html')
                ->template(
                    CProUtils::getInstanceSpecificEmailName($this->data['Patient']['emailTemplate'], 'html')
                );

            if (isset($webkey['Webkey']['text']))
                $email->viewVars(array('webkey' => $webkey['Webkey']['text']));

            if (isset($this->Patient->projectsStates))
                $email->viewVars(array(
                    'patientProjectsStates' => $this->Patient->projectsStates
                ));

            $email->send();
            $result['ok'] = true;
            $result['message'] = 'email sent';
        }
        catch (SocketException $e){
            $result['message'] = 'Failed to send: '.$e->getMessage();
        }
        // $result['debug'] = $data;
        $this->set($result);
        $this->set(array('_serialize' => array_keys($result)));

    }// function sendEmail(){

    /**
     * Removes multiple start-end blocks, recursively
     * @param $text to filter
     * @param $start string indicating start of block to remove (will itself be removed)
     * @param $end string indicating start of block to remove (will itself be removed)
     * @return filtered text
     */
    function removeTextBlock($text, $start, $end){
        // $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);
       // $this->log(__CLASS__ . "." . __FUNCTION__ . "(text, start=$start, end=$end)", LOG_DEBUG);

        
        // if (!strstr($text, $start) || !strstr($text, $end)){
            // $this->log(__CLASS__ . "." . __FUNCTION__ . "(...) start or end not found", LOG_DEBUG);
            // return $text;
        // }
        

        // text eg 123startJunk1end456startJunk2end789
        
        $textChunks = explode($start, $text, 2);
        $top = $textChunks[0]; // eg 123

        $textChunks = explode($end, $text, 2); 
        /* eg 0 => 123startJunk1
            1 => 456startJunk2end789
        */

        $bottom = $textChunks[1]; // eg 456startJunk2end789

        if (strstr($bottom, $start) && strstr($bottom, $end)){
            $bottom = $this->removeTextBlock($bottom, $start, $end);
        }

        $text = $top . $bottom; 

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning<<< $text>>>", LOG_DEBUG);
        return $text;
    }// function removeTextBlock($text, $start, $end){


    /**
     * Action for displaying patients that are eligible for 1-month follow-up assessment for P3P
     */
    function oneMonthFollowup(){
        $this->nMonthFollowup(P3P_1_MO_FU_PROJECT, 1, 7);
    }

    /**
     * Action for displaying patients that are eligible for 6-month follow-up assessment for P3P
     */
    function sixMonthFollowup(){
        $this->nMonthFollowup(P3P_6_MO_FU_PROJECT, 6, 14);
    }

    /**
     * Action for displaying patients that are eligible for 1-month follow-up assessment for P3P
     * $projectId
     * $nMonths = assessment should be available $n months from the randomization date
     * $cushionDays = available +/- this many days
     */
    function nMonthFollowup($projectId, $nMonths = 0, $cushionDays = 7){

        $projectIdsToMonths = array(P3P_1_MO_FU_PROJECT => 'one', 
                                P3P_6_MO_FU_PROJECT => 'six');

        $project = $this->Project->find('first', array(
                        'conditions' => array('Project.id' => $projectId),
                        'recursive' => -1));
        $this->set(compact('project'));
        $this->set(compact('nMonths'));

        $nMonthsInDays = $nMonths * 30;
        $preWindowNotificationDays = 3;

        $GMT = new DateTimeZone('GMT');
        $windowOpen = new DateTime('now', $GMT);
        $windowOpen->sub(
            new DateInterval('P'. ($nMonthsInDays + $cushionDays) .'D'));

        $windowClose = new DateTime('now', $GMT);
        $windowClose->sub(
            new DateInterval(
                'P' 
                . ($nMonthsInDays-($cushionDays + $preWindowNotificationDays))
                .'D'));

        // patients who finished the online version - to be excluded later.
        $completedPatients = $this->SurveySession->find('all', array(
            'conditions' => array(
                'SurveySession.project_id' => $projectId, 
                'SurveySession.finished' => 1,
            ),
            'fields' => array('SurveySession.patient_id'),
            'recursive' => -1
        ));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); completedPatients:" . print_r($completedPatients, true), LOG_DEBUG);
        $completedPatients = Hash::extract($completedPatients, '{n}.SurveySession.patient_id');
        // non-null one_month_fax_recd_on indicates completion of the mail version; we'll exclude these later as well too.
        $patientsWhoWontCompleteOrFaxRecd = $this->PatientExtension->find('all', array(
            'conditions' => array(
                'or' => 
                    array('PatientExtension.' . $projectIdsToMonths[$projectId] . '_month_fax_recd_on !=' => null,
                    'PatientExtension.' . $projectIdsToMonths[$projectId] . '_month_wont_complete' => '1') 
            ),
            'fields' => array('PatientExtension.patient_id'),
            'recursive' => -1
        ));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); patientsWhoWontCompleteOrFaxRecd:" . print_r($patientsWhoWontCompleteOrFaxRecd, true), LOG_DEBUG);
        $patientsWhoWontCompleteOrFaxRecd = Hash::extract($patientsWhoWontCompleteOrFaxRecd, '{n}.PatientExtension.patient_id');
        $completedPatients = array_unique(array_merge($completedPatients, $patientsWhoWontCompleteOrFaxRecd));

        $reportablePatients = $this->Patient->findWFieldInDateRange(
                        $this->authd_user_id, $this->centralSupport, 
                        $this->researchStaff,
                        "PatientExtension.primary_randomization_date", 
                        null, 
                        $windowClose, 
                        $completedPatients);

        // Modify patient records to add start/stop time
        $timezone = new DateTimeZone($this->Patient->getTimeZone($this->user));
        foreach($reportablePatients as &$patient){

            $range = $this->Patient->get_n_month_randomization_date_range($patient,
                        $nMonths, $cushionDays);
            $patient['PatientExtension']['start'] = $range[0];
            $patient['PatientExtension']['stop'] = $range[1];
        }

        $this->set(compact('reportablePatients'));
        
        $this->preRender();
        $this->render('n_month_followup');
    }// function nMonthFollowup

    /**
     * Action for reporting patients who have entered the 1 week f/u window for P3P
     */
    function oneWeekFollowup(){

        //find appts that are at least 4 days (1 one week - 3 days) in the past, ordered by appt date descending (latest first)

        $reportablePatients = $this->Patient->oneWeekFollowup(
            $this->authd_user_id, $this->centralSupport, $this->researchStaff,
            3);
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "() reportablePatients: " . print_r($reportablePatients, true), LOG_DEBUG); // WARNING: a LOT of output with this!

        $this->set(compact('reportablePatients'));
        
        $this->preRender();
    }// function oneWeekFollowup

}
?>
