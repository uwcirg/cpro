<?php
/** 
 * Substitution component
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    * 
 * Initialized with a controller, accepts inputs and returns them
 * with specially-formatted substitution strings (e.g. "%(FIRSTNAME)")
 * replaced by the appropriate value for the session. 
 *
 * Substitution strings in the format %([function name] arg1 arg2)
 *      with [SPACE] to be replaced by ' ' when the args are evaluated
 * Substitution string examples:
 *  %(FIRSTNAME)
 *  %(RELATIVE_DATETIME 3[SPACE]months[SPACE]ago)
 *
 * All functions are implemented in the Substitutions class. To add more,
 * add the functions to that class, or subclass Substitutions and then initialize
 * the new class as $this->substitutions in the startup method. All functions
 * will only receive the current session id.
*/
class SubstitutionComponent extends Component
{
  var $session_id = null;
  var $controller = null;
  
  function startup(Controller $controller)
  {
      $this->controller = $controller;
      $this->substitutions = new Substitutions($controller, $this);
  }
  
  function substitute($string)
  {
    return preg_replace_callback("/%\((.*?)?\)/", array("SubstitutionComponent", "replace"), $string);
  }
  
  function replace($match)
  {

    $fxnAndArgs = explode(' ', $match[1]);
    $fxnName = $fxnAndArgs[0];
    $fxnAndArgs[0] = $this->session_id;
    $fxnAndArgs = str_replace('[SPACE]', ' ', $fxnAndArgs);

    if (is_callable(array($this->substitutions, $fxnName))){
//      $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); calling $fxnName w/ args " . print_r($fxnAndArgs, true), LOG_DEBUG);

        return call_user_func_array(array($this->substitutions, $fxnName), $fxnAndArgs);
    }
    else return "<!--substitution fxn '" . $fxnName . "' not callable/implemented-->";

  }
  
    /**
    *
    */
    function for_page_in_session($page, $session_id)
    {
        $this->session_id = $session_id;
    
        $page["Page"]["BodyText"] = 
                        $this->substitute($page["Page"]["BodyText"]);
        $page["Page"]["Header"] = 
                        $this->substitute($page["Page"]["Header"]);
    
        return $page;
    }  
  
    /**
    *
    */
    function for_questions_in_session($questions, $session_id)
    {
        //$this->log("for_questions_in_session, here are the questions: " . print_r($questions, true), LOG_DEBUG);
        $this->session_id = $session_id;
        foreach($questions as &$question) {
            $question["Question"]["ShortTitle"] = 
                    $this->substitute($question["Question"]["ShortTitle"]);
            $question["Question"]["BodyText"]   = 
                    $this->substitute($question["Question"]["BodyText"]);
            foreach($question["Option"] as &$option){
                $option['BodyText'] = $this->substitute($option['BodyText']);
            }
        }
        return $questions;
    }


  function for_text_in_skipped($questions, $session_id) 
  {
//      $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); args: " . print_r(func_get_args(), true), LOG_DEBUG);

      $this->session_id = $session_id;
      foreach($questions as &$question) {
          $question["text"] = $this->substitute($question["text"]);
      }

//      $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); returning questions: " . print_r($questions, true), LOG_DEBUG);
      return $questions;
  }
}

class Substitutions extends Object
{  
  function Substitutions(&$controller, $substitute)
  {
      $this->controller = $controller;
      $this->substitute = $substitute;
  }
  // Returns relative time between now and last session.
  // TODO: Get rules for displaying time difference and which session to use.
  // Need to clarify which sessions are finished, etc.
  function PREVSESSION($session_id) {
    return "[time from last session]";
  }
  
  function PENULTIMATESESSION($session_id)
  {
    return "[time from penultimate session]";
  } 
  
  function ANTIPENULTIMATESESSION($session_id)
  {
    return "[time from antipenultimate session]";
  }
  
  // TODO: should print "back" if there is a previous session for this user
  function getReturnString($session_id)
  {
    return "";
  }
  
  //TODO: Should print patient's first name
  function FIRSTNAME($session_id)
  {
    $session = $this->controller->SurveySession->findById($session_id);
        
    return $session["User"]["first_name"];
  }
  
  function LASTNAME($session_id)
  {
    $session = $this->controller->SurveySession->findById($session_id);

    return $session["User"]["last_name"];
  }


