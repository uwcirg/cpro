<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class SurveysController extends AppController
{
    /** Constant to indicate to the view that there are no results
        associated with the current survey page */
    const NO_ASSOCIATED_RESULTS = "None";

    var $uses = array("SurveySession",
                      "Appointment",
                      "Project",
                      "Alert",
                      "Questionnaire",
                      "SessionItem",
                      "SessionSubscale",
                      "SessionScale",
                      "Scale",
                      "Subscale",
                      "Item",
                      "Page",
                      "Question",
                      "Option",
                      "Answer",
                      "Condition",
                      "Site",
                      "Clinic",
                      "Patient",
                      "User");

    var $components = array(
        'Substitution',
        'Conditionality',
        'Instruments',
        'TraverseSkipped',
        'DhairDateTime'
    );
    var $helpers = array("InstanceSpecifics");

    /** Patient taking the survey.  May be the current user, but also may be
     *  another user if a staff member is taking the survey for a patient 
     */
    var $surveyPatientId;

    //$nextAppointment;

    function beforeRender() {
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('cpro.p3p.js'));
        parent::beforeRender();
    }

    /**
     *
     */
    function beforeFilter() {
        parent::beforeFilter();
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); heres the SurveySession class: " . get_class($this->SurveySession), LOG_DEBUG);

        if ($this->request->params['action'] != 'show'){
            $this->TabFilter->selected_tab(array('controller' => 'surveys',
                                                'action' => 'index'));
            $this->TabFilter->show_normal_tabs();
        }

        $this->layout = 'survey'; // don't show tabs in survey page
        /**
        foreach ($this->patient['Appointment'] as $appointment){
            if ($appointment['datetime'] < currentGmt()){
                $nextAppointment = $appointment;
            }
            else break;
        }
        */

        if($session_id = $this->Session->read('session_id')) {
            $this->session_id = $session_id;
            $this->session = $this->SurveySession->findById($this->session_id);
        } else {
            $this->session_id = false;
            $this->session = false;
        }

        if ($this->Session->check(self::TAKING_SURVEY_AS)) {
            $this->surveyPatientId =
                $this->Session->read(self::TAKING_SURVEY_AS);
        } else {
            $this->surveyPatientId = $this->authd_user_id;
        }
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); end w/ this->session: " . print_r($this->session, true), LOG_DEBUG);
    }// function beforeFilter() {

    /**
     *
     */
    function index($sessionStart=null)
    {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(sessionStart = $sessionStart)", LOG_DEBUG);
        if (empty($this->session_link)){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(sessionStart = $sessionStart), session_link empty so redirecting to /users", LOG_DEBUG);
            $this->redirect("/users"); 
        }
        $this->layout = 'default'; // still show tabs for the main listing
        // Check for patient-designated appointment
        if (
            defined('SESSION_PATTERN') && 
            SESSION_PATTERN == PATIENT_DESIGNATED_APPTS &&
            !($sessionStart === null)
        ){
            if ($sessionStart){
                $time = $this->DhairDateTime->usersCurrentTimeStr();
                $self_reported = array(
                    'patient_id'=> $this->patient['Patient']['id'],
                    'datetime'=> $time
                );
                $this->Appointment->save($self_reported);
            }
            $this->Session->write('appointmentReminder', false);
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(sessionStart $sessionStart), PATIENT_DESIGNATED_APPTS, redirecting to " . $this->session_link[1], LOG_DEBUG);
            $this->redirect($this->session_link[1]);
        }
            
        $this->set('treatment', 
            $this->patient["Patient"]["study_group"] == Patient::TREATMENT);
        $this->set('participant', 
            $this->patient["Patient"]["study_group"] != null);
    }// function index()

    /**
     *
     */
    private function redirect_if_recent_survey($patient_id) {
        $recent_session_id = $this->SurveySession->findRecent($patient_id);
        if($recent_session_id){
//            $this->log("redirect_if_recent_survey redirecting; recent_session_id" . $recent_session_id, LOG_DEBUG);
            $this->redirect("/surveys/restart/$recent_session_id");
        }
    } //redirect_if_recent_survey

    /**
     *
     */
    function new_session($project_id = null)
    {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(project id $project_id)", LOG_DEBUG);

        // Is there a session in the last minute? 
        $this->redirect_if_recent_survey($this->surveyPatientId);

        $type = null;
        $appointment_id = null;

        $patient = $this->Patient->find('first',
            array(
                'recursive' => -1,
                'conditions' => array('Patient.id' => $this->surveyPatientId)
            )
        );
        // $this->log('patient:'.print_r($patient, true), LOG_DEBUG);

        $patientProjectState = $this->Patient->projectsStates[$project_id];

        if($patientProjectState->apptForNewSession){
            $appointment_id = $patientProjectState->apptForNewSession['Appointment']['id'];
            $type = APPT;
        }
        else if ($patientProjectState->initableNonApptSessionType){
            $type = $patientProjectState->initableNonApptSessionType;
        }
        else {
            $this->Session->setFlash("It's not time for you to take a survey. Please return within " . MIN_SECONDS_BETWEEN_APPTS / 60 / 60 . " hours of your appointment to do so.");
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "(project id $project_id), not time for session, redirecting to /surveys", LOG_DEBUG);
            $this->redirect($this->getSurveyHome());
            //$this->redirect('/surveys/');
        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . '() type set to: '.print_r($type, true), LOG_DEBUG);

        $start_time = $this->DhairDateTime->usersCurrentTimeStr();

        /**$nextAppointment = 
            $this->Appointment->getNextAppointment($this->surveyPatientId);*/

        $new_session = array( 
            "project_id"    => $project_id, 
            "user_id"       => $this->authd_user_id,
            "patient_id"    => $this->surveyPatientId,
            "appointment_id"=> $appointment_id,
            "type"          => $type,
            "modified"      => $start_time,
            "started"       => $start_time,
            "reportable_datetime" => $start_time,
        );

//        $this->log("new_session, here's new_session : " . print_r($new_session, true), LOG_DEBUG);

        $this->SurveySession->save($new_session);
        //$this->session_id = $this->SurveySession->getInsertID();
        $this->session_id = $this->SurveySession->id;

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(project id $project_id), redirecting to surveys/restart/" . $this->session_id, LOG_DEBUG);
        $this->redirect("/surveys/restart/" . $this->session_id);
    }// function new_session($project_id)

    /**
     *
     */
    function break_session() {
        $session_id = $this->Session->read("session_id");
//        $this->log(__CLASS__ . '->' . __FUNCTION__ . "(); session_id: $session_id", LOG_DEBUG);
        $surveySession = $this->SurveySession->findById($session_id);
        $projectId = $surveySession['SurveySession']['project_id'];
//        $this->log(__CLASS__ . '->' . __FUNCTION__ . "(); projectId: $projectId", LOG_DEBUG);
        $patientProjectState = $this->Patient->projectsStates[$projectId];

//        $this->log(__CLASS__ . '->' . __FUNCTION__ . "(); patientProjectState: " . print_r($patientProjectState, true), LOG_DEBUG);
//        $this->log(__CLASS__ . '->' . __FUNCTION__ . "(); apptForResumableSession: " . print_r($patientProjectState->apptForResumableSession, true), LOG_DEBUG);

        $this->Session->delete("session_id");
        //$this->Session->delete("patient" . $this->surveyPatientId);
        $this->session_id = false;
        $this->session = false;
        # FIXME: need rules on session completion times
        // if type APPT, indicate that
        // if type ELECTIVE, indicate today
        // else 
        $timeToComplete = 'one day'; 
        switch($patientProjectState->apptForResumableSession['SurveySession']['type']){
            case APPT:
                $timeToComplete = 'until your appointment';
                break;
            case ELECTIVE:
                $timeToComplete = 'until ' . RESUMABLE_UNTIL;
                break;
            default: //INTERVAL_BASED_SESSIONS
                if ($this->Patient->currentWindow){
                    $now = new DateTime("now", $this->Patient->currentWindow['stop']->getTimezone());
                    $diff = $this->Patient->currentWindow['stop']->diff($now);

                    if ($diff->format('%a') > 0)
                        $timeToComplete = $diff->format('%a') . ' day';
                    else
                        $timeToComplete = $diff->format('%h') . ' hour';

                    if ($diff->format('%a') > 1 or ($diff->format('%h') > 1 and $diff->format('%a') < 1))
                        $timeToComplete .= 's';

                    if ($diff->format('%a') > 0)
                        $timeToComplete .= ' (until ' . $this->Patient->currentWindow['stop']->format('m/d/y\)');

                }
                break;
        }
        if (isset($patientProjectState->project['Project']['taken_break_txt'])){
            if ($patientProjectState->project['Project']['taken_break_txt'] != 'false')
                $this->Session->setFlash(
                    __($patientProjectState->project['Project']['taken_break_txt']));    
        } else {
            $this->Session->setFlash(
                __("You've taken a break from the report. You have $timeToComplete to return to complete it."));    
        }
        

        if ($this->Session->check(self::TAKING_SURVEY_AS)) {
        // taking a survey as a patient, go back to the patient edit
            $this->Session->delete(self::TAKING_SURVEY_AS);
            $this->Session->delete("session_id");
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), redirecting to /patients/edit/" . $this->surveyPatientId, LOG_DEBUG); 
            $this->redirect('/patients/edit/' . $this->surveyPatientId);
        } else {
            $newLocation = 
                $this->getSurveyHome($patientProjectState->project['Project']);
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), redirecting to $newLocation", LOG_DEBUG); 
            $this->redirect($newLocation);
        }
    }// function break_session() {

    /**
      Get the page a particular question appears on
      @param question_id Id of the question
      @return The page the question appears on
     */
    private function pageId($question_id) {
        $question = $this->Question->findById($question_id);
        return($question["Page"]["id"]);
    }

    /**
      Restart a session, optionally at a particular question
      @param session_id Session to restart
      @param question_id Question to restart at.  If null, start at
           the last question answered
     */
    function restart($session_id, $question_id = null)
    {
        //$this->log("survey restart(session $session_id, question $question_id)", LOG_DEBUG); 

        $survey_session = $this->SurveySession->findById($session_id);

    	// allow special 'tester' users to restart finished sessions -- gsb
        if(($survey_session["SurveySession"]['finished'] == true && 
	        !($survey_session['Patient']['test_flag']))
            || $this->surveyPatientId != 
                    $survey_session["SurveySession"]["patient_id"]) 
        {
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), redirecting to getSurveyHome", LOG_DEBUG); 
            $this->redirect($this->getSurveyHome());
        }

        $this->Session->write("session_id", $session_id);
        $this->session_id = $session_id;
        $this->session = $this->SurveySession->findById($this->session_id);
        $this->session["SurveySession"]["modified"] = $this->DhairDateTime->usersCurrentTimeStr();
        $this->SurveySession->save($this->session);

        if($survey_session["SurveySession"]['partial_finalization']) {
            $this->after_partial_finalization();
        }

        // get the page id to restart at
        if (empty($question_id)) {
            //$this->log("survey restart(session $session_id, question $question_id); question empty so will look for most recent answer next", LOG_DEBUG); 
            // See if there are any answers for this session
            $answer = $this->Answer->most_recent_for_session($this->session_id);

            if ($answer) {
                $question_id = $answer["Answer"]["question_id"];
                $page_id = $this->pageId($question_id);
            } else {
                $page_id = $this->Conditionality->nextPageRecursive(
                                                    null, $this->session);
	        }
        } else {
	        $page_id = $this->pageId($question_id);
	    }
        
