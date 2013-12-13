<?php
  /** 
    * Answer class
    *
    * A single answer or click of an option for a survey.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * See note below at afterFind:  no timezone modification is done
    * to 'modified' field due to the overhead of getting the proper
    * timezone; saving is easy because it's just the current time in GMT,
    * but converting to local time on find is another matter.  
    * Because currently no one is using
    * the modified fields (except to compare to other modified fields for
    * the same user), we're letting this slide. --- gsbarnes
*/
class Answer extends AppModel {
    var $belongsTo = array('Question', 'Option', 'SurveySession');
    var $bindModels = true;

  /** Return analsysis value for a question
   *  NULL for absolutely no answer
   *  a String for textbox questions
   *  an Int analysis value for radio buttons
   *
   *  checkboxes:
   *    Returns an array of all options for which the most recent
   *    state is active/selected. May return an empty array.
   *    key = option ID; value = text from combo, or null if non-combo
   * 
   *  other controls undefined
   */
  function analysisValueForSessionAndQuestion($session_id, $question_id) {
//      $this->log(__CLASS__ . "=>" . __FUNCTION__ . "(session $session_id, question $question_id)", LOG_DEBUG);
      $answer_text = 
        $this->forSessionAndQuestion($session_id, $question_id);
      $answer_id = intval($answer_text);

      if ($answer_text === null) {
          // no answer
//          $this->log('analysisValueForSessionAndQuestion, answer_text === null; returning null', LOG_DEBUG);
		  return null;
      } elseif(is_string($answer_text)) {
//          $this->log('analysisValueForSessionAndQuestion, answer_text is_string; returning ' . $answer_text, LOG_DEBUG);
          // text or other non-analyzable value
          return $answer_text;
      } elseif(is_array($answer_text)) {
//          $this->log('analysisValueForSessionAndQuestion, answer_text is_array; returning ' . $answer_text, LOG_DEBUG);
          return $answer_text;
      }

	  $option = $this->Option->findById($answer_id);

      if(isset($option["Option"]["AnalysisValue"])) {
      // analysis value of -1 treated as question skipped (eg "not applicable")
        if ($option["Option"]["AnalysisValue"] == -1) {
//            $this->log('analysisValueForSessionAndQuestion, option analysis value == -1; returning null', LOG_DEBUG);
		    return null;
        }
        else{
//            $this->log('analysisValueForSessionAndQuestion, option analysis value != -1; returning ' . $option["Option"]["AnalysisValue"], LOG_DEBUG);
            return $option["Option"]["AnalysisValue"];
        }
      } 
      else {
//          $this->log('analysisValueForSessionAndQuestion, option analysis value not set; returning sequence(=' . $option["Option"]["Sequence"] . ')', LOG_DEBUG);
		  return $option["Option"]["Sequence"];
	  }
  }