    // Substitution function for rendering the body diagram
    // Instantiates a View to be able to render form a view file to a variable and return it
    // Uses the Views/Surveys/body_diagram.ctp view
    // Uses an unmodified SVG that is connected to survey questions/options/answers through set of javascript arrays constructed here

    /* Requires:
        a question with BodyText describing type of pain
        options for BodyText set to location of pain
        SVG path with id corresponding with above option BodyText
    */
    function BODY_DIAGRAM($session_id){

        // Question attribute where symptom name/text is stored
        $symptom_attribute = 'AncillaryText';

        // Option attribute where body part name/text is stored
        $bodypart_attribute = 'AncillaryText';

        // Map body parts to option_ids so we can correctly report responses to the backend
        //body_part_name:{{question_id:option_id}}
        //                 (symptom):(body part)
        $bodypart_map = array();

        // Mapping of option_id to body part name
        $option_map = array();

        // Map of symptom/body part location from previous answers
        $answers = array();

        // Map of symptom (ie pain) to question id
        $symptom_map = array();

        $this->log('questions from controller: '.print_r($this->controller->viewVars['questions'], true), LOG_DEBUG);
        foreach ($this->controller->viewVars['questions'] as $question){
            $symptom_name = $question['Question'][$symptom_attribute];
            $question_id = (int)$question['Question']['id'];

            $symptom_map[$symptom_name] = $question_id;

            foreach ($question['Option'] as $option){
                $bodypart_map[$option[$bodypart_attribute]][$question_id] = (int)$option['id'];
                $option_map[$option['id']] = $option[$bodypart_attribute];
            }

            if (!$question['Answer'])
                continue;
            else if (is_int($question['Answer']))
                $answers[$option_map[(int)$question['Answer']]] = array($symptom_name);
            else {
                foreach (array_keys($question['Answer']) as $answer){
                    if (array_key_exists($question_id, $answers))
                        array_push(
                            $option_map[$answers[(int)$answer]],
                            $symptom_name
                        );
                    else
                        $answers[$option_map[(int)$answer]] = array($symptom_name);
                }
            }
        }

        // Set up new view that won't enter the ClassRegistry
        $view = new View($this->controller, false);

        $view->set(compact('symptom_map', 'bodypart_map', 'answers'));
        return $view->render('body_diagram', 'blank');
    }


    function PATIENT_USERNAME($session_id){
        return $this->controller->patient['User']['username'];
    }

    function PATIENT_PRIMARY_RANDOMIZATION_DATE($session_id){
        $timezone = new DateTimeZone($this->controller->User->getTimeZone($this->controller->patient['Patient']['id']));
        $localDate = new DateTime($this->controller->User->gmtToLocal(
            $this->controller->patient['Patient']['primary_randomization_date'],
            $timezone->getName()
        ));
        return $localDate->format('m/d/y');
    }

    function CLINIC_SUPPORT_NAME($session_id){
        return $this->controller->user['Clinic']['support_name'];
    }

    function CLINIC_IRB_CONTACT($session_id){
        return $this->controller->user['Clinic']['irb_contact'];
    }

    function STAFF_USERNAME($session_id){
        if ($this->controller->is_staff)
            return $this->controller->user['User']['username'];
    }

    function CURRENT_DATE($session_id){
        $timezone = new DateTimeZone($this->controller->User->getTimeZone($this->controller->patient['Patient']['id']));
        $localDate = new DateTime($this->controller->User->gmtToLocal(
            'now',
            $timezone->getName()
        ));
        return $localDate->format('m/d/y');
    }

    function INTERVAL_WEEK($session_id){
        $week = $this->controller->Patient->getTrialWeek($this->controller->patient);
        return "Week $week";
    }

    function ACTIVITY_LOG($session_id){

        $lastSession = $this->controller->SurveySession->find('first', array(
            'conditions' => array(
                'SurveySession.patient_id' =>
                    $this->controller->patient['Patient']['id'],
                'SurveySession.id !=' =>
                    $session_id,
            ),
            'recursive' => -1,
            'order' => "SurveySession.id DESC"
        ));
        if ($lastSession){
            $answer = trim($this->controller->Answer->analysisValueForSessionAndQuestion($lastSession['SurveySession']['id'], 1043));
            if ($answer)
                return "<br /><br />The imporant activity you listed from your last assessment was:<br /> $answer<br />";
        }
        return '';
    }