//        $this->log("survey restart(session $session_id, question $question_id); next is redirect /surveys/show/$page_id", LOG_DEBUG); 
        $this->redirect("/surveys/show/$page_id");
    } // function restart

    /**
     * show a survey page
     *
     * Notes on some tricky pages:
        TEACHING_TIPS_PAGE (eg #315)
            has substitution fxn %(TEACHING_TIPS)
                if all tips have been displayed, redirect to surveys/index
                if this tip has already been displayed, 
                    $this->controller->redirect('show/' . TEACHING_TIPS_PAGE);
                else $this->controller->render('teaching_tips');
                    next button links to 'show/' . TEACHING_TIPS_PAGE
     *
     */
    function show($page_id, $iteration=0)
    {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id, $iteration)", LOG_DEBUG);

        // Initialize session and check that this is an appropriate page for the session    
        $project_id = $this->session["Project"]["id"];
        if (!$project_id){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id, $iteration), !project_id so redirecting", LOG_DEBUG);
            $this->redirect($this->getSurveyHome());
        }
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), post possible redirect if no project ID; project: $project_id", LOG_DEBUG); 

        if(!$this->Page->inProject($page_id, $project_id)){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id, $iteration), page not in project so redirecting", LOG_DEBUG);
            $this->redirect($this->Project->firstPage($project_id));
        }
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), post possible redirect if page not in project; will get page next", LOG_DEBUG); 

        // Retrieve the page the requested page    
        $page = $this->Page->findById($page_id);
        $qr_id = $page["Questionnaire"]["id"];

        if ($page['Page']["ProgressType"] == 'graphical'
            || isset($this->session['Project']['skipped_questions_page']) 
                && $page_id == $this->session['Project']['skipped_questions_page']) { 

            // Find our progress in the survey
            $this->_percentFinished($project_id, $qr_id, $page_id);
        }

        // Trigger and catch if we are at skipped questions
        if(isset($this->session['Project']['skipped_questions_page']) 
                && $page_id == $this->session['Project']['skipped_questions_page']) {
            $this->Session->write('fromSkipped', true);
        }

        if($this->Session->read('fromSkipped')) {
            $this->set('fromSkipped', true);
        }
        $back_link = "/surveys/previous_page/$page_id";
        if ($iteration)
            $back_link .= "/$iteration";

        $this->set('back_link', $back_link);
        $this->set('next_link', "/surveys/next_page/$page_id");
        $this->set('session_id', $this->session_id);

        $this->set('iterable', $page['Page']['iterable']);
        $this->set('iteration', $iteration);

        $this->set('sessionType', $this->session['SurveySession']['type']);
        $this->set('treatment', $this->patient["Patient"]["study_group"] == Patient::TREATMENT);

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), project = " . print_r($this->session['Project'], true), LOG_DEBUG); 
        $this->set('project', $this->session['Project']);

        $allowed = false;
        $questions = array();
        //$this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), will iterate Q's next", LOG_DEBUG); 
        foreach($page['Question'] as $question) {
            if(!$allowed) {
                if($this->allowed_to_answer_question($question['id'])) {
                    $allowed = true;
                }
            }
            $question_row = $this->Question->findById($question['id']);
            //$this->log("question = " . print_r($question_row, true), LOG_DEBUG); 
            if ($question_row['Question']["has_conditional_options"]){
                //$this->log("question has_conditional_options", LOG_DEBUG); 
                $question_row['Option'] = //$conditionalOptions;
                    $this->Conditionality->getOptions(
                            $question_row, $this->session_id);
                // keep Options 0-indexed because that's what the view expects
                //$question_row['Option'] = array_values($question_row['Option']);
                //$this->log("here's what the options ended up as in survey/show: " . print_r($question_row['Option'], true), LOG_DEBUG);
                if (sizeof($question_row['Option']) == 0) continue; 
            }
            $question_row["Answer"] = $this->Answer->forSessionAndQuestion(
                $this->session_id,
                $question["id"],
                $iteration
            );
            array_push($questions, $question_row);

            /** //FIXME removed because items and question are now many : many, and this functionality not used enough to warrant an update 
            // get the associated subscale id, if any
            $item = $this->Item->findByQuestion($question['id']);

            if (!empty($item)) {
                $subscaleId = $item['Item']['subscale_id'];
            }*/
        }    
        //$this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), done iterating Q's", LOG_DEBUG); 

        # If not allowed to answer any (and there are questions), show a warning.
        if(count($page["Question"]) > 0 && !$allowed) {
            $this->set('not_allowed', true);
            if($this->done()) {
                $this->set('not_allowed_text', "return to main page");
                $this->set('not_allowed_link', '/surveys');
            } else {
                $this->set('not_allowed_text', 
                            "proceed with completing the survey");
                $this->set('not_allowed_link', '/surveys/restart/'.$this->session_id);
            }
        } else {
            $this->set('not_allowed', false);
        }

        # FIXME: remove debug conditions for production
        $this->set('not_shown', !$this->Conditionality->showPage($page_id, $this->session_id) || !$this->Conditionality->showQr($qr_id, $this->session_id));

        # Is there an alert for this page?
        $alerts = $this->_evaluate_alert($page_id);
        $this->set('alerts', $alerts);

        /* set up a link for the associated results page if this user is
            a Tester as well, and the session is finished. */
        /**
        if (!Configure::read('isProduction') &&
	    $this->session['Patient']['test_flag'] && 
            $this->SurveySession->finished($this->session['SurveySession'])) 
        {
            if (!empty($subscaleId)) {
                    $this->set('resultsLink', "/results/show/$subscaleId");
            } else {
                $this->set('resultsLink', self::NO_ASSOCIATED_RESULTS);
            }
        }
        */

        // Substitution for page and questions
        $this->set("page", $page['Page']);

//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "($page_id), questions being set: " . print_r($questions, true), LOG_DEBUG);
    
        $this->set("questions", $questions);
        $page = $this->Substitution->for_page_in_session($page, $this->session_id);
        $this->set("page", $page['Page']);
        $questions = $this->Substitution->for_questions_in_session($questions, $this->session_id);
        $this->set("questions", $questions);

        if ($this->Session->check(self::TAKING_SURVEY_AS)) {
            $this->set("takingSurveyAs", $this->patient);
        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), done.", LOG_DEBUG); 
    } // function show($page_id)

    /**
     *
     */
    function next_page($page_id) {
       
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id)", LOG_DEBUG); 

        $this->Patient->next_page_clicked($page_id, $this->patient);
 
        // if advancing past the skipped questions page,
        // no need to show link to return to it, since questions won't be 
        // answerable.
        if(isset($this->session['Project']['skipped_questions_page']) 
                && $page_id 
                    == $this->session['Project']['skipped_questions_page']){ 
            $this->Session->delete("fromSkipped");
        }

        $next_page_id = $this->Conditionality->nextPage($page_id, $this->session);
        
