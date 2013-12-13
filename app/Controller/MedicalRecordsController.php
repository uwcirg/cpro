<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class MedicalRecordsController extends AppController {

    var $uses = array("SurveySession", 
        "Question",
        "User", 
        "Patient", 
        "Project", 
        "Scale", 
        "Subscale", 
        "Item", 
        "SessionScale",
        "SessionItem",
        "Answer",
        "Option",
        "SessionSubscale",
        "Clinic"
        );

    //var $components = array("InfluenceReader"); // FIXME add conditionally

    var $helpers = array("Html");
    var $scales;

    const DATE_FORMAT = 'm/d/Y';

    /**
    * REPORT WILL NOT CONVERT TO PDF PROPERLY IF CAKE'S DEBUG LEVEL IS >= 2
    * Data is only reported for sessions which have been at least
    *   partially finalized
    * Note that this action redirects to itself 
    *   in order to avoid displaying patientId's in the (printed) url
    * @param $apptId report on session for $apptId and the session for the appt preceeding it (if null, the two latest "finished" T sessions are reported on) 
    */
    function clinic_report_pdf($patientId = null, $projectId, $apptId = null,
                                $apptIdPrev){

        //Configure::write('debug', 0); // can't get this to work!
//        $this->log("clinic_report_pdf($patientId, $apptId, $apptIdPrev); " /**. Debugger::trace()*/, LOG_DEBUG);
        if ($patientId != null){
            // first pass through this action, redirect to self w/out params
            $sessions = 
              $this->SurveySession->getReportableSessionsForApptAndApptPrevious(
                    $patientId, $projectId, 
                    $apptId, $apptIdPrev);
            $this->Session->write('sessionArrayForPrintout', $sessions);
            $this->redirect("/medical_records/clinic_report_pdf");
        }
        $this->layout = 'html_to_pdf';

        $reportHtml = 
            $this->requestAction("medical_records/clinic_report", 
                                                    array('return'));
        $sessions = $this->Session->read('sessionArrayForPrintout');
        // artifact of session read - need to look at first element
        $sessions = $sessions[0];
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), just read sessionArrayForPrintout:" . print_r($sessions, true), LOG_DEBUG);
        $surveySessionId = 
            $sessions[count($sessions) - 1]['SurveySession']['id'];

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), just set surveySessionId:$surveySessionId", LOG_DEBUG);

        $this->set('filename_prefix', 'clinic-report-');
        $this->set('pdfId', $surveySessionId);
        $this->set('reportHtml', $reportHtml);
        /**$post_fields = array('filename_prefix' => 'clinic-report-', 
                                'pdfId' => $surveySessionId);
                                'reportHtml' => $reportHtml);*/
        //$this->DhairLogging->logArrayContents($post_fields, "post_fields");
        /**$this->set('post_fields', $post_fields);*/

        $this->response->type('pdf');
    }

    /**
    * Data is only reported for sessions which have been at least
    *   partially finalized
    * Note that this action redirects to itself 
    *   in order to avoid displaying patientId's in the (printed) url
    * @param $apptId report on session for $apptId and the session for the appt preceeding it (if null, the two latest "finished" T sessions are reported on) 
    */
    function clinic_report($patientId = null, $projectId, $apptId = null, 
                            $apptIdPrev = null){
//        $this->log("clinic_report($patientId, $apptId, $apptIdPrev); " /**. Debugger::trace()*/, LOG_DEBUG);

        //Configure::write('debug', 0);

        if ($patientId != null){
            if ($patientId != ''){
//            $this->log("clinic_report, patientId not null, = $patientId", LOG_DEBUG);
            $this->Session->write('patientBeingViewedByOther', $patientId);
            $sessions = 
              $this->SurveySession->getReportableSessionsForApptAndApptPrevious(
                    $patientId, $projectId, 
                    $apptId, $apptIdPrev);
            $this->Session->write('sessionArrayForPrintout', $sessions);
            $this->redirect("/medical_records/clinic_report");
            }
        }

        $sessions = $this->Session->read('sessionArrayForPrintout');
        // artifact of session read - need to look at first element
        $sessions = $sessions[0];
    
        //$this->log('clinic_report, just read sessions = ' . print_r($sessions, true), LOG_DEBUG);
    
        $patientId = $sessions[0]['SurveySession']['patient_id'];

        $this->layout = 'clinic_report';

        # TODO: it would be nice if we just kept the patientId in the url
        # so that bookmarks, etc. would work across sessions
        if ($patientId == null){
            $patientId = $this->Session->read('patientBeingViewedByOther');
        }
	    $this->patient = $this->Patient->findById($patientId);
        //$this->DhairLogging->logArrayContents($this->patient, "patient");

        $this->set('patient', $this->patient);

        $this->Session->write('patientBeingViewedByOther', $patientId);

        $this->scales =
                $this->Scale->sAndSubscalesForProject(
                                $projectId, false);
        $this->set("scales", $this->scales);
        //$this->log("just set scales : " . print_r($this->scales, true), LOG_DEBUG);

        
        // holds scales and subscales indexed by ID's
        $sAndSubsIndexByIdWScores =  array();
    
        // unlike the proper subscale data, these will be
        // organized primarily by session
        for($i = 0; $i < count($sessions); $i++) {
           
            $sessionId = $sessions[$i]['SurveySession']['id']; 

            //The following shouldnt do any TZ conversion
            $sessions[$i]['SurveySession']['lastAnswerDT'] = 
                date(self::DATE_FORMAT,
                      strtotime(
                            $this->SurveySession->lastAnswerDT($sessionId)
                        ));
            //only report these if session is "stage 2" finalized
            if ($sessions[$i]['SurveySession']['finished'] == 1){
                $sessions[$i]['SurveySession']['priority_subscales'] = array();
                $priority_options =
                    $this->Answer->forSessionAndQuestion(
                        $sessions[$i]['SurveySession']['id'], 
                        RANKING_Q);

                $sessions[$i]['SurveySession']['open_text'] = 
                    $this->Answer->analysisValueForSessionAndQuestion(
                        $sessions[$i]['SurveySession']['id'], OPEN_TEXT_Q); 
            }
            $apptIdToReport = $sessions[$i]['SurveySession']['appointment_id']; 
        }// for($i = 0; $i < count($sessions); $i++) {
        //$this->log('sessions to report, in controller just before set-ting for view : ' . print_r($sessions, true), LOG_DEBUG); 
        $this->set('sessions', $sessions);
       
        // the data will have been sorted by session mod date ASC;
        // the first session's data will be listed first, as
        // sessions are not modifiable after a subsequent one has been
        // created.
        for($i = 0; $i < count($this->scales); $i++) {
            $scale =& $this->scales[$i];
            
            if (isset($scale)){
              //$this->log("scale : " . print_r($scale, true), LOG_DEBUG);
              $scaleId = $scale['Scale']['id'];
              //$this->log("scale is $scaleId is set", LOG_DEBUG);
              $sAndSubsIndexByIdWScores[$scaleId] = array();

              foreach ($scale['Subscale'] as $subscale) {
                //$this->log("scale $scaleId has subscale " . $subscale['id'], LOG_DEBUG);
                $sAndSubsIndexByIdWScores[$scaleId][$subscale['id']] = array();
                foreach ($sessions as $session){
                    $sessionSubscales = 
                      $this->SessionSubscale->reportablesForSubscaleAndPatient(
                            $subscale['id'],
                            $this->patient['Patient']['id'], 
                            array(0=>$session['SurveySession']['id']));
                    //$this->log("sessionSubscales: " . print_r($sessionSubscales, true), LOG_DEBUG);
                    $apptIdToReport = $session['SurveySession']['appointment_id']; 
                    $subWScore =& 
                        $sAndSubsIndexByIdWScores[$scaleId][$subscale['id']];
                    if ((sizeof($sessionSubscales) > 0) &&
                        (!is_null(
                            $sessionSubscales[0]['SessionSubscale']['value']))){

                        // note: data_to_report is a percentage
                            $subWScore["data_to_report_" . $apptIdToReport] =
                            //combined = (value - base)/range
                            ($sessionSubscales[0]['SessionSubscale']['value']
                                - $subscale['base']) / $subscale['range'];

                        if ($sessionSubscales[0]['SessionSubscale']['value']
                                >= $subscale['critical']){
                            $subWScore["critical_" . $apptIdToReport]  = true; 
                        }
                        else {
                            $subWScore["critical_" . $apptIdToReport] = false; 
                        }
                    }
                    else {
                        $subWScore["data_to_report_" . $apptIdToReport] = null;
                        if ($subscale['id'] == PHQ9_SUBSCALE){
                        //PHQ9 if answered <= 6 PHQ9 qs, will end up here
                        // (subscale.combination is mean_or_third_null)
                        //if selected option index for q43 or q44 is > 2
                        //  PHQ9 considered incomplete, but high on key qs
                            $phq9_incompl_but_high = $this->SessionItem->find(
                              "count",
                                array(
                                "conditions" => array(
                                    'SessionItem.survey_session_id' =>
                                        $session['SurveySession']['id'],
                                    'SessionItem.item_id' => 
                                        array(PHQ9_LITTLE_INTEREST_ITEM, 
                                                PHQ9_FEELING_DOWN_ITEM),
                                    // SessionItem.value always a 1-based index
                                    'SessionItem.value >' => 2)));
                            if ($phq9_incompl_but_high > 0){
                                $subWScore["incomplete_but_high_" . $apptIdToReport] = true;
                            }
                        }
                        elseif($subscale['id'] == PROMIS_FATIGUE_SUBSCALE){
                        //PROMIS if skipped any q's, will end up here
                        // (subscale.combination is sum_or_any_null)
                        //if selected option index for any of these q's is > 3 
                        //  PROMIS considered incomplete, but high on some q(s)
                            $promis_incompl_but_high = $this->SessionItem->find(
                              "count",
                                array(
                                "conditions" => array(
                                    'SessionItem.survey_session_id' =>
                                        $session['SurveySession']['id'],
                                    'SessionItem.item_id' => 
                                        array(PROMIS_FEEL_FATIGUED_ITEM,
                                          PROMIS_HOW_FATIGUED_ITEM,
                                          PROMIS_FATIGUE_RUN_DOWN_ITEM,
                                          PROMIS_FATIGUE_TROUBLE_STARTING_ITEM),
                                    // SessionItem.value always a 1-based index
                                    'SessionItem.value >' => 3)));
                            if ($promis_incompl_but_high > 0){
                                $subWScore["incomplete_but_high_" . $apptIdToReport] = true;
                            }
                        }
                    }
                    if ($subscale['id'] == PHQ9_SUBSCALE){
                        $phq9_alert = $this->SessionItem->find(
                            "count",
                                array(
                                "conditions" => array(
                                    'SessionItem.survey_session_id' =>
                                        $session['SurveySession']['id'],
                                    'SessionItem.item_id' => 
                                        PHQ9_ALERT_ITEM,
                                    // SessionItem.value always a 1-based index
                                    'SessionItem.value >' => 1))); 
                        if ($phq9_alert > 0){
                            $subWScore["red_alert_" . $apptIdToReport] = true; 
                        }
                    }
                }// foreach ($sessions as $session){
              }// foreach ($scale['Subscale'] as $subscale) {
            }// if (isset($scale)){
        }// for($i = 0; $i < count($this->scales); $i++) {

        //$this->log("sAndSubsIndexByIdWScores " .  print_r($sAndSubsIndexByIdWScores, true), LOG_DEBUG);
        $this->set("sAndSubsIndexByIdWScores", $sAndSubsIndexByIdWScores);
        
        $this->render(CProUtils::getInstanceSpecificViewName(
            $this->name,
            $this->request->action
        ));
    } // function clinic_report($patientId = null, $apptId = null){

    /**
    * Data is only reported for sessions which have been at least
    *   partially finalized
    * Note that this action redirects to itself 
    *   in order to avoid displaying patientId's in the (printed) url
    */
    function clinic_report_sarcoma($patientId = null, $projectId){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patientId); " /**. Debugger::trace()*/, LOG_DEBUG);

        //Configure::write('debug', 0);

        if ($patientId != null){
            if ($patientId != ''){
//            $this->log("clinic_report, patientId not null, = $patientId", LOG_DEBUG);
            $this->Session->write('patientBeingViewedByOther', $patientId);
            $sessions = 
              $this->SurveySession->getReportableSessions(
                    $patientId, $projectId);
            $this->Session->write('sessionArrayForPrintout', $sessions);
            $this->redirect("/medical_records/clinic_report_sarcoma");
            }
        }

        $sessions = $this->Session->read('sessionArrayForPrintout');
        // artifact of session read - need to look at first element
        $sessions = $sessions[0];
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), sessions: " . print_r($sessions, true), LOG_DEBUG);
    
        $patientId = $sessions[0]['SurveySession']['patient_id'];

        $this->layout = 'clinic_report';

        # TODO: it would be nice if we just kept the patientId in the url
        # so that bookmarks, etc. would work across sessions
        if ($patientId == null){
            $patientId = $this->Session->read('patientBeingViewedByOther');
        }
	    $this->patient = $this->Patient->findById($patientId);

        $this->set('patient', $this->patient);

        $this->Session->write('patientBeingViewedByOther', $patientId);

        // holds scales and subscales indexed by ID's
        $sAndSubsIndexByIdWScores =  array();
    
        // unlike the proper subscale data, these will be
        // organized primarily by session
        for($i = 0; $i < count($sessions); $i++) {
           
            $sessionId = $sessions[$i]['SurveySession']['id']; 

            //only report these if session is "stage 2" finalized
            if ($sessions[$i]['SurveySession']['finished'] == 1){
                $sessions[$i]['SurveySession']['priority_subscales'] = array();
                $priority_options =
                    $this->Answer->forSessionAndQuestion(
                        $sessions[$i]['SurveySession']['id'], 
                        RANKING_Q);
                foreach($priority_options as $option_id => $comboTxtIfAny) {
                    $option = $this->Option->findById($option_id);
                    $sessions[$i]['SurveySession']['priority_subscales'][] =
                        $option["Option"]["BodyText"];
                }
                $sessions[$i]['SurveySession']['open_text'] = 
                    $this->Answer->analysisValueForSessionAndQuestion(
                        $sessions[$i]['SurveySession']['id'], OPEN_TEXT_Q); 

                $option_id = $this->Answer->forSessionAndQuestion(
                        $sessions[$i]['SurveySession']['id'], RELIGION_IMPORTANT_Q); 
                $option = $this->Option->findById($option_id);
                $sessions[$i]['SurveySession']['religion_important'] = 
                        $option["Option"]["BodyText"];

                $option_id = $this->Answer->forSessionAndQuestion(
                        $sessions[$i]['SurveySession']['id'], CHAPLAIN_VISIT_Q); 
                $option = $this->Option->findById($option_id);
                $sessions[$i]['SurveySession']['chaplain_visit'] = 
                        $option["Option"]["BodyText"];

            }
            $apptIdToReport = $sessions[$i]['SurveySession']['appointment_id']; 
        }
        //$this->log('sessions to report, in controller just before set-ting for view : ' . print_r($sessions, true), LOG_DEBUG); 
        $this->set('sessions', $sessions);
 
        $subscales = $this->Subscale->find('all', array(
            'recursive' => -1
        ));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), found subscales: " . print_r($subscales, true), LOG_DEBUG);

        foreach ($subscales as $key => $subscale) {
            $subscales[$key]['SessionSubscale'] = array();

            foreach ($sessions as $session){
                $sessionSubscale = $this->SessionSubscale->find('first', array(
                    'conditions' => array('SessionSubscale.survey_session_id' 
                                            => $session['SurveySession']['id'],
                                        'SessionSubscale.subscale_id' 
                                            => $subscale['Subscale']['id']),
                    'order' => 'SessionSubscale.id DESC',
                    'recursive' => -1 
                ));
                if (isset($sessionSubscale['SessionSubscale']))
                    $subscales[$key]['SessionSubscale'][] = $sessionSubscale['SessionSubscale'];

            }// foreach ($sessions as $session){
        }// foreach ($subscale){

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), subscales: " . print_r($subscales, true), LOG_DEBUG);
        $this->set('subscales', $subscales);
    }// function clinic_report_sarcoma
    
    
    /**
    * Data is only reported for sessions which have been at least
    *   partially finalized
    * Note that this action redirects to itself 
    *   in order to avoid displaying patientId's in the (printed) url
    */
    function clinic_report_p3p($patientId = null, 
                                $projectId = P3P_BASELINE_PROJECT){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patientId); " /**. Debugger::trace()*/, LOG_ERROR);

        //Configure::write('debug', 0);

        if ($patientId != null){
            if ($patientId != ''){
//            $this->log("clinic_report, patientId not null, = $patientId", LOG_DEBUG);
            $this->Session->write('patientBeingViewedByOther', $patientId);
            $sessions = 
              $this->SurveySession->getReportableSessions(
                    $patientId, $projectId);

            $this->Session->write('sessionArrayForPrintout', $sessions);
            $this->redirect("/medical_records/clinic_report_p3p");
            }
        }

        $sessions = $this->Session->read('sessionArrayForPrintout');
        // artifact of SurveySession->getReportableSessions - need to look at first element
        $session = $sessions[0][0];
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), session: " . print_r($session, true), LOG_DEBUG);
 
        $patientId = $session['SurveySession']['patient_id'];
        $sessionId = $session['SurveySession']['id'];

        $this->layout = 'clinic_report';

        # TODO: it would be nice if we just kept the patientId in the url
        # so that bookmarks, etc. would work across sessions
        if ($patientId == null){
            $patientId = $this->Session->read('patientBeingViewedByOther');
        }
	    $this->patient = $this->Patient->findById($patientId);

        $this->set('patient', $this->patient);

        $this->Session->write('patientBeingViewedByOther', $patientId);
        
        $optionStatus = $this->Answer->bodyTextForAnswerToQuestion($sessionId, 2117);
        $this->set('optionStatus', $optionStatus);
       
        $whichOption = ' ';//space but not empty, so only ws is output if question not asked.
        $optionStatusNumerical 
            = $this->Answer->analysisValueForSessionAndQuestion($sessionId, 2117);
        if ($optionStatusNumerical > 3){
            $whichOption 
                = $this->Answer->bodyTextForAnswerToQuestion($sessionId, 1533);
            if (isset($whichOption)){
                // Remove parentheticals
                $whichOption = preg_replace("/\([^)]+\)/","",$whichOption);
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), optionStatus = >$optionStatus<, optionStatusNumerical = >$optionStatusNumerical<, setting whichOption to >$whichOption< " , LOG_DEBUG);
        $this->set('whichOption', $whichOption );

        $this->set('decisionalControlPref', 
            $this->Answer->bodyTextForAnswerToQuestion($sessionId, 1739));

        $epic = '(incomplete)';
        $sessionScaleEpic = $this->SessionScale->find('first', array(
                    'conditions' => array('SessionScale.survey_session_id' 
                                            => $sessionId,
                                        'SessionScale.scale_id' 
                                            => EPIC_SCALE),
                    'order' => 'SessionScale.id DESC',
                    'recursive' => -1 
            ));
        if (isset($sessionScaleEpic['SessionScale']))
                    $epic = $sessionScaleEpic['SessionScale']['value'];
        $this->set('epic', $epic);

        $this->set('overallUrinary', 
            $this->Answer->bodyTextForAnswerToQuestion($sessionId, 1874));

        // holds scales and subscales indexed by ID's
        $sAndSubsIndexByIdWScores =  array();
   
//        $this->log('session to report, in controller just before set-ting for view : ' . print_r($session, true), LOG_DEBUG); 
        $this->set('session', $session);

        $subscaleIds = array(URINARY_INCONTINENCE_EPIC_SUBSCALE, URINARY_IRRITATION_EPIC_SUBSCALE, BOWEL_EPIC_SUBSCALE, SEXUALITY_EPIC_SUBSCALE, VITALITY_EPIC_SUBSCALE);
 
        $subscales = $this->Subscale->find('all', array(
            'conditions' => array('Subscale.id' => $subscaleIds),
            'recursive' => -1
        ));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), found subscales: " . print_r($subscales, true), LOG_DEBUG);

        $subscales = Hash::combine($subscales, '{n}.Subscale.id', '{n}');
        //$subscales = Set::combine($subscales, '{n}.Subscale.id', '{n}.Subscale');
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), subscales after Set::combine: " . print_r($subscales, true), LOG_DEBUG);

        foreach ($subscales as $key => $subscale) {
            $subscales[$key]['SessionSubscale'] = array();
            $sessionSubscale = $this->SessionSubscale->find('first', array(
                    'conditions' => array('SessionSubscale.survey_session_id' 
                                            => $sessionId,
                                        'SessionSubscale.subscale_id' 
                                            => $subscale['Subscale']['id']),
                    'order' => 'SessionSubscale.id DESC',
                    'recursive' => -1 
            ));
            if (isset($sessionSubscale['SessionSubscale']))
                    $subscales[$key]['SessionSubscale'] = $sessionSubscale['SessionSubscale'];

            //$subscales[$key]['Item'] = array();
            $items = $this->Item->find('all', array(
                    'conditions' => array('Item.subscale_id' 
                                            => $subscale['Subscale']['id']),
                    //'order' => '.id DESC',
                    'recursive' => -1 
                    //'recursive' => 1 
            ));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), items for subscale " . $subscale['Subscale']['id'] . ": " . print_r($items, true), LOG_DEBUG);

            $items = Hash::combine($items, '{n}.Item.id', '{n}');  
 
            foreach($items as $itemId => $item){

                $sessionItem = $this->SessionItem->find('first', array(
                    'conditions' => array('SessionItem.survey_session_id' 
                                            => $sessionId,
                                        'SessionItem.item_id' 
                                            => $itemId),
                    'order' => 'SessionItem.id DESC',
                    'recursive' => -1 
                ));
                if (array_key_exists('SessionItem', $sessionItem))
                    $items[$itemId]['SessionItem'] 
                        = $sessionItem['SessionItem'];
                else $items[$itemId]['SessionItem'] = null;

                $answer = $this->Answer->bodyTextForAnswerToQuestion(
                                            $sessionId, $item['Item']['question_id']);
                $items[$itemId]['Answer'] = $answer;
            }

            $subscales[$key]['Item'] = $items;
            //    = Hash::combine($items, '{n}.Item.id', '{n}');   
 
        }// foreach ($subscale){

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), subscales: " . print_r($subscales, true), LOG_DEBUG);
        $this->set('subscales', $subscales);

        $factorsSessionSubscales =
            $this->SessionSubscale->reportablesForScalesAndPatient(
                array(INFLUENTIAL_FACTORS_PERSONAL_PROFILE_SCALE/**,
                        INFLUENTIAL_FACTORS_SYMPTOMS_SCALE*/),
                $patientId, array($sessionId), true, true);

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), factorsSessionSubscales: " . print_r($factorsSessionSubscales, true), LOG_DEBUG);
        $factors_SomeInfluence = array();
        $factors_ALotOfInfluence = array();
        foreach($factorsSessionSubscales as $factorsSessionSubscale){
            $critical = $factorsSessionSubscale['Subscale']['critical'];
            foreach($factorsSessionSubscale['SessionItems'] as $item){
                $val = $item['SessionItem']['value'];
                if ($val == 3) $factors_SomeInfluence[] = $item['Item']['name']; 
                elseif ($val == 4) $factors_ALotOfInfluence[] = $item['Item']['name']; 
            }
        }
        $this->set('factors_SomeInfluence', $factors_SomeInfluence);
        $this->set('factors_ALotOfInfluence', $factors_ALotOfInfluence);

        $this->set('numTimesTriedToHaveSex', 
            $this->Answer->bodyTextForAnswerToQuestion($sessionId, 2087));

        $this->set('medsToHelpErection', 
            $this->Answer->bodyTextForAnswerToQuestion($sessionId, 2088));
        
        $this->set('patientId', $patientId);

        $pdfFormat = false;
        if (array_key_exists('pdfFormat', $this->params['named']))
            $pdfFormat = $this->params['named']['pdfFormat'];

        $this->set('pdfFormat', $pdfFormat);
        
        // Added for contact info on bottom of report
        $clinics = $this->Clinic->find('all');
        $this->set('clinics', $clinics);

    }// function clinic_report_p3p

    /**
    * REPORT WILL NOT CONVERT TO PDF PROPERLY IF CAKE'S DEBUG LEVEL IS >= 2
    * Note that this action redirects to itself 
    *   in order to avoid displaying patientId's in the (printed) url
    */
    function clinic_report_p3p_pdf($patientId = null,
                                    $projectId = P3P_BASELINE_PROJECT){

        //Configure::write('debug', 0); // can't get this to work!
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patientId, $projectId); " /**. Debugger::trace()*/, LOG_DEBUG);
        if ($patientId != null){
            if ($patientId != ''){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), patientId not null, = $patientId", LOG_DEBUG);
                $this->Session->write('patientBeingViewedByOther', $patientId);
                $sessions = 
                    $this->SurveySession->getReportableSessions(
                        $patientId, $projectId);

                $this->Session->write('sessionArrayForPrintout', $sessions);
                $this->redirect("/medical_records/clinic_report_p3p_pdf");
            }
        }

        $this->layout = 'html_to_pdf';

        $reportHtml = 
            $this->requestAction(
                array('controller' => "medical_records",
                        'action' => "clinic_report_p3p"),
                array('named' => array('pdfFormat' => 'true'),
                'return'));

        $subdomain = $this->getSubdomain();
        if ($subdomain <> ""){
            $subdomain .= "/";
        }
        //$reportHtml .= '<!-- subdomain = ' . $subdomain . ' -->';

        // Replace relative paths w/ absolute paths on the filesystem
        $reportHtml = str_replace('href="/' . $subdomain,
                                    'href="' . WWW_ROOT,
                                    $reportHtml);
        $reportHtml = str_replace('src="/' . $subdomain,
                                    'src="' . WWW_ROOT,
                                    $reportHtml);

//        Configure::write('debug', 2); 
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), reportHtml:" . print_r($reportHtml, true), LOG_DEBUG);
//        Configure::write('debug', 0); // can't get this to work!

        $sessions = $this->Session->read('sessionArrayForPrintout');
        // artifact of session read - need to look at first element
        $sessions = $sessions[0];
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), just read sessionArrayForPrintout:" . print_r($sessions, true), LOG_DEBUG);
        $surveySessionId = 
            $sessions[count($sessions) - 1]['SurveySession']['id'];

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), just set surveySessionId:$surveySessionId", LOG_DEBUG);

        $this->set('filename_prefix', 'clinic-report-');
        $this->set('pdfId', $surveySessionId);
        $this->set('reportHtml', $reportHtml);
        /**$post_fields = array('filename_prefix' => 'clinic-report-', 
                                'pdfId' => $surveySessionId);
                                'reportHtml' => $reportHtml);*/
        //$this->DhairLogging->logArrayContents($post_fields, "post_fields");
        /**$this->set('post_fields', $post_fields);*/

        $this->response->type('pdf');
        $this->render('clinic_report_pdf');

    } //function clinic_report_p3p_pdf

}
?>
