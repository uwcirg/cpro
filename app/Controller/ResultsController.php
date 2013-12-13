<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class ResultsController extends AppController {

    var $uses = array("SurveySession", 
				"Tip",
				"Question",
				"User", 
				"Patient", 
				"Associate", 
				"PatientAssociate", 
				"Project", 
				"JournalEntry",
				"Scale", 
				"Subscale", 
				"Item", 
				"PatientAssociateSubscale",
				"SessionScale",
				"SessionItem",
				"Answer",
				"Option",
				"SessionSubscale");

    var $helpers = array("Html");
    var $patientIsAuthdUser;
    var $patientAssociate;
    var $scales;
    var $sharedSubscales;
    var $sessionLastAnswerDTs = array();


    function beforeFilter() {
        parent::beforeFilter();

        if (in_array("activity_diary_entries", $this->modelsForThisDhairInstance)){
            //$this->loadModel("ActivityDiaryEntries");
            $this->loadModel("ActivityDiaryEntry");
        }

        //$this->DhairLogging->logArrayContents($this->request->params, "params");
        //$this->DhairLogging->logArrayContents($this->user, "this->user");
    }

    function beforeRender() {
        $this->TabFilter->show_normal_tabs();
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('jquery.js',
                'bootstrap.js',
                'cpro.controllers.js',
                'jquery.jeditable.js',
                'excanvas.js',
                'ui.datepicker.js',
                'jquery.template.js',
                'ui.tabs.js',
                'ui.position.js',
                'ui.widget.js',
                'cpro.jquery.js',
                'jquery.validate.js',
                'cpro.jquery.validate.js',
                'fix.png.js',
                'highcharts.src.js'));
        parent::beforeRender();
    }

    function index()
    {
        $this->_initPatientForSelfUse();
        $this->_initScalesAndSubscales(false);
        $this->_initScaleAndSubscaleSessionData();
        //$this->set("link", array('action' => 'show', 'id' => '1'));
    }

    /**
    * Show list of this user's associates
    */
    function others(){

        $associate = $this->Associate->findById($this->authd_user_id);

        if (!empty($associate)){
                $patientAssociates =
                    $this->PatientAssociate->forAssociate($this->authd_user_id);

        if(count($patientAssociates) == 1) {
            $p_id = $patientAssociates[0]["Patient"]["id"];
            $this->redirect("/results/othersReportsList/$p_id");
        }
        $this->set('patientAssociates', $patientAssociates);
        $this->TabFilter->selected_tab("View Reports");
        }
    }

    /**
    * Same as index, but for associate or staff to view patient's reports
    */
    function othersReportsList($patientId){
        $this->_initPatientForReadByAssoc($patientId);
        $this->_initScalesAndSubscales();

        //$this->set('patientAssociates', $this->patientAssociates);
        //$this->DhairLogging->logArrayContents($this->scales, "scales, wazzup");
        //$this->set("link", array('action' => 'show', 'id' => '1'));
        $this->_initScaleAndSubscaleSessionData();
        $this->set("journalShared", 
            $this->patientAssociate['PatientAssociate']['share_journal']);
    }

    function show($selected_subscale_id) {
		
		$this->set('siteId', $this->user["Clinic"]["site_id"]); // Added so teaching tips can be show/hidden by site when view a particular results page
		
        $this->_initPatientForSelfUse();

        $this->_initShowChart($selected_subscale_id, true, true);

        if (in_array('associates', Configure::read('modelsInstallSpecific'))){
            // count of associates for this subscale
            $associates = 
                $this->PatientAssociateSubscale->countForPatientAndSubscale(
                    $this->authd_user_id,
                    $selected_subscale_id);
            $associate_count = $associates[0]['count'];
            $associate_text  = "people";
            if(!isset($associate_count)) {
                $associate_count = 0;
            } elseif($associate_count == 1) {
                $associate_text = "person";
            }
            $this->set('associate_count', $associate_count);
            $this->set('associate_text', $associate_text);
        }

        /* for testers, get a link back to the most recent finished
	   session and question associated with particular set of results */
	    if (!Configure::read('isProduction') &&
	        $this->patient['Patient']['test_flag']) 
        {
            /* get the highest numbered finished session (presumably the 
	       most recent) */
	      $sessions = 
	        $this->SessionSubscale->reportablesForSubscaleAndPatient(
	            $selected_subscale_id, $this->patient['Patient']['id']);

            if (count($sessions) > 0) {
                $sessionId = 
		          $sessions[count($sessions)-1]['SurveySession']['id'];

                /* Per Barbara, only create a link if there is a single 
		        question that generated the result */
                $subscale = $this->Subscale->findById($selected_subscale_id);

		        if (count($subscale['Item']) == 1) { 
		            $questionId = $subscale['Item'][0]['question_id'];

	                $this->set('surveyLink', 
		               "/surveys/restart/$sessionId/$questionId");
                }
            } // otherwise, there is no survey session to link back to
	    }
    } // function show($selected_subscale_id) {

    function showToOthers($selected_subscale_id){
        // todo read session var for patient ID ?
        
        $this->_initPatientForReadByAssoc();
        if (!in_array($selected_subscale_id, $this->sharedSubscales)){
            $this->Session->setFlash(
                "Sorry, the logged in user does not have access " .
                "to that report for this patient.");
            $this->redirect("/users/othersReportsList(" . 
                $this->patient['Patient']['id'] . ")");
        }
        
        $this->_initShowChart($selected_subscale_id, true, true);
    }

    function showJournals() {
        $this->_initPatientForSelfUse();
        $patientJournalEntries =
                $this->JournalEntry->displayedFor(
                    $this->patient['Patient']['id']);
        $this->_initJournalsOnly($patientJournalEntries);
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('ui.datepicker.js'));
        $this->render('show');
    }


    /**
     * "Triple" plot 
     */
    function show_activity_diary_data($selected_subscale_id, $daysBack = 14)
    {
        $this->_initPatientForSelfUse();
        // FIXME only need ActivityDiaryEntry scale data
        $this->_initScalesAndSubscales(false);
        $scale =& $this->scales['ActivityDiaryEntry'];

        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('highcharts.src.js'));


        // FIXME use name instead of id
        $this->set('subscaleName', $selected_subscale_id);

        $showAll = true;
        $startDate = null;
        if (/**isset($daysBack) &&*/ is_numeric($daysBack)){
            $showAll = false;
            $startDate = date('Y-m-d', strtotime(1 - $daysBack . " days"));
        }

        $entries = 
            $this->ActivityDiaryEntry->getData($this->authd_user_id, $startDate);

        $strings_data = array('fatigue' => '[', 
                                'steps' => '[', 
                                'minutes' => '[');

        foreach($entries as $entry){
            $date = $entry['ActivityDiaryEntry']['date'];
            // date like 2011-03-30
            // translate date to noon (overkill, but makes any js timezone conversions inconsequential)
            // translate date (GMT) to unix time
            $m = substr($date, 5, 2);
            $d = substr($date, 8, 2);
            $y = substr($date, 0, 4);
            $date = gmmktime(12,0,0,$m,$d,$y) . '000'; // convert s to ms
            //$this->log("gmmktime(12,0,0,$m,$d,$y), w/ '000' added to tail: " . $datum, LOG_DEBUG);
                       
            foreach($strings_data as $field => $val){

                $datum = $entry['ActivityDiaryEntry'][$field];
                if (is_null($datum) || $datum =='') {
                    continue;
                }
                $strings_data[$field] .= "[" . $date . "," . $datum;
                $strings_data[$field] .= "],";        
            }
        }

        foreach($strings_data as $field => $val){
            $strings_data[$field] = substr($val, 0, -1);// remove trailing comma
            $strings_data[$field] .= "]";        
        }

        $this->set('strings_data', $strings_data);
        //$this->log("strings_data: " . print_r($strings_data, true), LOG_DEBUG);
				
				$this->set('entries', $entries); // Used to output "My Results" stats
				$this->set('showAll', $showAll); 
				
    } // function show_activity_diary_data($selected_subscale_id)


    function _initJournalsOnly($patientJournalEntries) {
        if (in_array('associates', Configure::read('modelsInstallSpecific'))){
            # Find associate count
            $associate_count = 
                $this->PatientAssociate->countForPatientJournal($this->authd_user_id);
            $associate_text  = "people";
            if(!isset($associate_count)) {
                $associate_count = 0;
            } elseif($associate_count == 1) {
                $associate_text = "person";
            }
            $this->set('associate_count', $associate_count);
            $this->set('associate_text', $associate_text);
        }
        $this->set('journalEntries', $patientJournalEntries);
        $this->set('showCharts', false);
        $this->set('subscaleName', "Journals");
        $this->set('teachingTip', false);
    }

    function showJournalsToOthers($patientId) {
        $this->_initPatientForReadByAssoc($patientId);
        //$this->set('siteId', $this->patient['Clinic']['site_id']); 
        $patientJournalEntries =
                $this->JournalEntry->displayedFor(
                    $this->patient['Patient']['id']);
        $this->_initJournalsOnly($patientJournalEntries);
        $this->render('showToOthers');
    }


    function _initShowChart($selected_subscale_id, $showTip, $showJournal) {
        $this->set('showCharts', true);
        $this->set('siteId', $this->user["Clinic"]["site_id"]);
        $this->_initScalesAndSubscales();

        $subscale = $this->Subscale->findById($selected_subscale_id);
        $scale = $subscale["Scale"];
        $subscale = $subscale["Subscale"];
        //$js_string = '';
        $subscalesData = array();
        if($scale['id'] == QOL_SCALE){
            // special attention because it's subscales have a variety of ranges
            $subscaleMin = $subscale["base"];
            $subscaleMax = $subscale["base"] + $subscale["range"];
            $translatedMin = 0;
            $translatedMax = 100;
            $subscalesData = 
                $this->_initSubscaleSessionDataForChart(
                        $selected_subscale_id, 
                        $subscaleMin, $subscaleMax,
                        $translatedMin, $translatedMax);
            /**$js_string = 
                $this->_initSubscaleSessionDataForChartOLD(
                        $selected_subscale_id, 
                        $subscaleMin, $subscaleMax,
                        $translatedMin, $translatedMax);*/
            $scaleCritical = $scale["critical"];
            $translatedCritical = null; 
            if ($scaleCritical != null){
                $translatedCritical = 
                    ($scaleCritical / ($subscaleMax - $subscaleMin)) * 
                        ($translatedMax - $translatedMin);
            }
            $this->set('subscaleMin', $translatedMin);
            $this->set('subscaleMax', $translatedMax);
            $this->set('scaleCritical', $translatedCritical);
        }
        else{
            $subscalesData =
                $this->_initSubscaleSessionDataForChart($selected_subscale_id);
            /**$js_string =
                $this->_initSubscaleSessionDataForChart($selected_subscale_id);*/
            $this->set('subscaleMin', $subscale["base"]);
            $this->set('subscaleMax', $subscale["base"] + $subscale["range"]);
            $this->set('scaleCritical', $scale["critical"]);
        }
        
        //$this->set('subscaleData', $js_string);
        $this->set('subscalesData', $subscalesData);
        $this->set('subscaleId', $selected_subscale_id);
        $this->set('scaleInverse', $scale['invert']);

        if($showJournal) {
            $patientJournalEntries =
                $this->JournalEntry->displayedFor(
                    $this->patient["Patient"]["id"]);
            $this->set('journalEntries', $patientJournalEntries);
        } else {
            $this->set('journalEntries', false);
        }

        if($showTip) {
            $this->Tip->forSubscaleAndPatient($subscale, $this->Auth->user('id'));
            $teachingTip = $subscale["TeachingTip"];
            $this->set('teachingTip', $teachingTip);
        }
        
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('cpro.controllers.js'));
    }// function _initShowChart($selected_subscale_id, $showTip, $showJournal) {


    function _initPatientForSelfUse(){

        //$this->patientIsAuthdUser = array_key_exists("Patient", $this->user);
        $this->patientIsAuthdUser = isset($this->user['Patient']['id']);
	if ($this->patientIsAuthdUser) {
	    $this->patient = 
                $this->Patient->findById($this->user['Patient']['id']);
            $this->set('patient', $this->patient); 
	} else{
           $this->Session->setFlash(
                            "Sorry, the logged in user is not a patient.");
           $this->redirect("/users");
        }
        $this->TabFilter->selected_tab("View My Reports");
    }


    function _initPatientForReadByAssoc($patientId = null){

        # TODO: it would be nice if we just kept the patientId in the url
        # so that bookmarks, etc. would work across sessions
        if ($patientId == null){
            $patientId = $this->Session->read('patientBeingViewedByOther');
            if ($patientId == null){
                $this->Session->setFlash(
                    "Please select a patient to view.");
                $this->redirect("/results/others");
            }
        }

        $this->patientIsAuthdUser = false;
        $hasPermissionToView = false;

        // if this user is staff, or is an associate of the patient
        // give them access to the patient's data
        // Note that not all staff will be granted auth access to this action
        if ($this->DhairAuth->checkWhetherUserBelongsToAro(
                            $this->Auth->user('id'), "aroStaff"))
        {
            $hasPermissionToView = true;
        }
        else {
            $this->patientAssociate = 
                    $this->PatientAssociate->forPatientAndAssociate(
                        $patientId, $this->Auth->user('id'));
            if (!empty($this->patientAssociate)){
                $hasPermissionToView = true;
            }
        }
        if ($hasPermissionToView == true)
        { 
            // use User rather than Patient so we get the Clinic record
            $this->patient = $this->User->findById($patientId);
            $this->set('patient', $this->patient);

            $this->Session->write('patientBeingViewedByOther', 
                $patientId);
        }
        else {
           $this->Session->setFlash(
             "You are not allowed to access that patient's information.");
           $this->redirect("/users");
        }
        $this->sharedSubscales = 
            $this->PatientAssociateSubscale->getListOfSharedSubscales(
                $this->patientAssociate['PatientAssociate']['id']);
        $this->TabFilter->selected_tab("View Reports");
    } // function _initPatientForReadByAssoc($patientId = null){


    /**
    *   Populate array of scales and subscales for this patient
    *   (If auth'd user is an associate, only include scales and
    *   subscales which the patient has shared w/ the associate)
    *   @param $limitToOrderGreaterThan0 only init those that are displayed
    *           in patient-viewable pages  
    
    */
    function _initScalesAndSubscales(
                    $trimForAssoc = true, 
                    $limitToOrderGreaterThan0 = true){

        $this->scales = 
                $this->Scale->sAndSubscalesForProject(
                                2,//FIXME Configure::read('PROJECT_ID'),
                                $limitToOrderGreaterThan0, true);

        /**$this->log("scales B4 sharing adjustment: " . 
                    print_r($this->scales, true), LOG_DEBUG); */

        if (($trimForAssoc === true) && !empty($this->patientAssociate)){
            // reduce $this->scales down to those defined in 
            //      PatientAssociateSubscale
            foreach($this->scales as $scaleKey => $scale){
                $hasAccessToSomeSubscale = false;
                foreach ($scale['Subscale'] as $key => $subscale){
                    if (!in_array($subscale['id'], $this->sharedSubscales)){
                    //if (in_array($subscale['id'], $sharedSubscales) == false){
                        /**$this->log("subscale[id]: " . $subscale['id'] .
                                    " is not in array." ,
                                    LOG_DEBUG); */
                        unset($this->scales[$scaleKey]['Subscale'][$key]);
                    }
                    else {
                        /**$this->log("subscale[id]: " . $subscale['id'] .
                                    " is in array." ,
                                    LOG_DEBUG); */
                        $hasAccessToSomeSubscale = true;
                    }
                }
                if (!$hasAccessToSomeSubscale) {
                    /**$this->log("Zero subscales in scale[" . $scaleKey . 
                                "]: are shared", 
                                LOG_DEBUG); */
                    unset($this->scales[$scaleKey]);
                }
            }
        }
        $this->set("scales", $this->scales);
        //$this->DhairLogging->logArrayContents($this->scales, "scales after assignment");
    } // function _initScalesAndSubscales(


    /**
    *   Get data for completed sessions.
    *   @param $sessionIdList An optional param of sessions. If this param is passed, returned data will be sorted by SurveySession.id DESC
    */
    function _initScaleAndSubscaleSessionData($sessionIdList = null){
        //$this->DhairLogging->logArrayContents($sessionIdList, "sessionIdList");
        //for($i = 0; $i < count($this->scales); $i++) {
        //foreach($this->scales as $key => $value) {
        foreach (array_keys($this->scales) as $key) {
            $scale =& $this->scales[$key];
            
            if (isset($scale)){

                if ($scale["Scale"]["name"] == "Activity Diary Entries"){
                    $scale = $this->json_scale_data_diary($scale);
                }
                else{
                  foreach (array_keys($scale['Subscale']) as $j) {
                    $subscale =& $scale["Subscale"][$j];
                    //$this->DhairLogging->logArrayContents($subscale, "subscale B4 json");
                      $subscale["data"] = 
                        $this->json_subscale_data($subscale["id"], $sessionIdList);
                    //$this->DhairLogging->logArrayContents($subscale, "subscale after json");
                  }
                }
            }
        }
        //$this->DhairLogging->logArrayContents($this->scales, "scales incl. json");
        $this->set("scales", $this->scales);
    } // function _initScaleAndSubscaleSessionData($sessionIdList = null){

    /**
    *   Get array containing chart data for the scale which contains this subscale
    *  eg:
        array(
            0 => array('id' => 37, 'name' => 'Nausea and Vomiting', 'data' => '[[1292328104000,2],[1292585218000,3],[1293054702000,],[1295458558000,1]]'),
            1 => array('id' => 38, 'name' => 'Pain ', 'data' => '[[1292328104000,2],[1292585218000,3],[1293054702000,],[1295458558000,1]]'),
            2 => array('id' => 39, 'name' => 'Breathing', 'data' => '[[1292328104000,2],[1292585218000,3],[1293054702000,],[1295458558000,1]]'),
        );
    *
    *   If the user is an associate, selectively populate chart data depending on whch subscales user has access to.
    *   This is used by the 2 show* actions; for most other purposes, use json_subscale_data instead. 
    */
  function _initSubscaleSessionDataForChart($selected_subscale_id, 
                    $subscaleMin = null, $subscaleMax = null,
                    $translatedMin = null, $translatedMax = null)
  {
    $data = $this->SessionSubscale->data($selected_subscale_id, $this->authd_user_id);
    // transform to json
        
    $subscalesData = array();

        // need to find the right scale first
	$subscale = $this->Subscale->findById($selected_subscale_id);
	$scale = $this->Scale->findById($subscale["Subscale"]["scale_id"]);

	foreach($scale["Subscale"] as $subscale) {
        if ($subscale['order'] <= 0) continue;

        $subscaleArray = array('id' => $subscale["id"],
                                'name' => $subscale["name"]);

        $data_row = array();
	    $subscale_id = $subscale["id"];
        if (!empty($this->patientAssociate)){
            if (!in_array($subscale_id, $this->sharedSubscales)){
                continue;
            }
        }
	    if($selected_subscale_id == $subscale_id) {
            $this->set('subscaleName', $subscale["name"]);
	    }
        $sessionSubscales = 
                $this->SessionSubscale->reportablesForSubscaleAndPatient(
                                            $subscale_id, 
                                            $this->patient['Patient']['id']);
        $this->set('sessionSubscales', $sessionSubscales);
        foreach($sessionSubscales as $sessionSubscale) {
            $dt = $this->_getDtAssignIfEmpty(
                    $sessionSubscale['SessionSubscale']['survey_session_id']);
	            
            $val = $sessionSubscale["SessionSubscale"]["value"];

            if (is_null($val) || $val == ''){
                continue;
            }
 
            if (!is_null($val) &&
                !is_null($subscaleMin) &&
                !is_null($subscaleMax) &&
                !is_null($translatedMin) &&
                !is_null($translatedMax) )
            {
                $val = ($val / ($subscaleMax - $subscaleMin)) * 
                    ($translatedMax - $translatedMin);
            }
            $val = round($val); // FIXME might not want this if small range
            array_push(
                $data_row, 
                "[" . 
                strtotime($dt) . 
                "000," .
	            $val . 
                "]");
	    }
	    sort($data_row);
	    $js_string = implode(",", $data_row);
        $subscaleArray['data'] = $js_string;

        $subscalesData[] = $subscaleArray;
    }

    return $subscalesData;
  } // function _initSubscaleSessionDataForChart(...)


    /**
    *   Ajax method
    *   Really just used for writing entries to the logs table
    */
    function customize_chart($subscaleId){

        //$this->log("hit customize_chart($subscaleId)", LOG_DEBUG);

        $this->render('customize_chart', 'ajax');
    }


    /**
    *   Ajax method
    *   Really just used for writing entries to the logs table
    */
    function log_click_to_external_resource($externalUrl) {
        //$this->render('log_click_to_external_resource', 'ajax');
        $this->autoRender = false;
        $this->layout = 'ajax';
        return;
    }

    /**
    *   Ajax method
    *   Really just used for writing entries to the logs table
    */
    function log_teaching_tip_expansion($tipQuestionStem) {

        //$this->render('log_teaching_tip_expansion', 'ajax');
        $this->autoRender = false;
        $this->layout = 'ajax';
        return;
    }


    /**
    *   @param $sessionIdList An optional param of sessions. If this param is used, returned data will be sorted by SurveySession.id DESC
    *   
    */
    private function json_subscale_data($subscale_id, $sessionIdList = null) {

        //$this->log("patientId:" . $this->patientId, LOG_DEBUG);
        //$this->DhairLogging->logArrayContents($sessionIdList, "sessionIdList");
        $data_row = array();
        $sessionSubscales = 
            $this->SessionSubscale->reportablesForSubscaleAndPatient(
                $subscale_id,
                $this->patient['Patient']['id']
                );
        //$this->DhairLogging->logArrayContents(
        //                        $sessionSubscales, "sessionSubscales");
        
        foreach($sessionSubscales as $sessionSubscale) {
            $strDateTime = $this->_getDtAssignIfEmpty(
                    $sessionSubscale['SessionSubscale']['survey_session_id']);
            array_push($data_row, 
                // make it micro seconds
                "[" . strtotime($strDateTime) . "000," .
                //"[" . $unixTimeStamp . "000," .
                $sessionSubscale["SessionSubscale"]["value"] . "]");
        }
        // each elem in $data_row like: [1232143855000,4]
        //$this->DhairLogging->logArrayContents(
        //                        $data_row, "data_row");
        sort($data_row);
        // data elems now sorted by session modified date ASC 
        // earlier sessions' data will be listed first, as
        // sessions are not modifiable after a subsequent one has been
        // created.
        $json = "[" . implode(",", $data_row) . "]";
        //$this->log("json for subscale " . $subscale_id . " = " . $json . "; "
        //                    . Debugger::trace(), LOG_DEBUG);
        // like [[1231879853000,10]] or [[1231879853000,10],[1232143855000,]]
        return $json;
    }

    /**
     *
     */
    private function json_scale_data_diary($scale) {

        $entries = $this->ActivityDiaryEntry->getData(
                          $this->authd_user_id, date('Y-m-d', strtotime("-13 days")));

        foreach($scale["Subscale"] as $index => $field){
            $afterFirstEntry = false;
            $fieldName = $field["id"];
            $scale['Subscale'][$index]["data"] = "["; 

            foreach($entries as $entry){
                if ($afterFirstEntry){
                    $scale['Subscale'][$index]["data"] .= ",";
                }
                $scale['Subscale'][$index]["data"] .= "[" . 
                    // FIXME convert to local time? 
                    strtotime($entry["ActivityDiaryEntry"]["date"]) .
                    "000," . $entry["ActivityDiaryEntry"][$fieldName] .
                    "]";
                $afterFirstEntry = true;
            }
            $scale['Subscale'][$index]["data"] .= "]";

            //$this->log("ActivityDiaryEntry scale: " . print_r($scale, true), LOG_DEBUG);

        }

        //$this->log("ActivityDiaryEntry scale : " . print_r($scale, true),  LOG_DEBUG);
        return $scale;
    }

    private function _getDtAssignIfEmpty($sessionId){
        if (array_key_exists($sessionId, 
                                $this->sessionLastAnswerDTs)){
            $dt = $this->sessionLastAnswerDTs[$sessionId];
        }
        else {
            $this->sessionLastAnswerDTs[$sessionId] = 
                $this->SurveySession->lastAnswerDT($sessionId);
            $dt = $this->sessionLastAnswerDTs[$sessionId]; 
        }
        return $dt;
    }

}
?>