    /**
     * Works for radio and combo-radio, at least...
     */
    function bodyTextForAnswerToQuestion($session_id, $question_id){
        $answer
            = $this->forSessionAndQuestion($session_id, $question_id);
        if (isset($answer) 
            && is_int($answer)){ // would be text for combo-radio, not int 
                $answerRecord = $this->Option->findById($answer);
                if ($answerRecord) // ignore some numerical combo responses, at least. Prob good enough, but FIXME at some point.
                    $answer = $answerRecord['Option']['BodyText'];
        }
        return $answer;
    }

/*
*   Non-combo radio buttons:
*       return option id as int
*   Combo radio buttons:
*       return Answer.value (the text from the combo field) 
*   Checkboxes:
*       Returns an array of all options for which the most recent
*       state is active/selected. May return an empty array.
*       key = option ID; value = text from combo, or null if non-combo
*   Others: 
*       return option id as int, text as string or NULL;
*/
    function forSessionAndQuestion($session_id, $question_id, $iteration=0){
        // Answer depends on the type of options for this question
        $options = $this->Option->for_question($question_id);
        if(isset($options) && $options && isset($options[0]) && $options[0]) {
            switch ($options[0]['Option']['OptionType']) {
            case 'radio':
            case 'combo-radio':
                $answer = $this->find('first', array(
                    'conditions' => array(
                        "Answer.survey_session_id = $session_id",
                        "Answer.question_id = $question_id",
                        'Answer.iteration' => $iteration,
                    ),
                    'fields' => array(
                        'option_id',
                        'question_id',
                        'state',
                        'value',
                        'survey_session_id'
                    ),
                    'order' => 'Answer.id DESC'
                ));
                if (!empty($answer) && $answer['Answer']['state'] == 'true'){
                    //$this->log(__CLASS . '->' . __FUNCTION__ . '(...), radio answer true, returning int option ID ' . intval($answer["Answer"]["option_id"]), LOG_DEBUG);
                    if ($answer['Answer']['value'] == null){
                        //$this->log('answer value == null, returning true', LOG_DEBUG);
                        return intval($answer['Answer']['option_id']);
                    }
                    else {
                        //$this->log('answer value != null, returning value', LOG_DEBUG);
                        return $answer['Answer']['value']; // only for combos
                    }
                }
                else{
                    //$this->log(__CLASS . '->' . __FUNCTION__ . '(...), radio option (' . $answer["Answer"]["option_id"] . ') answer false, returning null', LOG_DEBUG);
                    return NULL;
                }
            case 'checkbox':
            case 'combo-check':
                return $this->for_checkbox_options_and_session($question_id, 
                                    $options, $session_id, $iteration);
            case 'image':
                return $this->for_image($question_id, $session_id);
            case 'textbox':
            case 'text':
                return $this->for_textbox($question_id, $session_id, $iteration);
            case 'year':
            case 'month':
            case 'day':
                return $this->for_date($question_id, $options, 
                                    $session_id, $iteration);            

            default:
                return NULL;
            }
        }
        return null;
    }// function forSessionAndQuestion(...)

    // Return a string which is either the answer in the textbox
    // or else empty.
    function for_textbox($question_id, $session_id, $iteration=0){
        $answer = $this->find('first', array(
            'conditions' => array(
                "Answer.survey_session_id = $session_id",
                "Answer.question_id = $question_id",
                'Answer.iteration' => $iteration
            ),
            'fields' => array(
                'body_text',
                'survey_session_id',
                'question_id'
            ),
            'order' => 'Answer.id DESC'
        ));
        if ($answer)
            return $answer["Answer"]["body_text"];

        return "";
    }


    /**
     * Helper function called from $this->forSessionAndQuestion()
     * Finds previously given answer for a question
     * @param int $question_id id of the question to find the answer for
     * @param int $session_id id of the survey session
     * @return array a CakePHP data array containing the last answers for a given question
     */
    function for_image($question_id, $session_id){
        $answer = $this->find('all', array(
            'conditions' => array(
                'Answer.survey_session_id' => $session_id,
                'Answer.question_id' => $question_id,
                // 'Answer.iteration' => $iteration
            ),
            'order' => 'Answer.id DESC'
        ));
        return $answer;
    }

/*
*   Returns an array of all options for which the most recent
*   state is active/selected. May return an empty array.
*   key = option ID; value = text from combo, or null if non-combo
*/
    function for_checkbox_options_and_session($question_id, $options, 
                                                $session_id, $iteration=0){
        $option_ids = array();
        if(isset($options[0]['Option'])) {
            foreach($options as &$option)
                $option_ids[] = $option['Option']['id'];
        }

        $answers = array();
        foreach($option_ids as $option_id) {
            $answer = $this->for_option_w_possible_value($option_id, $session_id, $iteration);
            if ($answer){
                if ($answer === true) $answer = null;
                    $answers[$option_id] = $answer;
            }
        }
        return $answers;
    }


/*
*   Returns an array of all options for which the most recent
*   state is active/selected. May return an empty array.
*   key = option ID; value = text from combo, or null if non-combo
*/
    function for_date($question_id, $options, $session_id, $iteration=0){
        $option_ids = array();
        if(isset($options[0]['Option'])) {
            foreach($options as &$option)
                $option_ids[] = $option['Option']['id'];
        }

        $answers = array();
        foreach($option_ids as $option_id) {
            $answer = $this->for_option_w_possible_value($option_id, $session_id, $iteration);
            if ($answer){
                if ($answer === true) $answer = null;
                    $answers[$option_id] = $answer;
            }
        }

//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "($question_id , ... ), returning answers: " . print_r($answers, true), LOG_DEBUG);
        return $answers;
    }