    /**
     * @param relative_time php relative datetime format eg '3 months ago'
     */
    function RELATIVE_DATETIME($session_id, $relative_time = 'now'){
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($session_id, $relative_time)", LOG_DEBUG);
        $timezone = new DateTimeZone($this->controller->User->getTimeZone($this->controller->patient['Patient']['id']));
        $localDate = new DateTime($this->controller->User->gmtToLocal(
            $relative_time,
            $timezone->getName()
        ));
        return $localDate->format('m/d/y');
    }

    /*
    * P3P-specific substitution function
    * Only shows study coordinator info on Baseline Clinical Data assessment
    */
    function STUDY_COORDINATOR_INFO($session_id){
        $currentSession = array_shift(Hash::extract(
            $this->controller->patient,
            "SurveySession.{n}[id=$session_id]"
        ));
        if ($currentSession and $currentSession['project_id'] == 7){

            $randomizationDate = $this->PATIENT_PRIMARY_RANDOMIZATION_DATE($session_id);

            return "<strong>Study coordinator:</strong> Check each criterion below with the data above. You are confirming that the patient was eligible to participate in the P3P II trial as of the date he was randomized ($randomizationDate). Your electronic signature will confirm final eligibility.";
        }

    }

  function insertPrevArrow($session_id)
  {
    return "<button class='btn' title='Previous Page'>
                <i class='icon-chevron-left'></i> Previous</button>" ;
  }
  
  function insertNextArrow($session_id)
  {
    return "<button class='btn btn-primary btn-large' title='Next Page'>Next
                <i class='icon-chevron-right icon-white'></i></button>" ;
  }

  function T2DATE($session_id) 
  {
      $time = $this->controller->patient["Patient"]["t2"];
      # FIXME: wait for the authoritative way to deal with timezones
      return strftime("%B %e", strtotime($time));
  }

  function two_things($session_id)
  {
      $a = $this->T2A($session_id);
      $b = $this->T2B($session_id);
      if($a && $b) {
          return $this->substitute->substitute(
              "In your Timepoint 2 report on %(T2DATE), you said %(T2A) and %(T2B) were the two problems bothering you most.  This may have changed for you, but we would like to ask you about your experience with %(T2A) and %(T2B) since Timepoint 2.");
      } elseif ($a || $b) {
          return $this->substitute->substitute(
              "In your Timepoint 2 report on %(T2DATE), you said %(T2A) was the problem bothering you most. This may have changed for you, but we would like to ask you about your experience with %(T2A) since Timepoint 2.");
      } else {
          return "";
      }
  }

  function T2A($session_id)
  {
      $patient = $this->controller->patient;
      $subscale_id = $patient["Patient"]["t2a_subscale_id"];
      if(!$subscale_id) {
          return false;
      }
      $subscale = $this->controller->Subscale->findById($subscale_id);
      return $subscale["Subscale"]["name"];
  }

  function T2B($session_id)
  {
      $patient = $this->controller->patient;
      $subscale_id = $patient["Patient"]["t2b_subscale_id"];
      if(!$subscale_id) {
          return false;
      }
      $subscale = $this->controller->Subscale->findById($subscale_id);
      return $subscale["Subscale"]["name"];
  }

