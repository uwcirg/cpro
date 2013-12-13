<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

class DataExportComponent extends Component
{
    # global answer options
    const SELECTED_CHECK   =   1;
    const UNSELECTED_CHECK =   0;
    const SKIPPED          =  -9;
    const NO_SESSION       = -99;
    const NO_ANSWERS_IN_SESSION    = -999;

    // a rather arbitrary assignment, based on ESRAC2 data
    const NUM_NONT_SESSIONS_TO_REPORT = 10;
    const NONT_LABEL_PREFIX = 'nonT_';
        
    var $options = array(); 
    var $reportTable = array();
    var $patient_fields = array();
    var $labelInstanceID; // eg "esrac.dev"


    /**
     * Called after controller beforeFilter and before action handler
     *
     */

	function startup(Controller $controller)
	{
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $this->controller = $controller;

        $this->options = $this->controller->options;
      
        $this->patient_fields_for_query = 
              array('id', 'test_flag', 'clinical_service', 'user_type', 
                'birthdate', 'gender', 'study_group', 'consent_status',
                'consent_date', 'off_study_status', 'off_study_reason');

        if (array_key_exists('demographics', $this->options) &&
                $this->options['demographics']){
            $this->patient_fields = $this->patient_fields_for_query; 
        } 
        else {
            $this->patient_fields = array('id'); 
        }

        $this->labelInstanceID = INSTANCE_ID;
        if (!Configure::read('isProduction')){
            $this->labelInstanceID = $this->labelInstanceID . '.dev';
        }
//        $this->log(__FUNCTION__ . "; this>patient_fields: " . print_r($this->patient_fields, true), LOG_DEBUG);
        //$this->log("labelInstanceID: " . print_r($this->labelInstanceID, true), LOG_DEBUG);

    }

    /** data_file: create a new csv file with the data export and return the path to access it
     * @returns $path: to the csv file */
    function data_file(){
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        /* TODO NEED INTERPRETATION OF CHECKBOX OPTION DIGITS: 1=Yes,2=No - JUST DO IN EXCEL FOR NOW DON"T NEED SPECIAL LABELLING OF DEMO OPTION COLUMNS
        */
        //Configure::write('debug', 3);

        set_time_limit(120);

        $id = $this->getDateStringForFilename();
        $filename = $this->labelInstanceID . $this->options['label'] . ".$id.csv";

        if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
            // $this->log("data file $filename does not yet exist; will delete old files (keeping the latest) and create a new one next", LOG_DEBUG);

            $this->deleteOldFiles($this->options['label']);

            $questions = $this->_get_questions();
            $formattedQuestions = array();
            foreach($questions as $question) {
                array_push($formattedQuestions, 
                            array("id" => $question["Question"]["id"],
                                       "options" => $question["Option"]));
            }
            $questions = $formattedQuestions;
            $numSessionsPerPatientRow = 0;

            $patients = $this->_get_patients();
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), reporting for " . sizeof($patients) . " patients.", LOG_DEBUG);
            foreach($patients as $patient) {
                $patientData = $this->_patient_fields($patient);
                $sessions = $this->_patient_survey_sessions($patient);

                if ($this->options['row_per_session']){
                    foreach($sessions as $key => $session){
                        if(in_array($session['type'], 
                                $this->options['type_array'])) {
                            $row = array_merge(
                                    $patientData, 
                                    $this->_patient_session_answers(
                                            $questions, $session, $key));
                            array_push($this->reportTable, $row);
                        }
                    }
                }
                else { // row per patient
                    $row = $patientData;
                    if ($numSessionsPerPatientRow < sizeof($sessions))  
                                                    $numSessionsPerPatientRow = sizeof($sessions);

                    foreach($sessions as $key => $session){
                        if(in_array($session['type'], 
                                $this->options['type_array'])) {
                            $row = array_merge(
                                    $row, 
                                    $this->_patient_session_answers(
                                            $questions, $session, $key));
                        } else {
                            # print NO_SESSION for each question and modified 
                            for($i=0; $i<$this->num_headers_per_session; $i++){
                                $row[] = self::NO_SESSION;
                            }
                            continue;
                        }
                    }
                    array_push($this->reportTable, $row);
                } // row per patient
            }// foreach($patients as $patient) {

            array_unshift($this->reportTable, $this->_header_row($questions, $numSessionsPerPatientRow));