  // Returns most recent status of checkbox option (if combo-check and true, return the (text) value),
  // or false if no answers for that checkbox exist for this session.
    function for_option_w_possible_value($option_id, $session_id, 
                                                        $iteration=0){
        $answer = $this->find('first' , array(
            'conditions' => array(
                "Answer.survey_session_id = $session_id",
                "Answer.option_id = $option_id",
                'Answer.iteration' => $iteration
            ),
            'fields' => array(
                'option_id',
                'question_id',
                'state',
                'survey_session_id',
                'value'
            ),
            'order' => 'Answer.id DESC'
        ));
//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "($option_id ... ), here's answer: " . print_r($answer, true), LOG_DEBUG);

        if (!empty($answer) && $answer['Answer']['state'] == 'true'){
            //$this->log('answer state == "true"', LOG_DEBUG);
            if ($answer['Answer']['value'] == null){
                //$this->log('answer value == null, returning true', LOG_DEBUG);
                return true;
            }
            else {
                //$this->log('answer value != null, returning value', LOG_DEBUG);
                return $answer['Answer']['value']; // only for combos
            }
        }
        else {
            //$this->log('answer state != "true", returning false', LOG_DEBUG);
            return false;
        }
    }

  // Returns the most recent answer for a given session
  function most_recent_for_session($session_id)
  {
    $answer = $this->find('first', array(
                        'conditions' => array("Answer.survey_session_id = $session_id"),
                        'fields' => array("survey_session_id", "question_id"),
                        'order' => "Answer.id DESC"));
    return $answer;
  }

    function beforeSave($options = Array()) {
        $answer =& $this->data;

        // remove any possible malicious HTML from text answers
        if (!empty($answer['Answer']['body_text']))
            $answer['Answer']['body_text'] = strip_tags($answer['Answer']['body_text']);

        // Save filename as answer value to prevent attempting to save array as string
        if (
            is_array($answer['Answer']['value']) and
            array_key_exists('name', $answer['Answer']['value'])
        )
            $answer['Answer']['value'] = $answer['Answer']['value']['name'];

        return true;
    }

    function beforeFind($queryData){
        // $this->log(__CLASS__ . "->" . __FUNCTION__ . '('.print_r($queryData, true).')', LOG_DEBUG);

        // if bindModels hasn't been disabled for perf reasons
        if ($this->bindModels and Configure::check('modelsInstallSpecific')){

            $models = Configure::read('modelsInstallSpecific');
            if (in_array('images', $models))
                $this->bindModel(array('hasOne' => array(
                    'Image' => array('dependent' => true)
                )), false);
        }
        return $queryData;
    }


    function afterSave($new_record) {

        if ($new_record) {
            $answer = $this->findById($this->id);

            // special cases where answers need to be saved or acted on elsewhere
            $this->save_t2_ranking_question($answer);
            $this->save_to_patient_record($answer);

            // Set reportable time for survey sessions
            $surveySession = $answer['SurveySession'];
            $timezone = $this->getTimezone($surveySession['patient_id']);
            $surveySession['reportable_datetime'] = $this->gmtToLocal($answer['Answer']['modified'], $timezone);
            $this->SurveySession->save($surveySession);
        }
    }