//        $this->log("surveys/next_page($page_id), calculated next_page_id: " . $next_page_id, LOG_DEBUG);

        if(!isset($next_page_id) OR ($next_page_id == 0))
        {
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), next_page_id ($next_page_id) null or 0 so calling complete()", LOG_DEBUG);
            // not the best message, since data are saved instantly via ajax
            $finishSurveyText = "Assessment data have been saved.";
            if (isset($this->session['Project']['finish_survey_txt']))
                $finishSurveyText = $this->session['Project']['finish_survey_txt'];
            $this->Session->setFlash(__($finishSurveyText));

            $this->complete(2);
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), next_page_id ($next_page_id) null or 0 called complete() and now redirecting out of survey", LOG_DEBUG);
            if ($this->is_staff){ 
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), next_page_id ($next_page_id) null or 0 called complete() and now redirecting to patients/edit", LOG_DEBUG);
                $this->redirect("/patients/edit/" 
                    . $this->session['SurveySession']['patient_id']);
            }
            else {
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), next_page_id ($next_page_id) null or 0 called complete() and now redirecting to root", LOG_DEBUG);
                $this->redirect("/");
            }
            /**
            if ($this->Session->check(self::TAKING_SURVEY_AS)) {
            // taking a survey as a patient, go back to the patient edit
                $this->Session->delete(self::TAKING_SURVEY_AS);
                $this->Session->delete("session_id");
                $this->redirect('/patients/edit/' . $this->surveyPatientId);
            } else {
                $this->redirect("/surveys");
            }
            */
        }

        $interrupt = $this->_interrupt_survey($page_id, $next_page_id);

        if(isset($interrupt) && $interrupt) {
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), redirecting per interrupt to: $interrupt", LOG_DEBUG);
            $this->redirect($interrupt);
        } else {
            if (isset($this->session['Project']['skipped_questions_page'])
                && $next_page_id 
                    == $this->session['Project']['skipped_questions_page'] 
                && $this->session['SurveySession']['partial_finalization'] 
                                                                        == 1){
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), next is skipped_questions_page, and partial_finalization == 1, so redirecting to surveys/next_page/(skipped_questions_page == " . $this->session['Project']['skipped_questions_page'] . ")", LOG_DEBUG);
                $this->redirect("/surveys/next_page/" 
                    . $this->session['Project']['skipped_questions_page']);
            }
            elseif (isset($this->session['Project']['complete_btn_page'])
                && $next_page_id == $this->session['Project']['complete_btn_page'] 
                    && $this->session['SurveySession']['finished'] == 1){
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), next is complete_btn_page and finished == 1, so redirecting to surveys/next_page/(complete_btn_page == " . $this->session['Project']['complete_btn_page'] . ")", LOG_DEBUG);
                $this->redirect("/surveys/next_page/" 
                    . $this->session['Project']['complete_btn_page']);
            }