            $this->createFile($filename);
        }
        else {
            //$this->log("data file $filename exists", LOG_DEBUG);
        }
        return $filename;
    } // function data_file


    /** scores_file: create a new csv file with the scores export and return the path to access it
     * @returns $path: to the csv file */
    function scores_file() {
        //Configure::write('debug', 3);
        //$this->log(__FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        set_time_limit(120);

        $id = $this->getDateStringForFilename();
        $filename = $this->labelInstanceID . $this->options['label'] . ".$id.csv";
       

        if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
            //$this->log("scores file $filename does not yet exist; will delete old files (keeping the latest) and create a new one next", LOG_DEBUG);

            $this->deleteOldFiles('.scores' . $this->options['label']);

            //$this->patient_fields[] = "MRN";

            $subscales = $this->controller->Subscale->find('all', 
                array(
                    'recursive' => -1,
                    'order' => array('Subscale.id ASC')
            ));
            //$this->log("scores_file(...); subscales: " . print_r($subscales, true), LOG_DEBUG);
            $items = $this->controller->Item->find('all', 
                array(
                    'recursive' => -1,
                    'order' => array('Item.id ASC')
            ));
            //$this->log("scores_file(...); items: " . print_r($items, true), LOG_DEBUG);
            array_push($this->reportTable, $this->_scores_header_row(
                                                    $subscales, $items));

            $patients = $this->_get_patients();
            //$this->log("scores_file(...); patients: " . print_r($patients, true), LOG_DEBUG);
            foreach($patients as $patient) {
                $patientData = $this->_patient_fields($patient);
                $sessions = $this->_patient_survey_sessions($patient);

                if ($this->options['row_per_session']){
                    foreach($sessions as $key => $session){
                        if(in_array($session['type'], 
                                $this->options['type_array'])) {
                            $row = array_merge(
                                    $patientData, 
                                    $this->_patient_session_scores(
                                            $subscales, $items, $session, $key));
                            array_push($this->reportTable, $row);
                        }
                    }
                }
                else { // row per patient
                    $row = $patientData;
                    foreach($sessions as $key => $session){
                        if(in_array($session['type'], 
                                $this->options['type_array'])) {
                            $row = array_merge(
                                    $row, 
                                    $this->_patient_session_scores(
                                            $subscales, $items, $session, $key));
                        } else {
                            # print NO_SESSION for each question and modified 
                            for($i=0; $i<$this->num_headers_per_session; $i++){
                                $row[] = self::NO_SESSION;
                            }
                            continue;
                        }
                    }
                    array_push($this->reportTable, $row);
                } // row per patient
            }// foreach($patients as $patient) {

            $this->createFile($filename);
        }
        else {
            //$this->log("scores file $filename exists", LOG_DEBUG);
        }
        return $filename;
    } // function scores_file


    /** create a new csv file for reporting intervention dose 
     * @returns $path: to the csv file 
    */
    function intervention_dose_file() {

        set_time_limit(120);

        $id = $this->getDateStringForFilename();
        $filename = 
            $this->labelInstanceID . $this->options['label'] . ".$id.csv";

        if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
            //$this->log("file $filename does not yet exist; will delete old files (keeping the latest) and create a new one next", LOG_DEBUG);

            $this->deleteOldFiles($this->options['label']);

            $patients = $this->_get_patients();

            // don't include "birthdate" in this report
            $birthdateKey = 
                array_search('birthdate', $this->patient_fields);
            unset($this->patient_fields[$birthdateKey]);
            // don't include "test_flag" in this report
            $test_flagKey = 
                array_search('test_flag', $this->patient_fields);
            unset($this->patient_fields[$test_flagKey]);

            $header_row = $this->_patient_fields_header();

            $header_row[] = 'T1 session';

            $tsWTeachingTips = array('T2', 'T3', 'T4');
            foreach ($tsWTeachingTips as $tW){
                $header_row[] = $tW . ' session';
                $header_row[] = $tW . ' in-survey teaching tips';
            }

            $header_row[] = 'NonTSessions';
            $header_row[] = 'ErrantTSessions';
            $header_row[] = 'Associates';
            $header_row[] = 'JournalEntries';

            $interventionFields = array(
                'JournalPageVisits' => new LogEntry('results', 'showJournals'),

                'ViewMyReportsVisits' => new LogEntry('results', 'index'),
                "IndividualChartViewed" => 
                    new LogEntry('results', 'show', "(^[0-9])"),
                'RelatedChartAdditions' => new LogEntry('results', 
                                                            'customize_chart'),

                'TeachingTipsTabVisits' => new LogEntry('teaching', 'index'),
                "TeachingTipsAllExpanded" => 
                    new LogEntry('teaching', 'log_teaching_tip_exp', 
                        'Expand all teaching tips'),
                "TeachingTipsSubscaleExpandedOnTeachingTab" => 
                    new LogEntry('teaching', 'log_teaching_tip_exp', 
                        '(^[0-9])'), // eg "30,http://www.cancer.net"
                "ExternalResourceClicksFromTeachingTab" => 
                    new LogEntry('teaching', 'log_click_to_externa'),
                "TeachingTipsSubscaleExpandedOnResultsTab" => 
                    new LogEntry('results', 'show', 
                        '^teaching,log_teaching_tip_expansion'), // eg "teaching,log_teaching_tip_expansion,1,What can I do about this"
                "ExternalResourceClicksFromResultsTab" => 
                    new LogEntry('results', 'show', 
                        '^teaching,log_click_to_external_resource'), // eg "teaching,log_click_to_external_resource,1,http://www.dana-farber.org"

                'ShareMyReportsTabVisits' => new LogEntry('associates', 
                                                            'edit_list')
            );// $interventionFields = array(
            foreach($interventionFields as $key => $val){
                $header_row[] = $key;
            }

            array_push($this->reportTable, $header_row);

            foreach($patients as $patient) {

                $patientRow = $this->_patient_fields($patient);

                $numNonTs = 0;
                $numErrantTs = 0;

                $tsFound = array('T1' => false);
                foreach($tsWTeachingTips as $t){
                    $tsFound[$t] = false;
                }

                foreach ($patient['SurveySession'] as $session){
                    if ($session['type'] == 'nonT') $numNonTs += 1;
                    elseif ($session['type'] == 'errantT') $numErrantTs += 1;
                    elseif ($session['type'] == 'T1'){
                        $tsFound['T1'] = true;
                        $patientRow[] = $session['started']; 
                    }
                    elseif (in_array($session['type'], $tsWTeachingTips)){
                    // if this is T2-T4, search logs for how many views of ranked order display of teaching tips within 2 days of the appt
                    // would be nice to put this before TeachingTipsTabVisits
                        $tsFound[$session['type']] = true;
                        $patientRow[] = $session['started']; 
                        // T appts cannot be within 48 hours of eachother; there sessions will never be within 36 hours of one another.
                        $minTime = date('Y-m-d H:i:s', 
                            strtotime($session['started'] . "-36 hours"));
                        $maxTime = date('Y-m-d H:i:s', 
                            strtotime($session['started'] . "+36 hours"));
                        
                        $count = $this->controller->Log->find(
                            'count', 
                            array(
                              'conditions' => array( 
                                'Log.user_id' => $patient['Patient']['id'],
                                'Log.controller' => 'surveys',
                                'Log.action' => 'show',
                                'Log.time >=' => $minTime, 
                                'Log.time <=' => $maxTime, 
                                'Log.params REGEXP' => 315 // TEACHING_TIPS_PAGE
                              ),
                              'recursive' => -1));
                        $patientRow[] = $count; 
                    }
                } // foreach ($patient['SurveySession'] as $session){
                // pad for T sessions as needed
                foreach ($tsFound as $type => $tFound){
                    if (!$tFound){
                        $patientRow[] = 'N/A';
                        if (in_array($type, $tsWTeachingTips)){
                            $patientRow[] = 'N/A';
                        }
                    }
                }
                $patientRow[] = $numNonTs;
                $patientRow[] = $numErrantTs;
                $patientRow[] = sizeof($patient['Associate']);
                $patientRow[] = sizeof($patient['JournalEntry']);

                foreach($interventionFields as $logEntry){
                    $conditions = array(
                        'Log.user_id' => $patient['Patient']['id'],
                        'Log.controller' => $logEntry->controller,
                        'Log.action' => $logEntry->action
                    );
                    if (isset($logEntry->params)) {
                        $conditions['Log.params REGEXP'] = $logEntry->params;
                    }
                    $count = $this->controller->Log->find(
                                'count', 
                                array(
                                    'conditions' => $conditions,
                                    'recursive' => -1));
                    $patientRow[] = $count;
                }
                array_push($this->reportTable, $patientRow); 
            }// foreach($patients as $patient) {
       
            $this->createFile($filename);
        }// if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
        else {
            //$this->log("data file $filename exists", LOG_DEBUG);
        }
        return $filename;
    } // function intervention_dose_file() {


    /**
     * reports a timestamp for each visit for each visit to [controller]/index
     * columns: Patient ID, timestamp
     */
    function log_file() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        set_time_limit(120);

        $id = $this->getDateStringForFilename();
        $filename = 
            $this->labelInstanceID . $this->options['label'] . ".$id.csv";

        if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
            //$this->log("file $filename does not yet exist; will delete old files (keeping the latest) and create a new one next", LOG_DEBUG);

            $this->deleteOldFiles($this->options['label']);

            $patients = $this->_get_patients();

            $header_row = $this->_patient_fields_header();

            $header_row[] = 'timestamp';
            
            array_push($this->reportTable, $header_row);

            foreach($patients as $patient) {
                //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), patient ID " . $patient['Patient']['id'], LOG_DEBUG);

                $logEntries = $this->controller->Log->find(
                            'all', 
                            array(
                              'conditions' => array( 
                                'Log.user_id' => $patient['Patient']['id'],
                                'Log.controller' 
                                    => $this->options['controller'],
                                'Log.action' => 'index'
                              ),
                              'recursive' => -1));
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(), patient ID " . $patient['Patient']['id'] . ", found " . sizeof($logEntries) . " matching entries", LOG_DEBUG);

                foreach($logEntries as $logEntry){
                
                    $patientRow = $this->_patient_fields($patient);
                    $patientRow[] = $logEntry['Log']['time']; 
                    array_push($this->reportTable, $patientRow);
 
                }// foreach($logEntries as $logEntry){

            }// foreach($patients as $patient) {

            $this->createFile($filename);
        }// if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
        else {
            //$this->log("data file $filename exists", LOG_DEBUG);
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), done; returning $filename", LOG_DEBUG);
        return $filename;
    }// function log_file() {


    /** create a new csv file with the audio or chart codings export and return the path to access it
     * @param $type either 'AudioFile' or 'Chart'
     * @returns $path: to the csv file */
    function codings_file($type) {
        //Configure::write('debug', 3);

        //$this->log("codings_file()", LOG_DEBUG);

        $typeTableized = Inflector::tableize($type); //'audio_files' || 'charts'

        set_time_limit(120);

        $id = $this->getDateStringForFilename();
        $filename = $this->labelInstanceID . ".$typeTableized.$id.csv";

        if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
            //$this->log("scores file $filename does not yet exist; will delete old files (keeping the latest) and create a new one next", LOG_DEBUG);

            $this->deleteOldFiles(".$typeTableized");

            $patient_filter = create_function('$patient',
                'return $patient["Patient"]["consent_status"] == "consented" &&
                   $patient["Patient"]["test_flag"] == false &&
                   $patient["Patient"]["off_study_status"] != "Ineligible";');

            $this->patient_fields = 
                array('id', 'test_flag', 'user_type', 
                    'study_group', 'consent_status',
                    'off_study_status');

            $codingClass;
            $coded_item_fields = array();
            $item_codings_fields = array();
            $codings_categories_fields = array();

            switch($type) {

                case 'AudioFile':
                    $codingClass = 'AudioCoding';
                    $coded_item_fields =
                        array('patient_id', 'status', 
                                'present_during_recording', 
                                'double_coded_flag', 'agreement', 
                                'coder1_id', 'coder1_timestamp', 
                                'coder2_id', 'coder2_timestamp',
                                'recoding_timestamp');
                    $item_codings_fields = 
                        array('ref_esrac', 'ref_esrac_clinician_report', 
                            'ref_esrac_answer_qs', 'ref_esrac_journal', 
                            'ref_esrac_graphs', 'ref_esrac_teaching', 
                            'ref_esrac_external', 'ref_esrac_share');
                    $codings_categories_fields = array(
                        'initiator', 'initiator_ts', 'problem', 'problem_ts', 
                        'severity', 'severity_ts', 'pattern', 'pattern_ts', 
                        'allev_aggrav', 'allev_aggrav_ts', 
                        'request_help', 'request_help_ts', 
                        'treatment', 'treatment_ts', 'referral', 'referral_ts', 
                        'cat_ref_to_esrac', 'cat_ref_to_esrac_ts'
                    );

                break;

                case 'Chart':
                    $codingClass = 'ChartCoding';
                    $coded_item_fields =
                        array('patient_id', 'status', 
                                'double_coded_flag', 'agreement', 
                                'coder1_id', 'coder1_timestamp', 
                                'coder2_id', 'coder2_timestamp',
                                'recoding_timestamp');
                    // no fields for $item_codings_fields
                    $codings_categories_fields = array(
                        'problem', 'noted_by', 'treatment', 'reccmd_by', 
                        'referral', 'dose_mod', 'comments', 'phone'
                    );
                break;
            }

            $contain = array('User.clinic_id', 
                            $type => 
                                array(
                                    'fields' => $coded_item_fields));

            $patients = $this->_get_patients_for_coding(
                                                $patient_filter, $contain);

            $this->reportTable = array();

            $this->patient_fields = array('id'); 

            //$header_row = array('clinic_id'); 
            $header_row = array(); 
            foreach($coded_item_fields as $field){
                $header_row[] = $field;
            }
            foreach($item_codings_fields as $field){
                $header_row[] = 
                    substr($type, 0, 5) . '_' . $field;
            }

            // eg 'Appearance', 'Appetite'; approx 26
            $categories = $this->controller->Category->find(
                            'all', array('recursive' => -1));

            foreach($categories as $category){
                $categoryName = $category['Category']['name'];
                $categoryName = 
                    preg_replace('/[ \(\)-\/]+/', '', $categoryName);
                $categoryName = substr($categoryName, 0, 12);
    
                foreach($codings_categories_fields as $fields){
                    $header_row[] = $categoryName . '_' . $fields;
                }
            }

            //$this->log('header_row done: ' . print_r($header_row, true), LOG_DEBUG);
            array_push($this->reportTable, $header_row);
            //$this->log('this->reportTable array after pushing header_row: ' . print_r($this->reportTable, true), LOG_DEBUG);

            foreach($patients as $patient) {

                $patientId = $patient['Patient']['id'];
                $patientRow = array();
                //$patientRow[] = '"L' . $patient['User']['clinic_id'] . 'R"C,C';
                //$patientRow[] = $patient['User']['clinic_id'];

                if (isset($patient[$type]['patient_id'])){
                // if audio_file or chart exists

                    foreach($coded_item_fields as $field){
        
                        if (isset($patient[$type][$field])){
                            //$patientRow[] = '"l'.$patient[$type][$field].'r"';
                            $patientRow[] = $patient[$type][$field];
                        }
                        else $patientRow[] = '';
                    }

                    $double_coded = $patient[$type]['double_coded_flag'];
                    $agreement = $patient[$type]['double_coded_flag'];
                    $codingToReport = null;

                    if ($patient[$type]['status'] == 
                      'All Coding Done') { // constants refactored in trunk

                        $codings = $this->controller->$codingClass->find(
                            'all', array(
                                'conditions' => 
                                    array($codingClass . '.patient_id' => 
                                            $patientId),
                                //'fields' => $item_codings_fields,
                                'order' => $codingClass . '.id ASC')); 

                        //$this->log("codings for patient $patientId: " . print_r($codings, true), LOG_DEBUG);

                        if (($double_coded == 0) || ($agreement >= 1)){
                            $codingToReport = 
                                $codings[0];
                        }
                        else{ // double coded, report third
                            $codingToReport = 
                                $codings[2];
                        }
                    }

                    if ($codingToReport){
                        foreach($item_codings_fields as $field){
                            $patientRow[] = 
                                $codingToReport[$codingClass][$field];
                        }

                        // at this point these are in order by category_id
                        $categoryCount = 0;
                        foreach($categories as $category){

                            foreach($codings_categories_fields as $field){
                                $patientRow[] = 
                                    $codingToReport[$codingClass . 'Category']
                                                          [$categoryCount]
                                                            [$field];
                            }
                            $categoryCount += 1;
                        }
                    }
                }// if (isset($patient[$type]['patient_id'])){
                else {
                    //$this->log("patient $patientId does not have an AudioFile)", LOG_DEBUG);
                    $patientRow[] = $patientId;
                    $patientRow[] = 'coding has not yet begun'; 
                }

                array_push($this->reportTable, $patientRow); 
            }// foreach($patients as $patient) {
        
            $this->createFile($filename);
        }// if (!file_exists(APP . SECURE_DATA_DIR . DS . $filename)){
        else {
            //$this->log("scores file $filename exists", LOG_DEBUG);
        }
        return $filename;
    } // function codings_file() {


    /**
     *
     */
  function sort_by_mtime($file1,$file2) {
    $time1 = filemtime($file1);
    $time2 = filemtime($file2);
    if ($time1 == $time2) {
        return 0;
    }
    return ($time1 < $time2) ? 1 : -1;
  }


    /**
     *
     */
    function options_file() {
//        Configure::write('debug', 3);

        $questions = $this->_get_questions();

//        $this->log(__FUNCTION__ . "=>" . __CLASS__ . "(), here are questions: " . print_r($questions, true), LOG_DEBUG);

        //$options = $this->_get_options($questions);
        
        $id = $this->getDateStringForFilename();
        $filename = $this->labelInstanceID . $this->options['label'] . "$id.csv";
        $fp = fopen(APP . SECURE_DATA_DIR . DS . $filename, "w");
        fputcsv($fp, array("OptionID", "OptionType", "OptionBodyText", "OptionAnalysisValue", "QuestionID"));

        foreach($questions as $question){
            if (isset($question['Option'])){
                foreach($question['Option'] as $option) {
                    $data = array($option["id"], $option["OptionType"], $option["BodyText"], $option["Sequence"], $option["question_id"]);
                    fputcsv($fp, $data);
                }
            }
        }

        fclose($fp);
        return $filename;
    }

    /**
     *
     */
    function questions_file() {
        $questions = $this->_get_questions();
        $id = $this->getDateStringForFilename();
        $filename = $this->labelInstanceID . $this->options['label'] . "$id.csv";
        $fp = fopen(APP . SECURE_DATA_DIR . DS . $filename, "w");
        fputcsv($fp, array("QuestionID", "PageTitle", "PageHeader", "PageBodyText", "QuestionShortTitle", "QuestionBodyText"));
        foreach($questions as $question) {
            fputcsv($fp, array(
                $question["Question"]["id"],
                $question["Page"]["Title"],
                $question["Page"]["Header"],
                $question["Page"]["BodyText"],
                $question["Question"]["ShortTitle"],
                $question["Question"]["BodyText"]));
        }
        fclose($fp);
        return $filename;
    }

    /**
     *
     */
    function _header_row($questions, $numSessionsPerPatientRow) {
//        $this->log(__FUNCTION__ . "(questions, numSessionsPerPatientRow=" . $numSessionsPerPatientRow . "); heres questions: " . print_r($questions, true), LOG_DEBUG);
        $headers = $this->_patient_fields_header();

        $demographics_size = count($headers);

        $sessionLabels = array(); 

        if ($this->options['row_per_session']){
            // add column for type 
            $headers[] = 'SessionDesc';
            $sessionLabels[] = ''; // don't label each column w/ session type
        }
        else {
            for ($i = 1; $i <= $numSessionsPerPatientRow; $i++){
                $sessionLabels[] = "S" . $i; 
            }
            //$sessionLabels = $this->options['type_array'];
        }
        //$this->log(__FUNCTION__ . "; sessionLabels: " . print_r($sessionLabels, true), LOG_DEBUG);

        foreach($sessionLabels as $sessionLabel) {
            if (!$this->options['row_per_session']){
                $headers[] = $sessionLabel.'Desc';
            }
            $headers[] = $sessionLabel.'FirstAnswerTS';
            $headers[] = $sessionLabel.'LastAnswerTS';
            foreach($questions as $question) {
                $q_id = $question["id"];
                if(!isset($question["options"][0]['OptionType'])) {
                    $type = false;
                } else {
                    $type = $question["options"][0]['OptionType'];
                }

                switch($type) {
                case "checkbox": 
                case "combo-check": 
                    foreach($question["options"] as $option) {
                        $headers[] = $sessionLabel.'q'.$q_id.'o'.$option["id"];
                    }
                    break;
                default: 
                    $headers[] = $sessionLabel.'q'.$q_id;
                    break;
                }
            }
        }
        //$this->num_headers_per_session = (count($headers) - $demographics_size) / 4;
        $this->num_headers_per_session = 
                (count($headers) - $demographics_size) / sizeof($sessionLabels);
        /**$this->log(
                    "sessionLabels: " . sizeof($sessionLabels) .  
                    "; num_headers_per_session " . $this->num_headers_per_session . 
                    "; demographics_size: " . $demographics_size, 
                    LOG_DEBUG);*/
        return $headers;
    }// function _header_row($questions, $type_array, $includeNonTs=false) {

    /**
     *
     */
    function _scores_header_row($subscales, $items) {
        //$this->log(__FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        // first demographics field names then question numbers
        $headers = $this->_patient_fields_header();

        $sessionLabels = array(); 
        if ($this->options['row_per_session']){
            // add column for type 
            $headers[] = 'SessionDesc';
            $sessionLabels[] = ''; // don't label each column w/ session type
        }
        else {
            $sessionLabels = $this->options['type_array'];
        }
        //$this->log(__FUNCTION__ . "; sessionLabels: " . print_r($sessionLabels, true), LOG_DEBUG);

        foreach($sessionLabels as $sessionLabel) {
            if (!$this->options['row_per_session']){
                $headers[] = $sessionLabel.'Desc';
            }
            $headers[] = $sessionLabel.'FirstAnswerTS';
            $headers[] = $sessionLabel.'LastAnswerTS';
            foreach($subscales as $subscale) {
                $subscaleName = 
                    preg_replace('/[ \(\)-\/]+/', '', 
                                $subscale['Subscale']['name']);
                $subscaleName = substr($subscaleName, 0, 12);
                $headers[] = $sessionLabel . 'sub' .
                    $subscale['Subscale']["id"] .
                    '_' . $subscaleName;
            }
            foreach($items as $item) {
                $headers[] = $sessionLabel . 'item' .
                    $item['Item']["id"];
            }
        }

        $this->num_headers_per_session = 
                count($headers) / sizeof($sessionLabels);
        /**$this->log(
                    "type_array: " . sizeof($type_array) .  
                    "; num_headers_per_session " . $this->num_headers_per_session . 
                    "; demographics_size: " . $demographics_size, 
                    LOG_DEBUG);*/
        return $headers;
    }// function _scores_header_row


    /**
     *
     */
    function _patient_data_row_all_multisession($patient, $questions) {

    }

    /**
     *
     */
    function _patient_data_row_per_session($patient, $questions) {

    }

    /**
     * TODO delete this junk
     */
    function _patient_scores_row($subscales, $items, $patient) {
        //$this->log(__FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        return array_merge($this->_patient_fields($patient),
                           $this->_patient_sessions_scores($subscales, $items, 
                                    $patient));
    }

    /**
     *
     */
    function _patient_fields_header(){
//        $this->log(__FUNCTION__ . "; this>patient_fields: " . print_r($this->patient_fields, true), LOG_DEBUG);
        $headers = array();
        foreach($this->patient_fields as $field) {
            $headers[] = "Patient_" . $field;
        }
        if ($this->options['demographics']){
            $headers[] = "User_clinic_id";
        }
//        $this->log(__FUNCTION__ . "; returning: " . print_r($headers, true), LOG_DEBUG);
        return $headers;
    }

    /**
     *
     */
    function _patient_fields($patient) {
        $patientFields = array();
        foreach($this->patient_fields as $field) {
            $patientFields[] = $patient['Patient'][$field];
        }
        if ($this->options['demographics']){
            $patientFields[] = $patient['User']['clinic_id'];
        }
        //$this->log(__FUNCTION__ . "; returning: " . print_r($patientFields, true), LOG_DEBUG);
        return $patientFields;
    }

    /**
     * for a patient and set of questions, return array of answers 
     */
    function _patient_sessions_answers($patient, $questions) {
    }// _patient_sessions_answers

    /**
     * TODO delete this junk
     * @param $patient array with SurveySessions
     * @param $type_array array of T types to include, eg ('T1') || ('T1', 'T2', 'T3', 'T4'); can be empty array
     * @param $includeNonTs bool 
     * return array of scores for T-time sessions, ordered by session first: T's per $type_array order, then nonT's if $includeNonTs (these ordered by session ID)
     */
    function _patient_sessions_scores(
                $subscales, $items, $patient) {
        //$this->log(__FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $all_session_scores = array();
        $sessions = $this->_patient_survey_sessions($patient);

        foreach($sessions as $key => $session){
            if(in_array($session['type'], $this->options['type_array'])) {

                $all_session_scores = 
                    array_merge(
                        $all_session_scores, 
                        $this->_patient_session_scores(
                                    $subscales, $items, $session, $key));
                                    //$subscales, $items, $patient, $session, $key));
            } else {
                # print NO_SESSION for each question and modified 
                for($i=0; $i < $this->num_headers_per_session; $i++) {
                    $all_session_scores[] = self::NO_SESSION;
                }
                continue;
            }
        }
        return $all_session_scores;
    }// function _patient_sessions_scores

    /**
     * @param $patient array with SurveySessions
     * @return array with t1 => session, etc (nonT sessions are postfixed w/ a 1-based count eg nonT3), filtered by type_array, and ordered by session ID (TODO confirm)
     */
    function _patient_survey_sessions(&$patient) {
//        $this->log(__FUNCTION__ . '(' . $patient['Patient']['id'] . '), here are all sessions ' . print_r($patient["SurveySession"], true), LOG_DEBUG );

        // order not important here

        $sessions = array();
        $nonTIterator = 1;
        $sessionIterator = 1;

        foreach($patient["SurveySession"] as $session) {

            if ($session['project_id'] != $this->options['project'])
                continue;

            $type = $session["type"];
            $key;

            if (in_array($type, $this->options['type_array'])){

                if ($type == 'nonT'){
                    $key = self::NONT_LABEL_PREFIX . $nonTIterator; // eg 'nonT_7'
                    $nonTIterator += 1; 
                }
                else $key = "session" . $sessionIterator; // eg 'session1'

                if(!isset($sessions[$key])) {
                    $sessions[$key] = $session;

                    $sessions[$key]['FirstAnswerTS'] = 
                        $this->getFirstAnswerTsForSession($session);
                    $sessions[$key]['LastAnswerTS'] = 
                        $this->getFirstAnswerTsForSession($session, 'DESC');
                }
            }
            $sessionIterator += 1;
        }

//        $this->log(__FUNCTION__ . '(' . $patient['Patient']['id'] . '), returning sessions ' . print_r($sessions, true), LOG_DEBUG );

        return $sessions;
    }// function _patient_survey_sessions


    /**
     * find earliest or latest answer for this session
     */
    function getFirstAnswerTsForSession($session, $order = 'ASC'){
        $answer = $this->controller->Answer->find('first',
                array('conditions' => 
                        array('Answer.survey_session_id' =>
                                    $session["id"]), 
                    'recursive' => -1,
                    'order' => array('Answer.modified ' . $order),
                    'limit' => 1)
        );
        //$this->log(__FUNCTION__ . '(' . $session['id'] . ', ' . $order . '), answer = ' . print_r($answer, true), LOG_DEBUG );

        if (is_array($answer) && array_key_exists('Answer', $answer)){ 
            $answer = 
                date("d/m/Y H:i:s", strtotime($answer['Answer']['modified']));
        }
        else {
            $answer = self::NO_ANSWERS_IN_SESSION;
        } 

        // the following is what Barb describes as "dd/mm/yyyy hh:mm:ss"
        return $answer; 
    }


    # given questions, patient, and one session, return an array of modified time and Answers
    function _patient_session_answers(&$questions, &$session, $sessionLabel) {
        $answers = array();

        if ($session['type'] != null){
            $answers[] = $session['type'];
        }
        else $answers[] = $sessionLabel;

        /**if ($this->options['row_per_session']){
            $answers[] = $session['type'];
        }*/

        $answers[] = $session['FirstAnswerTS'];
        $answers[] = $session['LastAnswerTS'];

        set_time_limit(2);
        $session_id = $session["id"];
        foreach($questions as $question) {
            $question_id = $question["id"];

            # manipulate for no-value, not answered, skipped
            if(!isset($question["options"][0]['OptionType'])) {
                $type = false;
            } else {
                $type = $question["options"][0]['OptionType'];
            }
            $answer = 
                $this->controller->Answer->analysisValueForSessionAndQuestion(
                                                $session_id, $question_id);
            switch($type) {
            case "checkbox": 
            case "combo-check": 
                if(isset($answer) && $answer) {
                    # answered, print one selected and the rest unselected
                    foreach($question["options"] as $option) {
                        $optionId = $option["id"];
                        if(array_key_exists($optionId, $answer)) {
                            if ($answer[$optionId] != null){
                                // combo-check
                                $answers[] = $answer[$optionId]; 
                            }
                            else {
                                $answers[] = self::SELECTED_CHECK;
                            }
                        } else {
                            $answers[] = self::UNSELECTED_CHECK;
                        }
                    }

                } else {
                    # not answered, print all as skipped
                    $options = count($question["options"]);
                    for($i=0;$i<$options;$i++) {
                        $answers[] = self::SKIPPED;
                    }
                }
                break;
            case "textbox":
                if(isset($answer) && $answer || $answer === 0) {
                    $answer = $this->cleanTextForCSV($answer);
                    $answers[] = $answer;
                } else {
                    $answers[] = self::SKIPPED;
                }
                break;
            default:
                if(isset($answer) && 
                    ($answer || (intval($answer) === 0))) {
                    $answers[] = $answer;
                } else {
                    $answers[] = self::SKIPPED;
                }
                break;
            }
        }
        return $answers;
    } //function _patient_session_answers(&$questions, &$patient, &$session) {

    /**
     *# given subscales, items, patient, and one session, return an array of modified time and scores 
     *
     */
    function _patient_session_scores(
                &$subscales, $items, &$session, $sessionLabel) {
        $scores = array();

        if ($session['type'] != null){
            $scores[] = $session['type'];
        }
        else $scores[] = $sessionLabel;

        /**if ($this->options['row_per_session']){
            $scores[] = $session['type'];
        }*/

        $scores[] = $session['FirstAnswerTS'];
        $scores[] = $session['LastAnswerTS'];

        set_time_limit(2);
        foreach($subscales as $subscale) {
            $score = $this->controller->SessionSubscale->find('first',
                array('conditions' => 
                        array('SessionSubscale.survey_session_id' =>
                                    $session["id"], 
                                'SessionSubscale.subscale_id' => 
                                    $subscale['Subscale']['id']),
                    'recursive' => -1,
                    'order' => array('SessionSubscale.id DESC'),
                    'limit' => 1)
            );
            if(isset($score)) {
                $scores[] = $score['SessionSubscale']['value'];
            } else {
                $scores[] = self::SKIPPED;
            }
        }
        foreach($items as $item) {
            $score = $this->controller->SessionItem->find('first',
                array('conditions' => 
                        array('SessionItem.survey_session_id' =>
                                    $session["id"], 
                                'SessionItem.item_id' => 
                                    $item['Item']['id']),
                    'recursive' => -1,
                    'order' => array('SessionItem.id DESC'),
                    'limit' => 1)
            );
            if(isset($score)) {
                $scores[] = $score['SessionItem']['value'];
            } else {
                $scores[] = self::SKIPPED;
            }
        }
        return $scores;
    } // function _patient_session_scores(&$subscales, $items, &$session) {

    /*
     *
     */
    function _get_options($questions) {
        $conditions = array();

        $all = $this->controller->Option->find('all', 
                array('conditions' => $conditions));
    }


    /*
     *
     */
    function _get_patients() {

        $contain = array(
            // Removed to prevent self-referencing model
            // 'Patient' => array(
                // 'fields' => $this->patient_fields_for_query
            // ),
            'SurveySession' => array(
                'fields' => array('id', 'type', 'started', 'project_id'),
                'order' => 'SurveySession.id ASC'
            ),
            'User.clinic_id'
        );
        if (isset($this->options['max_sessions'])){
            $contain['SurveySession']['limit'] = $this->options['max_sessions'];  
        }
//        $this->log(__FUNCTION__ . "() here's contain: " . print_r($contain, true), LOG_DEBUG);

        //$this->log("_get_patients, next is Patient>find all w/ contain", LOG_DEBUG);
        // cludge: so Patient.beforeFind doesn't bind 
        $this->controller->Patient->bindModels = false;
        $this->controller->Patient->Behaviors->attach('Containable');
        $allPatients = $this->controller->Patient->find(
                        'all',
                        array(
                          'fields' => $this->patient_fields_for_query,
                          'contain' => $contain 
                        ));

//        $this->log("_get_patients; allPatients: " . print_r($allPatients, true), LOG_DEBUG);
//        $this->log("_get_patients, next is array_filter", LOG_DEBUG);
        $patients = array_filter($allPatients, 
                                    $this->options['patient_filter']);
        
//        $this->log("_get_patients, returning patients:" . print_r($patients, true), LOG_DEBUG);

        return $patients;
    }// function _get_patients


    /*
     *
     */
    function _get_patients_for_coding($patient_filter, $contain = null) {
        if(!isset($patient_filter) or !is_callable($patient_filter)) {
            $patient_filter = create_function('$item', "return true;");
        }

        //$this->log("_get_patients_for_coding; here's contain: " . print_r($contain, true), LOG_DEBUG);

        // cludge: so Patient.beforeFind doesn't bind 
        $this->controller->Patient->bindModels = false;
        $this->controller->Patient->Behaviors->attach('Containable');
        $allPatients = $this->controller->Patient->find(
                        'all',
                        array(
                          //'fields' => $this->patient_fields,// foobars contain
                          'order' => 'Patient.id ASC', 
                          'contain' => $contain 
                        ));

        /*$this->log("_get_patients_for_coding; allPatients: " . 
                    print_r($allPatients, true), LOG_DEBUG);*/
        //$this->log("_get_patients_for_coding, next is array_filter", LOG_DEBUG);
        $patients = array_filter($allPatients, $patient_filter);
        
        //$this->log("_get_patients_for_coding, returning patients:" . print_r($patients, true), LOG_DEBUG);

        return $patients;
    }


    function _get_questions() {
        if(
            !isset($this->options['question_filter']) or
            !is_callable($this->options['question_filter'])
        )
            $this->options['question_filter'] = create_function('$item', 'return true;');
        // $questions = array_filter($this->controller->Question->allShownIds(), $question_filter);
//        $this->log('options: '.print_r($this->options, true), LOG_DEBUG);
        $questions = array_filter(
            $this->controller->Project->findQuestionsInSequence($this->options['project']),
            $this->options['question_filter']
        );
//        $this->log(__FUNCTION__ . "() returning " . print_r($questions, true), LOG_DEBUG);
        return $questions;
    }

    // TODO delete this junk
    function formatReportArray($shown) {
        $results = array();
        foreach($shown as $question) {
            array_push($results, array("id" => $question["Question"]["id"],
                                       "options" => $question["Option"]));
        }
        return $results;
    }


    private function cleanTextForCSV($string) {
        $string = str_replace("\n", '\\n', $string);
        $string = str_replace("\r", '\\r', $string);
        return $string;
    }


    private function getDateStringForFilename(){
        return date('Y-m-d');
    }


    private function deleteOldFiles($label = ''){

        // delete all files matching the file name, except the one with the lastest mod time (so there will still be one if this file creation fails)
        $olderFilesForThisReport = 
            glob(APP . SECURE_DATA_DIR . DS . $this->labelInstanceID . "$label.[0-9]*.csv");
        usort($olderFilesForThisReport, array($this, "sort_by_mtime"));
        unset($olderFilesForThisReport[0]); // save the most recent
        //$this->log("old report files to delete: " . print_r($olderFilesForThisReport, true), LOG_DEBUG);
        
        foreach ($olderFilesForThisReport as $oldFile){
            unlink ($oldFile);
        }
    }


    private function createFile($filename){
        $fp = fopen(APP . SECURE_DATA_DIR . DS . $filename, "w");
        foreach($this->reportTable as &$row) {
            //$this->log('fputcsving row, which has first elem: ' . $row[0], LOG_DEBUG);
            fputcsv($fp, $row);
        }
        fclose($fp);
        //$this->log("finished creating report file $filename", LOG_DEBUG);
    }

} // class DataExportComponent extends Component


class LogEntry {
    var $controller;
    var $action;
    var $params;

    function __construct($c, $a, $p = null){
        $this->controller = $c;
        $this->request->action = $a;
        $this->request->params = $p;
    }
}


?>