  function TEACHING_TIPS($session_id) {
//      $this->log("TEACHING_TIPS($session_id), just entered", LOG_DEBUG);
      $subscales = $this->controller->Session->read('teaching_subscales');

//      $this->log("TEACHING_TIPS, here's subscales from session: " . print_r($subscales, true), LOG_DEBUG);

      if($subscales === array()) {
          $this->controller->Session->write('teaching_subscales', NULL);
          $this->controller->redirect('index');
      } elseif ($subscales == false) {
          $subscales = $this->set_teaching_subscales($session_id);

          $this->controller->Session->write('titlesOfRedundantTips', array());
      }
      $subscale_id = array_shift($subscales);
      $subscale = $this->controller->Subscale->findById($subscale_id);
      $this->controller->Session->write('teaching_subscales', $subscales);

      $this->controller->loadModel("Tip");
      // note this next fxn's subscale param is a reference
      $this->controller->Tip->forSubscaleAndPatient(
                            $subscale["Subscale"], $this->controller->authd_user_id);

//      $this->log('TEACHING_TIPS(), just added tip to subscale array, heres subscale: ' . print_r($subscale, true), LOG_DEBUG);
      
      if (!isset($subscale['Subscale']['TeachingTip'])){
//        $this->log('TEACHING_TIPS(), no tip found for subscale id ' . $subscale_id, LOG_DEBUG);
      }

      //FIXME if this tip has a title and it has been presented before, move on to the next one.
      if (isset($subscale['Subscale']["TeachingTip"]["title"])){

        $title = $subscale['Subscale']["TeachingTip"]["title"];

        $titlesOfRedundantTips = $this->controller->Session->read('titlesOfRedundantTips');
//        $this->log('TEACHING_TIPS(), just read titlesOfRedundantTips; its: ' . print_r($titlesOfRedundantTips, true), LOG_DEBUG);
        if (in_array($title, $titlesOfRedundantTips)){
//          $this->log("TEACHING_TIPS(), this title ($title) has already been displayed, so moving on to the next via redirect to show/" . TEACHING_TIPS_PAGE, LOG_DEBUG);
          $this->controller->redirect('show/' . TEACHING_TIPS_PAGE);
        }
        else {
//            $this->log('TEACHING_TIPS(), this title has not yet been displayed, so showing it now: ' . $title, LOG_DEBUG);
            $titlesOfRedundantTips[] = $title;
            $this->controller->Session->write('titlesOfRedundantTips',
                                    $titlesOfRedundantTips);
        }
      }
//      $this->log('TEACHING_TIPS(), showing tip for subscale ' . $subscale_id . '(' . $subscale['Subscale']['name'] . ')', LOG_DEBUG);

      $this->controller->set('teaching_tip', $subscale);
      //$this->log("TEACHING_TIPS, just set teaching_tip for view: " . print_r($subscale, true), LOG_DEBUG);

//      $this->log('TEACHING_TIPS(), next is render(teaching_tips)', LOG_DEBUG);
      $this->controller->render('teaching_tips');
  } // function TEACHING_TIPS($session_id) {

  /**
    * @return a simple array of subscale id's
    *
    */
  private function set_teaching_subscales($session_id) {
      $critical_subscales = 
        $this->controller->SessionSubscale->criticalForSession($session_id);
      $critical_subscale_ids = 
        array_map(
            create_function('$subscale', 
                            'return $subscale["Subscale"]["id"];'), 
                            $critical_subscales);
      shuffle($critical_subscale_ids);

      $priority_options = 
        $this->controller->Answer->forSessionAndQuestion(
                                                    $session_id, RANKING_Q);
      $priority_subscales = array();
      foreach($priority_options as $option_id => $comboTxtIfAny) {
          $option = $this->controller->Option->findById($option_id);
          $priority_subscales[] = $option["Option"]["AnalysisValue"];
      }

      $teaching_subscales = 
            array_unique(
                array_merge($priority_subscales, $critical_subscale_ids));
      $this->controller->Session->write('teaching_subscales', 
                                                $teaching_subscales);

      //$this->log("set_teaching_subscales, here is teaching_subscales: " . print_r($teaching_subscales, true), LOG_DEBUG);

      return $teaching_subscales;
  }

  /** the priority ranking substitution actually takes control of
   * rendering the action, sets controller variables as appropriate,
   * then renders the whole 'show' view */
  function PRIORITYRANKING($session_id)
  {
//      $this->log(__CLASS__ . "->" . __FUNCTION__ . "($session_id)", LOG_DEBUG);
      $this->controller->Instruments->calculate_for_session($session_id);
      $options = $this->controller->viewVars["questions"][0]["Option"];

      $critical_subscales = 
            $this->controller->SessionSubscale->criticalForSession($session_id);
      $critical_subscale_ids = 
            array_map(create_function(
                        '$subscale', 'return $subscale["Subscale"]["id"];'), 
                        $critical_subscales);
//      $this->log(__CLASS__ . "->" . __FUNCTION__ . "($session_id); critical_subscale_ids: " . print_r($critical_subscale_ids, true), LOG_DEBUG);

      $critical_options = array();

      $optionTexts = array();

      foreach($options as $option) {
          if(in_array($option["AnalysisValue"], $critical_subscale_ids)) {

              $optionText = $option['BodyText']; // eg "Pain", "Fatigue"
              if (in_array($optionText, $optionTexts)){
                // don't allow options w/ duplicate BodyText
                continue;
              }
              $optionTexts[] = $optionText;

              $option['class'] = 'pick2';
              $critical_options[] = $option;
          }
      }
      shuffle($critical_options);

      $this->controller->viewVars["page"]["BodyText"] = "";
      $this->controller->viewVars["questions"][0]["Option"] = $critical_options;

      $this->controller->render('show');
  }


