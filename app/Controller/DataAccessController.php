<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class DataAccessController extends AppController {

    var $name = "DataAccess";
    var $uses = array("SurveySession", 
                      "Project",
                      "Question",
                      "Option",
                      "Answer",
                      "Subscale",
                      "SessionSubscale",
                      "Item",
                      "SessionItem",
                      "Clinic",
                      "User", 
                      "Patient",
                      "Log"/**,
                      "AudioFile",
                      "AudioCoding",
                      "AudioCodingsCategory",
                      "Chart",
                      "ChartCoding",
                      "ChartCodingsCategory",
                      "Category"*/
                  );
    var $components = array("DataExport");

    var $options = array();

    /*
     *
     */
    function beforeFilter() {
        parent::beforeFilter();
        //Configure::write('debug', 0);
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's request->query" . print_r($this->request->query, true), LOG_DEBUG);
        // Try using http://php.net/manual/en/function.parse-str.php ?
        // "first=value&arr[]=foo+bar&arr[]=baz";
        // if this isn't called via url (eg via shell), initOptions will need to be called explicitly
        if (sizeof($this->request->query) > 0){
            $this->initOptions($this->request->query);
        }

        $this->TabFilter->selected_tab("Data Access");
        $this->TabFilter->show_normal_tabs();
    }


    /*
     * @param $options array. Required keys: 'project' 
     */
    function initOptions($options){

        // $this->log(__FUNCTION__ . "(); args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $this->options['project'] = $options['project'];

        // type_array e.g. 'T1.T2.T3.T4.nonT'
        // only report on sessions of these types. 
        // If not passed, only those w/ type == null will be reported on. 
        // Note that a patient cannot have more than one session of any type.
        // Sessions reported in order by ID / origination time
        // nonT only included if row_per_session == true
        if (array_key_exists('type_array', $options) && 
                                !empty($options['type_array'])){
            $this->options['type_array'] = explode('.', $options['type_array']);

        }
        else{
            $this->options['type_array'] = array(0 => null); 
        }
        // maximum # sessions to report on; applied after type_array filtering
        // eg '1' | '2'
        // If not passed, all sessions are reported on
        if (array_key_exists('max_sessions', $options) && 
                                !empty($options['max_sessions'])){
            $this->options['max_sessions'] = $options['max_sessions'];
        }
        else {
            $this->options['max_sessions'] = null;
        }
        // eg 'nonTs' | 'blah'
        if (array_key_exists('label', $options) && 
                                !empty($options['label'])){
            $this->options['label'] = '.' . $options['label'];
        }
        else {
            $this->options['label'] = '';
        }
        // eg true | false
        // whether to include the patient demographics section
        if (array_key_exists('demographics', $options) && 
                                !empty($options['demographics'])){
            $this->options['demographics'] = $options['demographics'];
        }
        else {
            $this->options['demographics'] = false;
        }
        // eg true | false
        // if false, nonT's are not reported
        if (array_key_exists('row_per_session', $options) && 
                                !empty($options['row_per_session'])){
            $this->options['row_per_session'] = $options['row_per_session'];
        }
        else {
            $this->options['row_per_session'] = false;
            $indexNonT = array_search('nonT', $this->options['type_array']);
            if ($indexNonT){
                $this->log('When requesting nonT data, row_per_session must be true', LOG_ERROR);
                unset($this->options['type_array'][$indexNonT]);
            }
        }
        // eg participant | tx | non_test
        if (array_key_exists('patient_filter', $options) && 
                                !empty($options['patient_filter'])){
            //$this->log('patient_filter key exists, its:' . $options['patient_filter'], LOG_DEBUG);
            $this->options['patient_filter'] = 
                // eg create_tx_criteria_fxn 
                call_user_func(array($this, 
                    'create_' . $options['patient_filter'] . '_criteria_fxn'));
        }
        else {
            //$this->log('patient_filter key does not exist', LOG_DEBUG);
            $this->options['patient_filter'] = 
                $this->create_participant_criteria_fxn();
        }
        // eg ethnicity 
        if (array_key_exists('question_filter', $options) && 
                                !empty($options['question_filter'])){
            $this->options['question_filter'] = 
                // eg create_ethnicity_question_filter_fxn 
                call_user_func(array($this, 
                    'create_' . $options['question_filter'] . 
                        '_question_filter_fxn'));
        }
        // eg 'teaching' | 'results'
        if (array_key_exists('controller', $options) && 
                                !empty($options['controller'])){
            $this->options['controller'] = $options['controller'];
        }

//         $this->log("initOptions() done, options: " . print_r($this->options, true), LOG_DEBUG);

    }// function initOptions($options)


    # View to show to research staff
    function index() {
        $clinics = $this->Clinic->find('all');
        $this->set('clinics', $clinics);

        $projects = $this->Project->find('all',
                        array('recursive' => -1));
        $this->set('projects', $projects);
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's SurveySession->ARRAY_TYPES:" . print_r($this->SurveySession->ARRAY_TYPES, true), LOG_DEBUG);
        $this->set('allSessionTypes', $this->SurveySession->ARRAY_TYPES);
    }

    /**
     *
     */
    function data_export() {
        //Configure::write('debug', 3);
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        //$this->log("data_export() via web app", LOG_DEBUG);

        $filename = $this->DataExport->data_file();

        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function scores_export() {
        //Configure::write('debug', 3);
        //$this->log("scores_export() via web app", LOG_DEBUG);

        $filename = $this->DataExport->scores_file();

        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function intervention_dose_export() {
        //Configure::write('debug', 3);
        //$this->log("intervention_dose_export() via web app", LOG_DEBUG);

        //$patient_filter = $this->create_tx_criteria_fxn();

        $filename = $this->DataExport->intervention_dose_file();
        //$filename = $this->DataExport->intervention_dose_file($patient_filter);
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function log_export() {
//        Configure::write('debug', 3);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $filename = $this->DataExport->log_file();
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function create_non_test_criteria_fxn(){
       //$this->log(__FUNCTION__ . "()", LOG_DEBUG);
       $patient_filter = create_function('$patient', 
            'return $patient["Patient"]["test_flag"] == false;');
        return $patient_filter;
    }

    /**
     *
     */
    function create_participant_criteria_fxn(){
       //$this->log(__FUNCTION__ . "()", LOG_DEBUG);


       $patient_filter = create_function('$patient', 
           'return $patient["Patient"]["consent_status"] == "' .
                Patient::CONSENTED . '" &&
                $patient["Patient"]["test_flag"] == false &&
                $patient["Patient"]["off_study_status"] != "' .
                Patient::OSINELIGIBLE . '";');

        return $patient_filter;
    }

    /**
     *
     */
    function create_tx_criteria_fxn(){
       //$this->log(__FUNCTION__ . "()", LOG_DEBUG);
       $patient_filter = create_function('$patient',
            'return $patient["Patient"]["consent_status"] == "' .
                Patient::CONSENTED . '" &&
                $patient["Patient"]["test_flag"] == false &&
                $patient["Patient"]["study_group"] == "' .
                Patient::TREATMENT . '" &&
                $patient["Patient"]["off_study_status"] != "' .
                Patient::OSINELIGIBLE . '";');

        return $patient_filter;
    }

    /**
     *
     */
    function create_ethnicity_question_filter_fxn(){
        //$this->log(__FUNCTION__ . "()", LOG_DEBUG);
        return create_function('$question',
            'return $question["Question"]["id"] == ' . RACE_QUESTION . ' || 
                    $question["Question"]["id"] == ' . HISPANIC_QUESTION . ';');
    }

    /**
     *
     */
    function create_demographics_question_filter_fxn(){
        //$this->log(__FUNCTION__ . "()", LOG_DEBUG);
        return create_function('$question',
            'return $question["Page"]["questionnaire_id"] == ' 
                . DEMOGRAPHICS_QNR . ';');
    }

    /**
     *
     */
    function options_export() {
        $filename = $this->DataExport->options_file();
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function questions_export() {
        $filename = $this->DataExport->questions_file();
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function time_submitted_export() {
        $filename = $this->DataExport->time_submitted_file();
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function audio_codings_export() {
        //Configure::write('debug', 3);
        //$this->log("audio_codings_export() via web app", LOG_DEBUG);

        //$patient_filter = $this->create_non_test_criteria_fxn();

        $filename = $this->DataExport->codings_file('AudioFile');
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function chart_codings_export() {
        //Configure::write('debug', 3);
        //$this->log("chart_codings_export() via web app", LOG_DEBUG);

        //$patient_filter = $this->create_non_test_criteria_fxn();

        $filename = $this->DataExport->codings_file('Chart');
        $this->downloadSecureFile($filename);
    }

    /**
     *
     */
    function downloadSecureFile($filename){
        $this->layout = 'ajax'; // so view does all output
        $this->set('sourcefile', APP . SECURE_DATA_DIR . DS . $filename);
        $this->set('destfile', $filename);
        $this->set('contentType', 'text/csv');

        $this->render('/DataAccess/download'); 
    }

    /**
     *
     */
    function downloadSecureFileViaMediaView($filename){
        $this->layout = 'ajax'; // so view does all output
        $this->view = 'Media';
        $this->autoRender = false;
        $this->autoLayout = false;
        $params = array(
            'id' => $filename,
            'name' => str_replace('.csv', '', $filename),
            'download' => true,
            'extension' => 'csv',
            'mimeType' => array('csv' => 'text/csv'),
            'path' => APP . SECURE_DATA_DIR . DS
        );
        $this->set($params);
        //$this->render('download'); 
    }


}