//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), redirecting to surveys/show/(next_page_id == $next_page_id)", LOG_DEBUG);
            $this->redirect("/surveys/show/$next_page_id");
        }
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id), returning w/out redirecting", LOG_DEBUG);
    }// function next_page($page_id) {

    /**
     *
     */
    function previous_page($page_id, $iteration=0) {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id, $iteration)", LOG_DEBUG);

        if ($iteration > 0){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($page_id, $iteration), iteration > 0 so redirecting", LOG_DEBUG);
            $this->redirect(array('action' => 'show', $page_id, $iteration-1));
        }

        $prev_page = $this->Conditionality->previousPage($page_id, $this->session);
        //NOTE: PUTTING A PREVIOUS BUTTON ON THE FIRST PAGE OF A QUESTIONNAIRE WILL RESULT IN UNDEFINED BEHAVIOR. WOULDN'T BE MUCH WORK TO IMPL THO, JUST LOOK AT $this->next_page()

        $interrupt = $this->_interrupt_survey($page_id, $prev_page['Page']['id']);
        if ($interrupt){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($q_id), redirecting to $interrupt", LOG_DEBUG);
            $this->redirect($interrupt);
        }
        else if (
            isset($this->session['Project']['skipped_questions_page']) and
            $prev_page['Page']['id'] == $this->session['Project']['skipped_questions_page'] and
            $this->session['SurveySession']['partial_finalization'] == 1
        ){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($q_id), redirecting to surveys/previous_page/" . $this->session['Project']['skipped_questions_page'], LOG_DEBUG);
            $this->redirect(array(
                'controller' => 'surveys',
                'action' => 'previous_page',
                $this->session['Project']['skipped_questions_page'],
            ));
        }
        else if ($prev_page['Page']['iterable']){

            // Check if the last page had any answers on iterations > 0
            $lastIteration = $this->Answer->find('all', array(
                'joins' => array(
                    array(
                        'table' => 'questions',
                        'alias' => 'Question',
                        'conditions' => array('Question.id = Answer.question_id'),
                    ),
                ),
                'fields' => array('max(Answer.iteration) as max'),
                'conditions' => array(
                    'Answer.survey_session_id' => $this->session['SurveySession']['id'],
                    'Question.page_id' => $prev_page['Page']['id'],
                ),
                'recursive' => -1,
            ));
            if (
                isset($lastIteration[0][0]['max']) and
                $lastIteration[0][0]['max'] > 0
            )
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($q_id), redirecting to surveys/show/" . $prev_page['Page']['id'], LOG_DEBUG);
                $this->redirect(array(
                    'controller' => 'surveys',
                    'action' => 'show',
                    $prev_page['Page']['id'],
                    $lastIteration[0][0]['max']
                ));

        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($q_id), redirecting to surveys/show/" . $prev_page['Page']['id'], LOG_DEBUG);

        $this->redirect(array(
            'controller' => 'surveys',
            'action' => 'show',
            $prev_page['Page']['id'],
        ));
    }// function previous_page($page_id) {

    /** Evaluated for all survey next/previous button clicks:
     * if you want to interrupt, return the url to send the user to */
    function _interrupt_survey($page_id, $new_page_id) {
        # Display an alert
        if($alerts = $this->Session->read("alerts")) {
            $this->Session->write('alerts', 0); # Only display alert once
            return "/surveys/show/$page_id"; # FIXME: lookup alert page
        }
        # FIXME: this is the slow way to do this
        # FIXME: only if non_t-time survey 
        if(
            $this->session['SurveySession']['type'] == SurveySession::NONT  ||
            $this->session['SurveySession']['type'] == ELECTIVE
        ) {
            $old_page = $this->Page->findById($page_id);
            $old_qr = $old_page["Page"]["questionnaire_id"];
            $new_page = $this->Page->findById($new_page_id);
            $new_qr = $new_page["Page"]["questionnaire_id"];
            if($old_qr != $new_qr) {
                if(($old_qr == 1 && $new_qr == 19) || ($old_qr == 19 && $new_qr == 1)) {
                    return false;
                    # must show QLQ/CIPN together, depends on db definition
                    # REMOVE this condition for other survey definitions or dhair projects
                } else {
                    return "/surveys/questionnaires";
                }
            }
        }
    }

    /** Checks for alerts for this page and evaluates necessary instruments.
     *  Returns alert message array (true) if it should be shown, else false */
    function _evaluate_alert($page_id) {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(page_id:$page_id)", LOG_DEBUG);
        $session_id = $this->Session->read("session_id");
        $alerts = $this->Alert->findAllByPageId($page_id);
        if(!$alerts) {
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(page_id:$page_id), no alerts so returning false", LOG_DEBUG);
            return false;
        }
        $triggered_alerts = array();

        foreach($alerts as $alert) {
            switch($alert["Alert"]["target_type"]) {
            case "item":
                $value = $this->Instruments->calculate_item($alert["Alert"]["target_id"], null);
                break;

            case "subscale":
                $value = $this->Instruments->calculate_subscale($alert["Alert"]["target_id"], 0, true);
                break;

            case "scale":
                $value = $this->Instruments->calculate_subscale($alert["Alert"]["target_id"], 0, true);
                break;

            default:
                trigger_error("Incorrectly-defined alert");
            }

            $triggered = false; 
            switch($alert["Alert"]["comparison"]) {
            case ">":
                $triggered = $value > $alert["Alert"]["value"];
                break;
            case "<":
                $triggered = $value < $alert["Alert"]["value"];
                break;
            case "=":
                $triggered = $value == $alert["Alert"]["value"];
                break;
            }

            if($triggered) {
                array_push($triggered_alerts, $alert);
            }
        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(page_id:$page_id), returning: " . print_r($triggered_alerts, true), LOG_DEBUG);
        return $triggered_alerts;
    }// function _evaluate_alert($page_id) {

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
     * 
     */
    function print_csv_line($array) {
        # Need to do escaping, etc...
        foreach($array as &$item) {
            $item = str_replace(array("\"", "\n", "\r"), 
                                array('\"'), $item);
        }
        $line = "\"" . implode("\",\"", $array) . "\"";
        print $line . "\n";
    }

    # Print to the screen a csv listing of all questions with basic data for researcher review
    function summary_csv() {
        $questions = $this->Question->allShownQuestions();
        $this->print_csv_line(array("Page id", "Page title", "Page header", "Question id", "Question text", "Options..."));
        foreach($questions as $question) {
            $line = array($question["Page"]["id"],
                          $question["Page"]["Title"],
                          $question["Page"]["Header"],
                          $question["Question"]["id"],
                          $question["Question"]["BodyText"]);
            foreach($question["Option"] as $option) {
                array_push($line, $option["BodyText"]);
            }
            $this->print_csv_line($line);
        }
        exit;
    }

    /**
     *  Project/Survey editor 
     *
     */
    function summary($project_id, $language = 'all', $textOnly = 'false') {
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('jquery.jeditable.js','cpro.editable.js'));
        
        $this->layout = 'default'; // still show tabs for the main listing
        $this->TabFilter->selected_tab("Editor");
        
        $i18nLanguages = array();
        $inclEnglish = true;
        $textOnly = ($textOnly && strtolower($textOnly) !== "false")? true : false;

        if ($this->i18nLanguages){
            if ($language == 'all'){
                $i18nLanguages = $this->i18nLanguages;
                unset($i18nLanguages['English']);
            }
            else {
                $i18nLanguages[] = $language;
                $inclEnglish = false;
            }
        }

        $surveyHtml = '';
        $surveyHtml = "<p>Settings: inclEnglish = " . ($inclEnglish ? 'true' : 'false') . "; textOnly = $textOnly; i18nLanguages = " . print_r($i18nLanguages, true) . "</p><hr/>";

        # Project info
        $projectInfo = '';
        
        $project = $this->Project->findById($project_id);
        $title = $project["Project"]["Title"];
        $id = $project["Project"]['id'];
        $projectInfo .= "<h1>$title (Project #$id)</h1>";
        $this->set('projectInfo', $projectInfo);
        
        # table of contents 
        $tableOfContents = '';
        $tableOfContents.= "<div class='section'>Questionnaires:</div>";
        $seq = 1;
        foreach($project['Questionnaire'] as $qr) {
            
            $title = $qr["Title"];
            $id = $qr['id'];

            $tableOfContents.= "<li><a href='#qnr_$id' class='scroll-on-page topQnrList'>$seq. $title (#$id)</a></li>";

            $seq++;
        }
        $this->set('tableOfContents', $tableOfContents);

        // Wrapping all questions in <div> in order to use css
        $surveyHtml.= "<div class='admin-editor-wrapper'>";
        $surveyHtml.= "<ul>";
        
        // Begin foreach that displays all pages/questions
        foreach ($project['Questionnaire'] as $qr) {
            $title = $qr["Title"]; 
            $id = $qr['id'];

            $qr = $this->Questionnaire->findById($id);
            $scales = 
                $this->Scale->find('all', 
                                    array(
                                    'conditions' => 
                                        array("Scale.questionnaire_id" => ($id))));
            //$scales = $this->Scale->findByQuestionnaireId($id);
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); scales for qnr ID $id: " . print_r($scales, true), LOG_DEBUG);

            if (!$textOnly) 
                $surveyHtml.= "<br/>" .
                    "<li class='qnr' id='qnr_$id'><h2>$title (Qnr#$id)</h2>\n";

            // look for conditions which apply to this; display info here
            if (!$textOnly) 
                $surveyHtml.= $this->Condition->getAsHtml('Questionnaire', $id);
 
            foreach ($scales as $scale){

              $scale_id = $scale["Scale"]["id"];
              $scale_name = $scale["Scale"]["name"];
              $scale_text = "$scale_name scale ($scale_id)";
              if(!$scale_id)
                  $scale_text = "";
              if (!$textOnly) 
                  $surveyHtml.= "<h3>$scale_text</h3>\n";    

              if (!$textOnly){ 
                if(isset($scale["Subscale"])) {
                  $surveyHtml.= "<p>";
                  foreach($scale["Subscale"] as $subscale) {
                      $name = $subscale["name"];
                      $id = $subscale["id"];
                      $com = $subscale["combination"];
                      $range = $subscale["range"];
                      $invert = $subscale["invert"];
                      $base = $subscale["base"];
                      $critical = $subscale["critical"];

                      $surveyHtml.= "<a name='ss_$id'>$name subscale ($id): combine with: $com, range: $range, base: $base, invert: $invert, critical: $critical</a><br/>\n";
                  }
                  $surveyHtml.= "</p>";
                }
              }
            }//foreach ($scales as $scale){

            if (!$textOnly) 
                  $surveyHtml.= "<ul>\n";    

            foreach ($qr["Page"] as $page) {
                $pageId = $page['id'];
                if(!$textOnly)
                    $surveyHtml.= "<li id='page_$pageId' class='page'>"; 
                if (!$textOnly) { 
                    $surveyHtml.= "<button class='btn btn-small pull-right go-down'><i class='icon-arrow-down'></i> Go to next page</button>";
                    $surveyHtml.= "<h3>Page #$pageId, seq=" . $page['Sequence'] . "</h3>";

                    // look for conditions which apply to this; display info here
                    $surveyHtml.= $this->Condition->getAsHtml('Page', $pageId);

                    $alerts = $this->Alert->findAllByPageId($pageId);
                    foreach ($alerts as $alert){
                        $alert = $alert['Alert'];
                        $surveyHtml.= "<i>This page will be redisplayed with the following alert message if the score of " . $alert['target_type'] . " " . $alert['target_id'] . " is " . $alert['comparison'] . " " . $alert['value'] . "</i> : " . $alert['message'];
                    }
                }
 
                $pageFields = array('Title', 'Header', 'BodyText');
                foreach($pageFields as $field){
                    //if (isset($page[$field])){
                      if (!$textOnly && $inclEnglish)
                        $surveyHtml.= "<h4>$field:";
                        if ($field == 'BodyText') {
                            $surveyHtml.= "<button class='editor-preview btn btn-mini' title='Preview in new window'>Preview <i class='icon-zoom-in icon-gray'></i></button>";
                        }
                        $surveyHtml.= "</h4>";
                      // Page title is actually not used by dhair2; 
                      // in the orig dhair it was used as the html page title
                      if ($inclEnglish === true && 
                            (!$textOnly || 
                                ($textOnly && $field != 'Title'))) 
                        $surveyHtml.= "<span class='editable edit-page edit-$field' data-edit_type='text' data-db_table='Page' data-db_id='$pageId' data-db_col='$field'>" . $page[$field] . '</span>' . "<br/>\n";
                      foreach($i18nLanguages as $lang){
                        $lang = '_' . $lang;
                        if (!$textOnly) 
                          $surveyHtml.= "<h4>$field$lang:";
                          if ($field == 'BodyText') {
                            $surveyHtml.= "<button class='editor-preview btn btn-mini' title='Preview in new window'>Preview <i class='icon-zoom-in icon-gray'></i></button>";
                          }
                          $surveyHtml.= "</h4>";
                        if (isset($page[$field . $lang])){
                          if (!$textOnly || ($textOnly && $field != 'Title')) 
                            $surveyHtml.= "<span class='editable edit-page edit-$field' data-edit_type='text' data-db_table='Page' data-db_id='$pageId' data-db_col='$field$lang'>" . $page[$field . $lang] . '</span>' . "<br/>\n";
                        }
                        else {
                            if (!$textOnly) 
                                        $surveyHtml.="<span class='editable edit-page edit-$field' data-edit_type='textarea' data-db_table='Question' data-db_id='$pageId' data-db_col='$field$lang'>" . '<i>(TRANSLATION MISSING)</i>' . "</span>" . "<br/>\n";
                        } 
                      }
                    //}
                } 
                if (!$textOnly) 
                  $surveyHtml.= "<h4>Questions</h4>";

                //if (!$textOnly) 
                  $surveyHtml.= "<ul>";

                // Get info about Questions, if any
                $page = $this->Page->findById($pageId);
                if (empty($page['Question']) && !$textOnly) $surveyHtml.= 
                    "(no questions for this page)";
                foreach($page["Question"] as $q) {
                    //if (!$textOnly) 
                      $surveyHtml.= "<li>";
                    $qId = $q["id"];
                    if (!$textOnly) 
                      $surveyHtml.= "<h5>Question #$qId</h5>";
                    if (!$textOnly) $surveyHtml.= 
                        $this->Condition->getAsHtml('Question', $qId);

                    $qFields = array('ShortTitle', 'BodyText');
                    foreach($qFields as $field){
                        //if (isset($q[$field])){
                            if (!$textOnly) {
                                $surveyHtml.= "<h5>$field:";
                                if ($field == 'BodyText') {
                                  $surveyHtml.= "<button class='editor-preview btn btn-mini' title='Preview in new window'>Preview <i class='icon-zoom-in icon-gray'></i></button>";
                                }
                                $surveyHtml.= "</h5>";
                            }
                            if ($inclEnglish === true) 
                                $surveyHtml.= "<span class='editable edit-question edit-$field' data-edit_type='textarea' data-db_table='Question' data-db_id='$qId' data-db_col='$field'>" . $q[$field] . "</span>" . "<br/>\n";
                            foreach($i18nLanguages as $lang){
                                $lang = '_' . $lang;
                                if (!$textOnly) {
                                    $surveyHtml.= "<h5>$field$lang:";
                                    if ($field == 'BodyText') {
                                      $surveyHtml.= "<button class='editor-preview btn btn-mini' title='Preview in new window'>Preview <i class='icon-zoom-in icon-gray'></i></button>";
                                    }
                                    $surveyHtml.= "</h5>";
                                }
                                if (isset($q[$field . $lang])){
                                    $surveyHtml.= "<span class='editable edit-question edit-$field' data-edit_type='textarea' data-db_table='Question' data-db_id='$qId' data-db_col='$field$lang'>" . $q[$field . $lang] . "</span>" . "<br/>\n";
                                }
                                else {
                                  if (!$textOnly) $surveyHtml.= 
                                        "<span class='editable edit-question edit-$field' data-edit_type='textarea' data-db_table='Question' data-db_id='$qId' data-db_col='$field$lang'>" . '<i>(TRANSLATION MISSING)</i>' . "</span>" . "<br/>\n";
                                } 
                            }
                        //}
                    } 
                    // Get info about Item and Options, if any
                    $question = $this->Question->findById($qId);

                    foreach($question["Item"] as $item){
                        //$item = $question["Item"];
                        $item_id = $item["id"];
                        $item_ss_id = $item["subscale_id"];
                        $item_range = $item["range"];
                        $item_base = $item["base"];
                        $item_text = " -> Item $item_id [range $item_range, base $item_base] in <a href='#ss_$item_ss_id'>subscale $item_ss_id</a>";
                        if(!$item_id) 
                            $item_text = "";
                        if (!$textOnly) 
                            $surveyHtml.= "<h5>$item_text</h5>";

                    }
                    if (!$textOnly) 
                        $surveyHtml.= "<h5>Options</h5>";

                    if (!$textOnly) 
                        $surveyHtml.= "<ul>";

                    foreach($question["Option"] as $option) {
                        $optionId = $option["id"];
                        $type = $option["OptionType"];
                        $field = "BodyText";
                        $text = '<ul>';
                        if ($inclEnglish === true) 
                          $text .= "<li>"  . 
                             "<span class='editable edit-option edit-$field' data-edit_type='text' data-db_table='Option' data-db_id='$optionId' data-db_col='$field'>" . $option[$field] . "</span></li>";
                        foreach($i18nLanguages as $lang){
                            $lang = '_' . $lang;
                            $text .= "<li>";
                            if (isset($option[$field . $lang])){
                              $text .= "<span class='editable edit-option edit-$field' data-edit_type='text' data-db_table='Option' data-db_id='$optionId' data-db_col='$field$lang''>" . $option[$field . $lang] . "</span>";
                            }
                            else{
                              $text .= 
                                "<span class='editable edit-option edit-$field' data-edit_type='text' data-db_table='Option' data-db_id='$optionId' data-db_col='$field$lang'>" . "(TRANSLATION MISSING FOR $lang)" . "</span>";
                            } 
                            $text .= "</li>";
                            
                        }
                        $text .= '</ul>';
                        $value = "seq={$option["Sequence"]}";
                        if (!is_null($option['AnalysisValue']))
                            $value .= ";analysisValue={$option["AnalysisValue"]}";
                        if (!is_null($option['AncillaryText']))
                            $value .= ";AncillaryText={$option["AncillaryText"]}";

                        if (!$textOnly) $surveyHtml.= 
                          "<li> Option $optionId : $type : $value : ";
                        $surveyHtml .= $text;
                        if (!$textOnly) $surveyHtml.= 
                            $this->Condition->getAsHtml('Option', $optionId) . "</li>";
                    }

                    //if (!$textOnly) 
                        $surveyHtml.= "</ul></li>";
                }// foreach($page["Question"] as $q) {
                    
                //if (!$textOnly) 
                    $surveyHtml.= "</ul></li>";
            }// foreach ($qr["Page"] as $page) {

            //if (!$textOnly) 
                $surveyHtml.= "</ul></li>";
        }// foreach ($project['Questionnaire'] as $qr) {
        //if (!$textOnly) 
        $surveyHtml.= "</ul>";
        $surveyHtml.= "</div>";
        $this->set('surveyHtml', $surveyHtml);
    } // function summary($project_id, $language = '', $textOnly = false) {


    /**
    *   @param $appointment_id 
    *   //TODO @param $select_option Which options to select; can be either: min, max, rand, median, none 
    *   //TODO @param $checkbox_option Which check boxes to select; can be either: min, max, rand, all, none
    */
    function generate_se_test($patientId, $appointment_id = null, 
                                $select_option = 'max') {

        $this->layout = 'clinic_report'; // using this because it's blank

        /**
        Why do we need session record?
            because conditionality needs session reference
        Why do we need patient record?
            to create session, and because some conditionality fxns
            expect $this->patient to be set
        */
        /**Look for any T session w/ same type, if can't find one, create it*/
        $this->session = 
            $this->SurveySession->find('first', array('conditions' =>
                        array("SurveySession.appointment_id" => $appointment_id,
                                    "SurveySession.patient_id" => $patientId)));       

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); session: " . print_r($this->session, true), LOG_DEBUG);

        $project_id = $this->session['SurveySession']['project_id'];
    
        $this->patient = $this->Patient->findById($patientId);
        //FIXME remove the session creation code?
        // this would mean staff need to login twice as patient - not so easy
        if (empty($this->session)){
            $this->SurveySession->save(array
                ('SurveySession' => array(
                        'appointment_id' => $appointment_id,
                        'started' => $this->DhairDateTime->UsersCurrentTimeStr(),
                        "user_id" => $patientId,
                        "project_id" => $project_id,
                        "patient_id" => $patientId
                ))
            );
            $this->session = $this->SurveySession->find(
                                    array("appointment_id" => $appointment_id,
                                        "patient_id" => $patientId));        
        }
        if ($this->session['Patient']['test_flag'] != 1) return;
        $s_id = $this->session['SurveySession']['id'];

        $pagesWAlerts = $this->Alert->getListOfPagesWAlerts();
        //$this->DhairLogging->logArrayContents($pagesWAlerts, "pageWAssertions");
        
        $project = $this->Project->findById($project_id);
        $title = $project["Project"]["Title"];
        $id = $project["Project"]['id'];
        
        $this->_se_script_header();
        print "<!-- THIS SCRIPT IS FOR PATIENT $patientId, APPOINTMENT #" . 
            $this->Appointment->getAppointmentNumber(
                                    $appointment_id, $patientId) . 
            " (ID =  $appointment_id), PATIENT $patientId, SESSION #" .
            $this->SurveySession->getSessionNumber(
                                    $s_id, $patientId) .
            " (ID = $s_id), PROJECT ID $project_id, SELECT OPTIONS $select_option -->\n";    
        print "<!-- HOW TO USE THIS SCRIPT -->\n";    
        print "<!-- While viewing in the browser, select \"File\" - \"Save Page As\", and save as \"Web Page, HTML only\". -->\n";    
        //print "<!-- Copy the text displayed on this page, and paste it into a new file in your favorite text editor (e.g. notepad). Save the file as an .html file. Then run that html file in the Selenium IDE Firefox plugin (File - Open - then click the \"Play current test case\" button).  -->\n";    
        print "<!-- Then run that html file in the Selenium IDE Firefox plugin (File - Open - and click the \"Play current test case\" button).  -->\n";    
        
        foreach ($project['Questionnaire'] as $qr) {
            $title = $qr["Title"]; 
            $id = $qr['id'];
            if ($id == 17){
                // PROMIS isn't displayed if they answer all min options
                if ($select_option == 'min') continue; 
            }
            elseif($this->Conditionality->showQr($id, $s_id) === false) 
                continue;

            $qr = $this->Questionnaire->findById($id);
            $scales = $this->Scale->findByQuestionnaireId($id);
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); scales: " . print_r($scales, true), LOG_DEBUG);
            $scale_id = '(none)';
            $scale_name = '(none)';
            if (array_key_exists('Scale', $scales)){
                $scale_id = $scales["Scale"]["id"];
                $scale_name = $scales["Scale"]["name"];
            }
            $scale_text = " -> $scale_name scale ($scale_id)";
            print "<!-- Questionnaire $id: $title -->\n";    

            foreach ($qr["Page"] as $page) {
                $title = $page["Title"];
                $pageId = $page['id'];
                // FIXME for now, had to hard code comparisonQuestion in ...
                // FIXME hard code in for male condition (T1 only) 
                // Complex conditions for religion q's...
                if ($pageId == 311){
                    // "How much strength/comfort ... from religion..." 
                    if ($select_option == 'max') continue; 
                }   
                elseif ($pageId == 312){
                    // "Has there ever been a time when religion...important" 
                    if ($select_option == 'min') continue; 
                }
                elseif ($pageId == 313){
                    // "Would you like chaplain visit" page
                    // complex condtns, but they dont prevent this for max/min
                }   
                elseif ($pageId == 318){
                    // mod-high level PHQ9 alert page
                    if ($select_option == 'min') continue; 
                }   
                elseif (($qr['Questionnaire']['id'] == 18) && ($pageId != 137)){
                    // only display skin questions if the answer
                    //   to the first skin question was > 1
                    if ($select_option == 'min') continue; 
                }
                elseif( ! $this->Conditionality->showPage($pageId, $s_id)) continue;
                print "<!-- Page $pageId: $title -->\n";
                if ((isset($project['Project']['skipped_questions_page'])
                        && $pageId == $project['Project']['skipped_questions_page'])
                        || ($pageId == $project['Project']['complete_btn_page'])){ // "Total" finalization 
                    //  can't assertTextPresent for page[BodyText], as that's not used by survey for these, text gen'd by view 
                    //  simply click "Complete" button
                    print"<!--Pausing for a minute, in case you want to halt here... -->\n";
                    print "<tr><td>pause</td><td>60000</td><td></td></tr>\n";
                    print "<tr><td>clickAndWait</td>\n";
                    print "<td>class=complete-survey</td>\n";
                    print "<td></td></tr>\n\n";
                    continue; 
                }
                $this->_se_script_assertion($page['BodyText']);
                
                $page = $this->Page->findById($pageId);
                foreach($page["Question"] as $q) {
                    $text = $q["BodyText"];
                    $id = $q["id"];
                    $question = $this->Question->findById($id);

                    $item_text = '';
                    foreach($question["Item"] as $item){
                        $item_id = $item["id"];
                        $item_ss_id = $item["subscale_id"];
                        $item_range = $item["range"];
                        $item_base = $item["base"];
                        if($item_id) $item_text .= " -> Item $item_id in <a href='#ss_$item_ss_id'>subscale $item_ss_id</a> [range $item_range, base $item_base]\n";
                    }

                    print "<!-- Question ($id) $text $item_text -->\n";
                   
                    $this->_se_script_assertion($q['BodyText']);

                    $deselect_option;
                    switch ($select_option){
                        case "min":
                            $select = 1;
                            $deselect_option = 2;
                            break;
                        case "max":
                            $select = sizeof($question["Option"]);
                            $deselect_option = $select - 1;
                            break;
                        /**case "rand": //TODO needs impl
                            $select = sizeof($question["Option"]);
                            break;
                        case "med": //TODO needs impl
                            $select = sizeof($question["Option"]);
                            break;
                        case "none": //TODO wontwork if some already selected 
                            $select = sizeof($question["Option"]) + 1;
                            break;*/
                    }

                    foreach($question["Option"] as $option) {
                      $type = $option["OptionType"];
                      $text = $option["BodyText"];
                      $id = $option["id"];
                      $sequence = $option["Sequence"];
                      if ($type == "radio"){
                        // TODO impl deselect 
                        if ($sequence == $select){
                                
                          print "<!-- option $type: $text -> $sequence ($id) -->\n";

                          print "<tr><td>click</td>\n";
                          print "<td>id=" . $q["id"] . "-" . $id . "</td>\n";

                          print "<td></td></tr>\n";
                          print "<tr><td>pause</td><td>500</td><td></td></tr>\n";
                          // assert radio button was clicked
                          //print "<tr><td>assertAttribute</td>";
                          //print "<td>id=" . $q["id"] . "-" . $id . 
                          //          "@class</td>";
                          //print "<td>radio-button selected</td></tr>\n";
                        }
                      } 
                      elseif ($type == 'checkbox'){
                        // FIXME Currently clicks all checkboxes... 
                        // TODO how to detect whether option has already been selected???
                        print "<!-- option $type: $text -> $sequence ($id) -->\n";

                        print "<tr><td>click</td>\n";
                        print "<td>id=" . $q["id"] . "-" . $id . "</td>\n";

                        print "<td></td></tr>\n";
                        print "<tr><td>pause</td><td>500</td><td></td></tr>\n";
                      }
                      elseif ($type == 'textbox'){
                        print "<!-- option $type: $text -> $sequence ($id) -->\n";

                        print "<tr><td>typeKeys</td>\n";
                        print "<td>" . $q["id"] . "</td>\n";
                        print "<td>This is text which the testing tool added</td></tr>\n";
                        print "<tr><td>pause</td><td>500</td><td></td></tr>\n";
                      }
                      elseif ($type == 'text'){
                        print "<!-- option $type: $text -> $sequence ($id) -->\n";

                        print "<tr><td>typeKeys</td>\n";
                        print "<td>" . $q["id"] . "</td>\n";
                        print "<td>1976</td></tr>\n";
                        print "<tr><td>pause</td><td>500</td><td></td></tr>\n";
                      }
                        // TODO impl other option types!           
                    }
                } // foreach($page["Question"] as $q) {
                // go to next page 
                // FIXME If we expect this page will show an alert, we need to advance through the page twice. Currently, the only alerts are for high values 
                if (in_array($pageId, $pagesWAlerts) && 
                        ($select_option == 'max')){
                    print "<!-- Two clicks for redisplay of page w/ alert-->\n";
                    print "<tr><td>clickAndWait</td>";
                    print "<td>//a[@title='Next Page']</td><td></td></tr>\n";
                }
                print "<tr><td>clickAndWait</td>";
                print "<td>//a[@title='Next Page']</td><td></td></tr>\n\n";
            } // foreach ($qr["Page"] as $page) {
        }
        $this->_se_script_footer($appointment_id, $patientId, $s_id, $select_option);
    } 

    private function _se_script_header() {
        //print "<xmp>\n";       
        print "<head><title>selenium</title></head>\n"
            . "<body>\n" 
            . "<table>\n";
    }

    private function _se_script_footer($appointment_id, $patientId, 
                                            $s_id, $select_option) {
        print "</tbody></table></body></html>\n";
        print "<!-- End of script for appointment ID $appointment_id, patient $patientId, session ID $s_id, SELECT OPTIONS $select_option -->\n";    
        //print "</xmp>\n";
    }

    private function _se_script_assertion($bodyText) {
                if (isset($bodyText)){
                    $bodyTextToFind = $bodyText;
                    $bodyTextToFind = strip_tags($bodyTextToFind); 
                    // remove substitution fxns
                    $bodyTextToFind = 
                            preg_replace("/%\((.*?)?\)/", "", $bodyTextToFind);
                    $bodyTextToFind = trim($bodyTextToFind);
                    $maxStrLen = 10;
                    $bodyTextToFind = substr($bodyTextToFind, 0, $maxStrLen);
                    if ($bodyTextToFind != ""){
                        print "<tr><td>assertTextPresent</td><td>" 
                                . $bodyTextToFind . "</td><td></td></tr>\n"; 
                    }
                }
    }

    private function partially_done() {
        return $this->session["SurveySession"]["partial_finalization"];
    }

    private function done() {
        return $this->session["SurveySession"]["finished"];
    }

    // Return false if done or if partially done and this isn't one of the 'special' questions.
    private function allowed_to_answer_question($question_id) {
        return !$this->partially_done() 
               ||(!$this->done() && 
                  in_array($question_id, array(RANKING_Q, OPEN_TEXT_Q, PARTICIPATION_QUESTION, PARTICIPATION_QUESTION_NEW)));
    }

    private function save_alerts_to_session($alerts) {
        if($alerts) {
            $alert_ids = array();
            foreach($alerts as $alert) {
                array_push($alert_ids, $alert["Alert"]["id"]);
            }
            if($alert_ids == array()) {
                $this->Session->write('alerts', false);
            } else {
                $this->Session->write('alerts', $alert_ids);
            }
        }
    }

    /*
     * AJAX method
     */
    function answer($question_id){
        if (!$this->request->isAjax()) return;

        $result = array(
            'ok' => false,
            'message' => 'error saving answer',
            // 'debug' => $this->data,
        );
        $this->viewVars = &$result;
        $this->set(array('_serialize' => array_keys($result)));

        $answer = $this->data;

        if ($this->allowed_to_answer_question($question_id)) {
	        // set GMT for timestamp, otherwise Cake will set it to local time
	        $answer['Answer']['modified'] = gmdate(MYSQL_DATETIME_FORMAT);
            $answer['Answer']['survey_session_id'] = $this->session_id;

            $savedAnswer = $this->Answer->save($answer);
            if ($savedAnswer){
                $result['ok'] = true;
                $result['message'] = 'Answer saved successfully';
            }

            // Attempt to save uploaded images
            if (isset($answer['Answer']['value']['type']) and $savedAnswer){

                // Add image information to response
                $result['files'] = array();
                $this->set(array('_serialize' => array_keys($result)));

                $id = String::uuid();
                if (move_uploaded_file(
                    $answer['Answer']['value']['tmp_name'],
                    APP.'securedata'.DS.'images'.DS.$id
                )){
                    $image = array(
                        'id' => $id,
                        'answer_id' => $savedAnswer['Answer']['id'],
                        'patient_id' => $this->session['SurveySession']['patient_id'],
                        'filename' => $answer['Answer']['value']['name'],
                        'created' => gmdate(MYSQL_DATETIME_FORMAT),
                    );

                    $this->Answer->bindModel(array('hasOne' => array(
                            'Image' => array('dependent' => true)
                    )), false);
                    if ($this->Answer->Image->save($image)){
                        $result['message'] = 'Answer and Image saved successfully';
                        array_push(
                            $result['files'],
                            array(
                                'name' => $answer['Answer']['value']['name'],
                                'size' => $answer['Answer']['value']['size'],
                                'url' => Router::url(array(
                                    'controller' => 'images',
                                    'action' => 'view',
                                    $id
                                )),
                            )
                        );
                    }
                }
            }
        }

        // See if this answer triggered any alerts
        $alerts = $this->_evaluate_alert($answer["Page"]["id"]);
        $this->save_alerts_to_session($alerts);
    }// function answer($question_id)

    /** displays a table of contents for the current survey session
     * with a list of available questionnairres, the submit button, 
     * and some help text. Only show for elective aka nonT sessions */
    function questionnaires()
    {
        $session_id = $this->Session->read("session_id");
        $session = $this->SurveySession->findById($session_id);
        $qrs = $this->Project->questionnaires($session["SurveySession"]["project_id"]);

        $show_qrs = array();
        foreach($qrs as $qr) {
            # don't show CIPN in questionnaires list REMOVE for other dhair apps
            if($qr["id"] == 19)
                continue;

            if($this->Conditionality->showQr($qr["id"], $session_id)) {
                array_push($show_qrs, $qr);
            }
        }

        $this->set('qrs', $show_qrs);
    }

    /** redirects to the first page in this qr that should be shown for the current session */
    function questionnaire($qr_id) {
        $session_id = $this->Session->read('session_id');
        $session = $this->SurveySession->findById($session_id);

        $first_page = $this->Questionnaire->first_page($qr_id);
        $page_id = $this->Conditionality->nextPageRecursive($first_page['Page']['id'], $session);
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($q_id), redirecting to surveys/show/$page_id", LOG_DEBUG);
        $this->redirect(array('controller'=>'surveys', 'action'=>'show', $page_id));
    }

    /**
    * Note that this code is (sometimes?) not called before the 
    *   view code is, e.g. when surveys/show displays a page w/
    *   the substitution fxn "complete"
    *
    * Partial finalization: all data through the partial finalization step ("first stage" data) are reportable; survey session can be resumed, but answers for the first stage cannot be changed.
    *
    * Finalized: all data reportable; survey session cannot be resumed; answers cannot be changed
    *
    *
    * This is the sequence for the end of SurveySessions, and the various states (eg finalized) each step modifies. If something is missing along this sequence, the user will exited from the survey session. Note that there may be survey pages between each of these steps which do not affect finalization state.
        surveys/show/SKIPPED_QUESTIONS_PAGE (eg #120)
            has substitution fxn %(skippedQuestions)
                traverses/constructs skipped questions
                return $this->controller->render('skipped_questions');
                    "next" button links to surveys/complete/1
            if session is partially finalized, redirect to next|prev
        surveys/complete/1
            if SINGLE_STAGE_FINALIZATION, jump to surveys/complete/2
            else
                sets partial_finalization = true
                calculates instruments
                redirects to "/surveys/next_page/" . SKIPPED_QUESTIONS_PAGE
        surveys/show/COMPLETE_BTN_PAGE (eg #119)
            has substitution fxn %(Complete)
                return $this->controller->render('complete');
                    note that this render call will *not* cause the controller 
                        function surveys/complete to be called!
                    the complete view has a "Complete" button which links 
                        to surveys/complete/2
        surveys/complete/2
            set finished = true
            calculates instruments
            redirects to an instance-specific location
    *
    *
    */
    function complete($nth=1)
    {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); args: " . print_r(func_get_args(), true), LOG_DEBUG);

        if ($this->Session->check('calculatingSession' . $this->session["SurveySession"]['id'])){
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); calculatingSession, so bailing on this redundant call", LOG_DEBUG);
            return;
        }

        $this->Session->write('fromSkipped', false);
        $this->Session->write('calculatingSession' . $this->session["SurveySession"]['id'], 
                                $nth);

        // First finalization, but project is ! single_stage_finalization 
        if($this->session["SurveySession"]['appointment_id'] != null
            && (!isset($nth) || $nth == 1)
            && ($this->session['Project']['single_stage_finalization'] == 0))
            //&& ($this->session['Project']['single_stage_finalization'] === false))
        {
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth), first finalization, but project is ! single_stage_finalization; next is partial_finalization and then calculate_for_session", LOG_DEBUG);

            $this->SurveySession->partially_finalize($this->session);
            $this->Instruments->calculate_for_session($this->session_id);
            $this->Session->delete('calculatingSession' . $this->session["SurveySession"]['id']);
            $this->after_partial_finalization();

        } else { // completely finish SurveySession 
            $this->session["SurveySession"]["modified"] = $this->DhairDateTime->usersCurrentTimeStr();
            
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth); completely finishing SurveySession, next is " . get_class($this->SurveySession) . "->finish()", LOG_DEBUG);

            $this->SurveySession->finish($this->session);


        if (defined('EMAIL_STAFF_SESSION_FINISH')
            && EMAIL_STAFF_SESSION_FINISH 
            && $this->session['SurveySession']['type'] != ELECTIVE){
            
            $user = $this->User->find('first', 
                array('conditions' => array('User.id' => 
                            $this->session['SurveySession']['patient_id']),
                    'recursive' => 0,
                    'contain' => array('Clinic'),
                    'fields' => array('Clinic.patient_status_email', 
                                        'Clinic.support_email')
            ));

//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth); doing EMAIL_STAFF_SESSION_FINISH", LOG_DEBUG);

            if (isset($user['Clinic']['patient_status_email'])){
                $email = new CakeEmail();
                $email->from(array($user['Clinic']['support_email'] => SHORT_TITLE));
                $email->to($user['Clinic']['patient_status_email']); 
                $email->subject('Assessment complete for patient ' . 
                                    $this->session['SurveySession']['patient_id'] .
                                    ' (' . Router::url('/', true) . ')');
                
                $email->viewVars(array('session' => $this->session, 'url' => Router::url('/', true)));
                $email->emailFormat('html');
                $email->template(CProUtils::getInstanceSpecificEmailName('assesment_finished', 'html'));
                $email->send();
            }
        }//EMAIL_STAFF_SESSION_FINISH
            
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth); next is calculate_for_session()", LOG_DEBUG);
            
            $this->Instruments->calculate_for_session($this->session_id);
            $this->Session->delete('calculatingSession' . $this->session["SurveySession"]['id']);

            if (isset($this->session['Project']['finish_survey_txt']))
                $this->Session->setFlash(__($this->session['Project']['finish_survey_txt']));
            else
                $this->Session->setFlash(__('Thank you for finishing the report.'));
            
            $redirectUrl;
            $instanceComponentName = Inflector::humanize(INSTANCE_ID);