  function complete($session_id) {
      // note that the render call below will *not* cause the controller function surveys/complete to be called!
      return $this->controller->render('complete');
  }

  /** skippedQuestions
   * Note: calling render prevents the normal view from
   * rendering, so make sure to show all the elements
   */ 
  function skippedQuestions($session_id)
  {
      $skipped_qrs = $this->controller->TraverseSkipped->run();
      foreach($skipped_qrs as &$skipped_qr) {
          $skipped_qr["Questions"] 
              = $this->controller->Substitution->for_text_in_skipped(
                                                $skipped_qr["Questions"], 
                                                $session_id);
      }
      $this->controller->set('skipped', $skipped_qrs);
      return $this->controller->render('skipped_questions');
  }
  
  function allQuestions($session_id)
  {
    return "[We need to discuss how to list all questions, and whether this is even appearing]";
  }

  function calculateScales($session_id) {
      $this->controller->SurveySession->calculateScales($session_id);
  }  
  // Should this really be a substitution function?
  function doRandomization($session_id)
  {
    return "<strong>Did randomization</strong>";
  }
 
  function getOralChemoMedsAndList($session_id){
    return $this->getOralChemoMeds($session_id, 'and');
  }
 
  function getOralChemoMeds($session_id, $separator = 'or')
  {
    $array = $this->controller->Answer->analysisValueForSessionAndQuestion(
                $session_id, 1001);
    //$this->controller->DhairLogging->logArrayContents($array, "answerArrayForQ1001");
    if (empty($array)){
        return "oral chemotherapy medications";
    }

    $str = '';
    $sep = '';
    $arraySize = count($array);
    $tally = 0;


    foreach ($array as $optionId => $comboText){
        $option = $this->controller->Option->findById($optionId);

        if ($arraySize == 1) return $option['Option']['BodyText'];

        if ($tally == $arraySize - 1) $sep .= $separator . ' ';
        $str .= $sep . $option['Option']['BodyText'];
        $sep = ', ';
        $tally += 1;
    }
    return $str;
  }
 
  function studyInfoUrl($session_id){

    return "332";

  }

    function variableResponse_familymember($sessionId){
        $response = $this->controller->Answer->forSessionAndQuestion(
            $sessionId,
            1514
        );

        if ($response and $response == 5046)
            return 'any other family member\'s';

        return 'a family member\'s';

    }

    function variableTense_wouldlikethetreatmentdecisiontobemade($sessionID){
        //if ($sessionRecord['ProjectID'] == 1){
            return "";
        /**}
        else {
            return " (or how you made the decision)";
        }*/
    }

    function variableTense_prefertomake($sessionID){
        //if ($sessionRecord['ProjectID'] == 1){
            return "";
        /**}
        else {
            return " (I made)";
        }*/
    }

    function variableTense_IpreferthatmydoctorandIshare($sessionID){
        //if ($sessionRecord['ProjectID'] == 1){
            return "";
        /**}
        else {
            return " (we shared)";
        }*/
    }

    function variableTense_Ipreferthatmydoctormakes($sessionID){
        //if ($sessionRecord['ProjectID'] == 1){
            return "";
        /*}
        else {
            return " (my doctor made)";
        }*/
    }

    function variableTense_Iprefertoleave($sessionID){
        //if ($sessionRecord['ProjectID'] == 1){
            return "";
        /**}
        else {
            return " (I left)";
        }*/
    }

    function variableTense_ed($sessionID){
        //if ($sessionRecord['ProjectID'] == 1){
            return "";
        /**}
        else {
            return "(ed)";
        }*/
    }



 
}