    /*%% TODO:  need to turn all modified times into local time from GMT.
      This is somewhat difficult because we have to do another db query
      (joining survey_sessions, users, clinics and sites table) to get the 
      timezone.
      Currently no one uses the times, so we're leaving this for now --- gsb
     */
    function afterFind($results, $primary = false) {
        return $results;
    }

    /* Email sites.research_staff_email_alias if patient wants to participate */
    private function participation_email($answer) {
        $user_id = $answer["SurveySession"]["patient_id"];

        # don't send for test users
        $Patient = new Patient();
        $patient = $Patient->findById($user_id);
        if($patient["Patient"]["test_flag"]) {
            return false;
        }

        $site = $this->query("SELECT research_staff_email_alias FROM
            sites JOIN clinics on clinics.site_id = sites.id
                  JOIN users on users.clinic_id = clinics.id
                  WHERE users.id = $user_id");
        $staff_email = $site[0]["sites"]["research_staff_email_alias"];
        $title = "[Esrac2] Study Participation Alert";
        $text = "A patient has selected 'yes' on the Study Participation question.\r\n";
        $text.= Router::url("/patients/edit/$user_id", 1);
        $headers = "From: esrac2 <esrachelp@rt.cirg.washington.edu>";
        mail($staff_email, $title, $text, $headers);
    }

  private function save_to_patient_record($answer) {
    switch ($answer["Answer"]["question_id"]) {

        case PARTICIPATION_QUESTION:
        case PARTICIPATION_QUESTION_NEW:
          # automatic email if patient wants to participate
          if((($answer["Option"]["id"] == 
                PARTICIPATION_YES_OPTION)
                || ($answer["Option"]["id"] ==
                PARTICIPATION_YES_OPTION_NEW)) 
                && ($answer["Answer"]["state"] == "true")
                && (Configure::read('isProduction'))){
              $this->participation_email($answer);
          }
          $patient_model = new Patient;
          $patient = $patient_model->findById($answer["SurveySession"]["patient_id"]);
          $participate = $answer["Option"]["Sequence"] == 1 && $answer["Answer"]["state"] == "true";
          $patient["Patient"]["study_participation_flag"] = $participate;
          $patient_model->save($patient);
        break;      

        case GENDER_QUESTION:
          $patient_model = new Patient;
          $patient = $patient_model->findById($answer["SurveySession"]["patient_id"]);
          if($answer["Answer"]["state"]) {
              if($answer["Option"]["Sequence"] == 1) {
                  $gender = "male";
              } else {
                  $gender = "female";
              }
          } else {
              $gender = "";
          }
          $patient["Patient"]["gender"] = $gender;
          $patient_model->save($patient);
        break;
      
    }
  }

  private function save_t2_ranking_question($answer) {
      $question_id = $answer["Answer"]["question_id"];
      $session_id = $answer["SurveySession"]["id"];
      $session_type = $answer["SurveySession"]["type"];
      $patient_id = $answer["SurveySession"]["patient_id"];

      # id of priority ranking question: add results to patient record
      if($question_id == RANKING_Q && $session_type == "T2") {
          $patient_model = new Patient();
          $patient = $patient_model->findById($patient_id);

          if($answer["Answer"]["state"] == "true") {
              $subscale_id = $answer["Option"]["AnalysisValue"];
              if($patient["Patient"]["t2a_subscale_id"]) {
                  $patient["Patient"]["t2b_subscale_id"] = $subscale_id;
              } else {
                  $patient["Patient"]["t2a_subscale_id"] = $subscale_id;
              }
          } else {
              $subscale_id = $answer["Option"]["AnalysisValue"];
              if($patient["Patient"]["t2a_subscale_id"] == $subscale_id) {
                  $patient["Patient"]["t2a_subscale_id"] = NULL;
              } else {
                  $patient["Patient"]["t2b_subscale_id"] = NULL;
              }
          }
          $patient_model->save($patient);
      }
  }

}