//            $this->log("surveys/complete($nth), here's instanceComponentName:$instanceComponentName; this->components: " . print_r($this->components, true), LOG_DEBUG);
            // eg 'Paintracker'
            if (array_key_exists($instanceComponentName, $this->components)
                && method_exists($this->{$instanceComponentName}, 
                                                        'getPostSurveyUrl')){
                $redirectUrl = 
                    $this->{$instanceComponentName}->getPostSurveyUrl(
                                                        $this->session);
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth), instance specific getPostSurveyUrl so redirecting to that ($redirectUrl)", LOG_DEBUG);
            }
            elseif($this->session["SurveySession"]['appointment_id'] != null
                    && isset($this->session['Project']['complete_btn_page'])){
                $redirectUrl = '/surveys/next_page/'
                    . $this->session['Project']['complete_btn_page'];
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth), session has appt and there is a complete_btn_page so redirecting to /surveys/next_page/(complete_btn_page=" . $this->session['Project']['complete_btn_page'] . ")" , LOG_DEBUG);
            }
            else {
                $redirectUrl = $this->getSurveyHome($this->session['Project']);
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "($nth), redirectUrl set to getSurveyHome ($redirectUrl)", LOG_DEBUG);
            }
//            $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); next is Session->write SURVEY_SESSION_JUST_FINISHED and then redirect to redirectUrl:$redirectUrl", LOG_DEBUG);

            $this->Session->write(SURVEY_SESSION_JUST_FINISHED, true);

            $this->redirect($redirectUrl);
        } // completely finish SurveySession 
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); exiting", LOG_DEBUG);
    }// function complete($nth=1)


    private function after_partial_finalization() {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), redirecting to /surveys/next_page/" . $this->session['Project']['skipped_questions_page'], LOG_DEBUG);
        # set session or view vars to distinguish this?
        $this->redirect("/surveys/next_page/" 
            . $this->session['Project']['skipped_questions_page']);
    }

    private
    function _percentFinished($project_id, $qr_id, $page_id) {
        $project = $this->Project->findById($project_id);
        $qrs = $project["Questionnaire"];
        $this_qr_position = 0;
        foreach($qrs as $id => $qr) {
            if ($qr["id"] == $qr_id) {
                $this_qr_position = $id;
                break;
            }
        }

        $qr = $this->Questionnaire->findById($qr_id);
        $pages = $qr["Page"];
        $this_p_position = 0;
        foreach($pages as $id => $page) {
            if ($page["id"] == $page_id) {
                $this_p_position = $id;
            }
        }

        $total_qrs = count($qrs);
        $qrs_finished = (float)$this_qr_position / $total_qrs;
        $ps_finished = (float)$this_p_position / count($pages);
        $percent_finished = $qrs_finished + $ps_finished / $total_qrs;
        $this->set('donePercent', $percent_finished);
    }

    function overview(){
        $this->layout = 'default'; 
        $this->TabFilter->selected_tab("Editor");

        $projects = $this->Project->find('all');
        $this->set('projects', $projects);
    }

    function edit_project($project_id){
        $this->layout = 'default'; 
        $this->TabFilter->selected_tab("Editor");
        $this->request->data = $this->Project->findById($project_id);
        //$this->set("project", $project);
    }

    /**
     *
     */
    function reopen_test_session($sessionId){

        $session = $this->SurveySession->findById($sessionId);

        if (empty($session['Patient']['test_flag'])){
            $this->Session->setFlash("This is not a test patient, therefore the session cannot be re-opened.");
            $this->redirect("/patients/edit/" . $session['Patient']['id']);
        }

        $session['SurveySession']['partial_finalization'] = 0;
        $session['SurveySession']['finished'] = 0;
        $this->SurveySession->save($session["SurveySession"]);
        $this->Session->setFlash("Session re-opened.");
        $this->redirect("/patients/edit/" . $session['Patient']['id']);
    }


    /**
     *
     */
    function finish_test_session($sessionId){

        $session = $this->SurveySession->findById($sessionId);

        if (empty($session['Patient']['test_flag'])){
            $this->Session->setFlash("This is not a test patient, therefore you cannot closed/finish the survey session.");
            $this->redirect("/patients/edit/" . $session['Patient']['id']);
        }

        $session['SurveySession']['partial_finalization'] = 1;
        $session['SurveySession']['finished'] = 1;
        $this->SurveySession->save($session["SurveySession"]);
        $this->Instruments->calculate_for_session($sessionId);
        $this->Session->setFlash("Session now closed/finished.");
        $this->redirect("/patients/edit/" . $session['Patient']['id']);
    }


    /**
     * Copies a Questionnaire and it's Pages, Questions, and Options
     * // TODO copy conditions for which any of the above are targets.
     */
    function copy_questionnaire($qnrId){

        $qnr = $this->Questionnaire->find('first', array('conditions' => array('id' => $qnrId)));
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); qnr: " . print_r($qnr, true), LOG_DEBUG);

        $qnrData = array();
        $qnrData['Questionnaire'] = $qnr['Questionnaire'];
        unset($qnrData['Questionnaire']['id']);

        $this->Questionnaire->create();
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); qnrData before save: " . print_r($qnrData, true), LOG_DEBUG);
        $this->Questionnaire->save($qnrData);
        $newQnrId = $this->Questionnaire->id; 
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); new qnr ID: $newQnrId", LOG_DEBUG);

        // TODO copy scales for this, if any     
        // TODO copy subscales for this, if any     
 
        foreach($qnr['Page'] as $page){
            $sourcePageId = $page['id'];

            $pageData = array();
            $pageData['Page'] = $page;
            unset($pageData['Page']['id']);
            $pageData['Page']['questionnaire_id'] = $newQnrId;
            
            $this->Page->create();
//            //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); pageData before save: " . print_r($pageData, true), LOG_DEBUG);
            $this->Page->save($pageData);
            $newPageId = $this->Page->id;
//            //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); newPageId: $newPageId", LOG_DEBUG);

            $sourceQuestions = $this->Question->find('all', 
                        array('conditions' => array('page_id' => $sourcePageId)));
//            //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); all sourceQuestions on sourcePageId $sourcePageId: " . print_r($sourceQuestions, true), LOG_DEBUG);

            //find and copy all questions w/ $sourcePageId
            foreach($sourceQuestions as $sourceQuestion){
                $sourceQuestionId = $sourceQuestion['Question']['id'];

                $questionData = array();
                $questionData['Question'] = $sourceQuestion['Question'];
                unset($questionData['Question']['id']);
                $questionData['Question']['page_id'] = $newPageId;
            
                $this->Question->create();
//                //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); questionData before save: " . print_r($questionData, true), LOG_DEBUG);
                $this->Question->save($questionData);
                $newQuestionId = $this->Question->id;
//                //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); newQuestionId: $newQuestionId", LOG_DEBUG);
        
                // TODO copy items for this, if any     

                // find and copy all options w/ $sourceQuestionId
                foreach($sourceQuestion['Option'] as $sourceOption){
                    $sourceOptionId = $sourceOption['id'];

                    $optionData = array();
                    $optionData['Option'] = $sourceOption;
                    unset($optionData['Option']['id']);
                    $optionData['Option']['question_id'] = $newQuestionId;
                
                    $this->Option->create();
//                    //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); optionData before save: " . print_r($optionData, true), LOG_DEBUG);
                    $this->Option->save($optionData);
                    $newOptionId = $this->Option->id;
//                    //$this->log(__CLASS__ . "->" . __FUNCTION__ . "(); newOptionId: $newOptionId", LOG_DEBUG);

                }// foreach($sourceQuestion['Option'] as $sourceOption){
            }//foreach($qnr['Question'] as $question){
        }//foreach($qnr['Page'] as $page){
    }// function copy_questionnaire($qnrId){


    function edit($id=null, $table=null, $text=null){
        // $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        // Default response array
        $result = array(
            'ok' => false,
            // 'debug' => $this->data,
            'message' => 'response could not be saved',
        );
        
        $data = $this->data;
        $this->viewVars = &$result;

        // If no table is passed, use the first array key that isn't "AppController"
        if (!$table){
            $dataKeys = array_keys($data);

            $index = array_search('AppController', $dataKeys);
            if ($index!==false){
                unset($dataKeys[$index]);
                $temp = array_values($dataKeys);
                $table = array_shift($temp);
            }
        }

        if (!empty($data) and $table){
            switch ($table) {
                case 'Questionnaire':
                    $model = $this->Questionnaire; break;
                case 'Page':
                    $model = $this->Page; break;
                case 'Question':
                    $model = $this->Question; break;
                case 'Option':
                    $model = $this->Option; break;
                default:
                    $model = null;
            }
        }

        if ($model and $model->findById($id)){

            // Clean up submitted HTML
            $tidy = new TidyBetterMessages();
            foreach ($data[$table] as $attribute => &$value){
                $tidy->parseString($value, Configure::read('tidy.config'), 'utf8');
                $tidy->diagnose();
                $value = $tidy->value;
            }

            // Add HTML tidy messages to response
            if ($tidy->getMessages(true, array('warning', 'error')))
                $result += array('tidy'=>$tidy->getMessages(true, array('warning', 'error')));

            // Check if HTML Tidy had any errors
            if (isset($result['tidy']['error'])){
                $this->response->statusCode(400);
                $result['message'] = 'HTML Tidy error';
            }

            else if (
                // Add the id to the array so Cake updates instead of inserts
                $model->save(array(
                    $table =>($data[$table] + array('id'=>$id))
                ))
            ){
                $result['ok'] = true;
                // $result['debug'] = $model->findById($id);
                // $result['debug'] = array($table =>($data[$table] + array('id'=>$id)));
                $result['message'] = 'record updated';
            }
            else {
                $this->response->statusCode(403);
                $result['message'] = "unable to save record with table:$table id:$id";
            }
        }
        else {
            $this->response->statusCode(404);
            $result['message'] = "could not find existing record with table:$table id:$id";
        }
        $this->set(array('_serialize' => array_keys($result)));
    }

    /**
     *
     */
    function getSurveyHome($project = null){
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(project), w/ project: " . print_r($project, true), LOG_DEBUG); 

        if ($this->is_staff){
            return '/patients/edit/' . $this->patient['Patient']['id'];
        }

        elseif (isset($project) && !empty($project['contextLimiter'])){
            return '/' . $project['contextLimiter'];
        }

        elseif (in_array(array('controller'=>'surveys', 'action'=>'index'), Configure::read('tabControllerActionMap'))) {
            return '/surveys/index';
        }
        else return '/users/index';
    }

}
?>
