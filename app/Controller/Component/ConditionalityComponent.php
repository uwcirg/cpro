<?php
/** 
    * Conditionality Component API
    *
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
 * The conditionality component encapsulates the logic determining whether a
 * specific page should be shown for a session. Its basic API is accessed
 * through the functions nextLink and previousLink. These functions should be
 * passed the current page id and session id; they will run the conditionality
 * rules on succeeding or previous pages until they find a page which ought to
 * be shown, and return a cakephp link to it.
 * 
 * Multiple conditions can be applied to a target; the default grouping is AND.
 *   To use OR instead, include a record with condition "OR" for that target. 
 *
 * The conditionality rules have two parts: 1) function definitions wrapped in
 * an appropriate php object, 2) records in the database's conditions table.
 *
 * Condition functions are grouped together into objects, which can be inherited
 * from other condition function objects. They should ultimately inherit from
 * ConditionFunctions (see documentation below), which provides the
 * infrastructure.  Functions defind inside your own ConditionFunction
 * subclasses should: 1. Accept 2-3 arguments (the session and page id, which
 * will be passed in autumatically, and also one other argument provided
 * database record (see below) 2. Return true if that page should be shown,
 * otherwise false.
 * 
 * When these functions are called is determined by records in the database.
 * The records contain an id, a TargetType (page or questionnaire) a target id,
 * and a condition. 
 * If the condition is formatted like:
 *    %(checkNotFirstVisit)
 * A function with that name is called and the target item is shown if the 
 * function returns true. 
 * 
 * If it is formatted like:
 *    %(comparisonQuestion 118 == 1)
 * The function name in first position is called with the argument in second position.
 * The result is then compared with the operator to the evaluated value of the 
 * item in the final position. Again, if the comparison is true, the item is shown.
 * 
 * The value which is being compared against can be a semicolon-delimited list,
 * e.g. 1;2;3;4. If the operand is == or ===, it's implicit OR
 *    %(comparisonQuestion 118 == 1;2;3;4)
 * If it's anything else eg !=, > etc, it's implicit AND, eg:
 *    %(comparisonField User.clinic_id != 1;2;3;4)
 * 
 * Negations can be used by adding ! before the condition's function name eg
 *          %(!isFirstSession)
 * Comments are provided in the source below.
 * 
 * 11/15/08 FIXME: Fails to return a sensible link if a
 * questionnaire that should be shown does not have any pages
 * that should be shown. (Need to add a recursive step_
 */
class ConditionalityComponent extends Component
{
    //  API

	/** startup: called by cakePHP automatically when included into a controller
	 * @ param AppController: current controller */
	function startup(Controller $controller)
	{
		$this->controller = $controller;
		$this->Condition = $this->controller->Condition;
		$this->ConditionFunctions = new InstanceConditionFunctions($controller);
	}

	/**nextLink: return a link to the next page to show in the survey
	 * @return string: cakephp url to the next page.
	 * @param int $page_id: id of the current page.
	 * @param int $session_id: id of the current session	 */
	function nextLink($page_id, $session_id)
	{
		$session = $this->controller->SurveySession->findById($session_id);
		return "/surveys/show/" . 
			$this->nextPage($page_id, $session);
	}

	/**previousLink: returns a link to the previous page to show in the survey session
	 * @param int $page_id
	 * @param int $session_id
	 * @return string: cakephp url
	 */
	function previousLink($page_id, $session_id) {
		$session = $this->controller->SurveySession->findById($session_id);
        $prev_page = $this->previousPage($page_id, $session);
		return "/surveys/show/{$prev_page['Page']['id']}";
	}

	/* nextPage: returns the id of the next page to show for this session
	 * @param int $page_id
	 * @param array $session 
	 * @return int next page's id */
	function nextPage($page_id, $session)
	{
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(page_id:$page_id, session var)", LOG_DEBUG);
		# First we find the next page in the normal sequence
		$page  = $this->controller->Page->findById($page_id);
		$qr_id = $page["Questionnaire"]["id"];
		$project_id = $session["Project"]["id"];

		$next_page_id = 
            $this->controller->Page->next_page_for_project(
                                                $page_id, $project_id);
        if ($next_page_id){
            # Now, find the first page in sequence that doesn't
            # match a condition.
            $next_page_id = $this->nextPageRecursive($next_page_id, $session);
        }
	
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning: " . $next_page_id, LOG_DEBUG);
        return $next_page_id;
	}
	
	/* previousPage: returns the id of the previous page to show for this session
	 * @param int $page_id
	 * @param array $session current session array
	 * @return int previous page's id */
	function previousPage($page_id, $session) {
        //$this->log(__CLASS__ . '.' . __FUNCTION__ . "(page_id:$page_id, session var)", LOG_DEBUG);
		$page = $this->controller->Page->findById($page_id);
		$qr_id =  $page['Questionnaire']['id'];
		$project_id = $session['SurveySession']['project_id'];
        $previous_page = $this->controller->Page->previous_page_for_project(
            $page_id,
            $project_id
        );
        if ($previous_page)
		    $previous_page = $this->previousPageRecursive(
                $previous_page,
                $session
            );

        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '; returning: ' . $previous_page_id, LOG_DEBUG);
        return $previous_page;
	}

	// Implementation functions
	// Don't call these from elsewhere, as their signatures may change.
		
	// nextPage/previousPageRecursive:
	// The last two functions are just setting up the data for the recursive functions.
	// Our goal is to return the first page in the sequence, starting with the one given,
	// that should be shown.
	// The execution strategy is to check first the questionnaire and then the
	// page, go forward to the next qr or page if this one is supposed to be skipped,
	// and call the function recursively again.

	/** Given a page, see if we should show it. If not, try the function again on the next one.
	 * @param $current_page_id: id of the page where we start looking; if null, start at the project's first page.
	 * @param $session, the array for the current session
	 * @return id of next page to show, or null if no more pages in survey
     */
	function nextPageRecursive($current_page_id, $session)
	{
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; current_page_id: " . $current_page_id, LOG_DEBUG);
		$session_id = $session["SurveySession"]["id"];
        $p_id = $session["SurveySession"]["project_id"];
        $page;
    
        if (!$current_page_id){
           // get the first page in the survey; conditionaltiy applied below 
            $current_qnr_id = 
                $this->controller->Project->first_questionnaire($p_id); 
            $current_page = $this->controller->Questionnaire->first_page($current_qnr_id);
            $current_page_id = $current_page['Page']['id'];
//            $this->log("Conditionality->nextPageRecursive($current_page_id, $session); current page id null so retrieved Project -> first_page($p_id) = $current_page_id", LOG_DEBUG);
        }

		$page = $this->controller->Page->findById($current_page_id);
        $qr_id = $page["Questionnaire"]["id"];
		
		// if the qr should not be shown, try the first page of the next qr
        if(!$this->showQr($qr_id, $session_id)) {
			
			$next_qr_id   = $this->controller->Questionnaire->next_qr_for_project($qr_id, $p_id);
			$next_page = $this->controller->Questionnaire->first_page($next_qr_id);
			$next_page_id = $next_page['Page']['id'];
			return $this->nextPageRecursive($next_page_id, $session);
		}
		// if the page should not be shown, try the next page in this qr
        if(!$this->showPage($current_page_id, $session_id)) {
			
			$next_page_id = $this->controller->Page->next_page_for_project($current_page_id, $p_id);
            if (isset($next_page_id)){
			    return $this->nextPageRecursive($next_page_id, $session);
            }
            else {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "; current_page_id: " . $current_page_id . "; returning null", LOG_DEBUG);
                return null;
            } 
		}
		// if the qr and page should be shown, this is it
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning current_page_id: " . $current_page_id, LOG_DEBUG);
		return $current_page_id;
	}
	
	/**
	 * @param $current_page_id
	 * @param $session
	 * @return int id of the previous page to show */
	function previousPageRecursive($current_page, $session) {
        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '; current_page_id: ' . $current_page_id, LOG_DEBUG);
		$session_id = $session['SurveySession']['id'];
		// $page = $this->controller->Page->findById($current_page_id);

		if (!$this->showQr($current_page['Questionnaire']['id'], $session_id)) {
			$p_id = $session['SurveySession']['project_id'];

			$previous_qr_id = $this->controller->Questionnaire->previous_qr_for_project(
                $current_page['Questionnaire']['id'],
                $p_id
            );
			$previous_page = $this->controller->Questionnaire->first_page($previous_qr_id);
			return $this->previousPageRecursive($previous_page, $session);
		}

		// This qr should be shown: now test this page
		if (!$this->showPage($current_page['Page']['id'], $session_id)) {
            $p_id = $session['SurveySession']['project_id'];
            $previous_page = $this->controller->Page->previous_page_for_project(
                $current_page['Page']['id'],
                $p_id
            );
	        return $this->previousPageRecursive($previous_page, $session);
        }
        # Both qr and page should be shown
        return $current_page;
	}

	/** 
	 * @param $qr_id : id of the questionnaire to be checked
	 * @param $session_id : id of the session to check for
	 * @return bool : true if it should be shown, else false 	 */
	function showQr($qr_id, $session_id)
	{
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; qr_id: " . $qr_id, LOG_DEBUG);
        $returnVal;
		$conditions = $this->Condition->for_qr($qr_id);
		if (!$conditions)
			$returnVal = true;
		else $returnVal = $this->runConditions($conditions, $session_id);

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; qr_id: " . $qr_id . "; returning $returnVal", LOG_DEBUG);
        return $returnVal;
	}
	
	// Return true if this page should be shown, else false
	// This function does not check qr conditions, 
	// usually you will want to call a higher-level function
	// like next_page_for_session 
	function showPage($page_id, $session_id)
	{
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; page_id: " . $page_id, LOG_DEBUG);
        $returnVal;
		$conditions = $this->Condition->for_page($page_id);
		
		if (!$conditions)
			$returnVal = true;
		else $returnVal = $this->runConditions($conditions, $session_id);

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; page_id: " . $page_id . "; returning $returnVal", LOG_DEBUG);
        return $returnVal;
	}

    // this is only called if questions.has_conditional_options,
    // in order to minimize DB lookups
    function getOptions($question, $session_id){
        //$this->log("getOptions for options: " . print_r($question['Option'], true), LOG_DEBUG);        
        foreach($question['Option'] as $key => $val){
            if (!$this->showOption($val['id'], $session_id)){
                unset ($question['Option'][$key]);
                //$this->log("option $key not included", LOG_DEBUG);
            }
            else{
                //$this->log("option $key remains included", LOG_DEBUG);
            }
        }
        $question['Option'] = array_values($question['Option']);
        //$this->log("question[Option] leaving getOptions = " . print_r($question['Option'], true), LOG_DEBUG);        
        return $question['Option'];
    }

	// Return true if this option should be shown, else false
	function showOption($option_id, $session_id)
	{
		$conditions = $this->Condition->for_option($option_id);
		if (!$conditions)
			return true;

        return $this->runConditions($conditions, $session_id);
    }

    /**
     * By default: all conditions must be met (i.e. AND)
     * If condition 'OR' exists for this target, meeting any of the conditions 
     *  for the target suffices
     */
    function runConditions($conditions, $session_id) {

        //$this->log(__CLASS__ . "." __FUNCTION__ . "; conditions = " . print_r($conditions, true), LOG_DEBUG);

        $andOr = '&&';
        $atLeastOneConditionMet = false;
        $allConditionsMet = true;
 
		foreach($conditions as $condition) {

            if ($condition["Condition"]["condition"] == 'OR'){
                $andOr = '||';
                continue;
            }

			preg_match("/\%\((.*)\)/", 
					   $condition["Condition"]["condition"],
                       $match);
            $terms = explode(" ", $match[1]);
            if (!$this->ConditionFunctions->evaluate_for_session($terms, $session_id)) {
                //$this->log("runConditions returning false, based on condition: " . print_r($condition, true), LOG_DEBUG);
                $allConditionsMet = false;
                //return false;
            }
            else {
                $atLeastOneConditionMet = true;
            }
		}
	
        if ($allConditionsMet){
            $returnVal =  true;
        }
        elseif ($atLeastOneConditionMet){
            if ($andOr == '||'){
                $returnVal = true;
            }
            else $returnVal = false;
        }
	    else {
            $returnVal = false;
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; qr_id: " . $qr_id . "; returning $returnVal", LOG_DEBUG);
		return $returnVal;
	}// function runConditions($conditions, $session_id) {
}// class ConditionalityComponent extends Component


/**
 *
 */
class ConditionFunctions extends Object
{
	// this function initializes an instance and accepts a controller
	// instance so we can access models and other components.
	// This controller had better be Survey controller and have access
	// to all those models...
	
	function ConditionFunctions(&$controller) {
		$this->controller = $controller;
	}
	
    // all outside objects should only call evaluate_for_session 
    // Accepts a string and the session_id and returns 
    // the value of the appropriate function.
    function evaluate_comparison_for_session($session_id, $function_name, $arg, $comparison, $value) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(session_id: $session_id, function_name: $function_name, arg: $arg, comparison: $comparison, value: $value)", LOG_DEBUG);

        $result = call_user_func(array($this, $function_name), $session_id, $arg);

//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "(...); just calc'd result: " . $result . ".", LOG_DEBUG);

        $returnVal = true;

        //For unanswered qns, only trigger condition if requirement is "!= null"
        if(is_null($result) && $comparison == "!=" && $value == 'null') {
            $returnVal = false;
        }
        // ... or comparison is ===
        elseif(is_null($result) && $comparison != "===") {
            $returnVal = true;
        }
        // comparisonQuestion calls Answer->analysisValueForSessionAndQuestion() which returns an array of checked boxes for questions with checkbox options. This should handle checking if a given option id is in the list returned by analysisValueForSessionAndQuestion()
        else if (is_array($result)){
//            $this->log('result: '. print_r($result, true), LOG_DEBUG);
            $returnVal = array_key_exists($value, $result);
        }
        else {
            switch($comparison) {
            case "==":
            case "===":
                $returnVal = $result == (int)$value;
                break;
            case "!=":
                $returnVal = $result != (int)$value;
                break;
            case ">":
                $returnVal = $result > (int)$value;
                break;
            case "<":
                $returnVal = $result < $value;
                break;
            }
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); returning $returnVal", LOG_DEBUG);
        return $returnVal;
    }// function evaluate_comparison_for_session

    /**
     *
     */
    function evaluate_for_session($terms, $session_id) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...) w/ args session_id $session_id and terms " . print_r($terms, true), LOG_DEBUG);
        //$this->log("terms in evaluate_for_session = " . print_r($terms, true), LOG_DEBUG);
        $function_name = $terms[0];

        // Check for negation
        $negation = false;
        if ($function_name[0] == '!'){
            $function_name = substr($function_name, 1);
            $negation = true;
        }

        $returnVal;

        if(count($terms) == 1) {
            $returnVal = call_user_func(array($this, $function_name), $session_id);
        } 
        elseif(count($terms) == 2) {
            // eg clinic-specific condition
            list($function_name, $arg) = $terms;
            $returnVal = call_user_func(array($this, $function_name), $session_id, $arg);
        } 
        else {
            list($function_name, $arg, $comparison, $compWith) = $terms;
            //$this->log("evaluate_for_session; size of terms > 2; next is evaluate_comparison_for_session for function_name:" . $function_name, LOG_DEBUG);  

            $values = explode(";", $compWith);
            
            $returnVal = true;
            if (($comparison == '==') || ($comparison == '===')){
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); comparison either == or ===; initializing returnVal to false", LOG_DEBUG);
                $returnVal = false;
            }
            foreach($values as $value){
                $comparisonEval = $this->evaluate_comparison_for_session(
                    $session_id,
                    $function_name,
                    $arg,
                    $comparison,
                    $value
                );
                if (($comparison == '==') || ($comparison == '===')){
                    // $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); comparison either == or ===", LOG_DEBUG);
                    // implicit OR
                    if ($comparisonEval){
                        // $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); comparison either == or ===, and comparisonEval true, so setting returnVal true", LOG_DEBUG);
                        $returnVal = true;
                        break;
                    }
                }
                else{
                    // implicit AND
                    if (!$comparisonEval){
//                        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); comparison neither == nor ===; !comparisonEval; setting returnVal = false", LOG_DEBUG);
                        $returnVal = false;
                        break;
                    }
                } 
            }
        }

        if ($negation)
            $returnVal = !$returnVal;

        // $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); returning $returnVal", LOG_DEBUG);
        return $returnVal;

    }// function evaluate_for_session($terms, $session_id) {

}// class ConditionFunctions extends Object



/**
 *
 * Conditions object for this survey.
 * Add rules to this object, or subclass it
 * Each rule should return true if the page
 * should be shown, otherwise false.
 */
class InstanceConditionFunctions extends ConditionFunctions
{

    function _get_user($session_id) {
        return $this->controller->Auth->user();
    }

    function _get_patient($session_id) {
        return $this->controller->Patient->findById(
                    $this->controller->session['SurveySession']['patient_id']
                    );
    }

    function male($session_id) {
        $patient = $this->_get_patient($session_id);
        return $patient["Patient"]["gender"] == "male"; 
    }

    function female($session_id) {
        return ! $this->male($session_id);
    }

    function isConsentStatus($session_id, $consent_status){
        $patient = $this->_get_patient($session_id);
        return $consent_status == $patient['Patient']['consent_status'];
    }

    function isElectiveSession($session_id) {
        return ! $this->isNotElectiveSession($session_id);
    }

    function isNotElectiveSession($session_id) {
        return ($this->controller->session["SurveySession"]["appointment_id"] != null); 
    }

    function isEvenWeek($session_id) {
        $patient = $this->controller->patient;
        $currentWeek = $this->controller->Patient->getTrialWeek($patient);
        
        return ($currentWeek % 2 == 0);
    }
    function isOddWeek($session_id) {
        $patient = $this->controller->patient;
        $currentWeek = $this->controller->Patient->getTrialWeek($patient);
        
        return !($currentWeek % 2 == 0);
    }

    function isSessionType($session_id, $session_type){
        return ($session_type == $this->controller->session["SurveySession"]["type"]);
    }

    function isProjectId($session_id, $project_ids){
        $project_ids = explode(';', $project_ids);
        return in_array(
            $this->controller->session['Project']['id'],
            $project_ids
        );
    }

    function isTtime($session_id) {
        return ! $this->isNotTtime($session_id);
    }

    function isNotTtime($session_id) {
        return ($this->controller->session["SurveySession"]["type"] == SurveySession::NONT); 
    }

    // Is the session is the first for this patient?
    function isFirstSession($session_id) {
        return 
          $this->controller->SurveySession->getSessionNumber(
                $session_id, 
                $this->controller->session["SurveySession"]["patient_id"]) 
          == 0;
    }

    function isT1($session_id) {
        return $this->controller->session["SurveySession"]["type"] == "T1";
    }

    function isT2($session_id) {
        return $this->controller->session["SurveySession"]["type"] == "T2";
    }

    function isT3($session_id) {
        return ($this->controller->session["SurveySession"]["type"] == "T3");
    }

    function isT4($session_id) {
        return ($this->controller->session["SurveySession"]["type"] == "T4");
    }

    function isT1orT2($session_id) {
        $type = $this->controller->session["SurveySession"]["type"];
        return ($type == "T1" || $type == "T2");
    }

    function showChaplainQ($session_id) {  
        return $this->comparisonQuestion($session_id, 891) == 1
            || $this->comparisonQuestion($session_id, 892) == 2;
    }

    function showPromis($session_id) {
        return $this->comparisonQuestion($session_id, 113) > 5
            || $this->comparisonQuestion($session_id, 63) >= 3
            || $this->comparisonQuestion($session_id, 64) >= 3;
    }

    function checkSkippedQuestions($session_id) {
        // No decisions made on skipped questions page.
		return true;
    }

    /**
    * Note: this is only functional for radio questions
    */
    function comparisonQuestion($session_id, $question_id) {

        $val = $this->controller->Answer->analysisValueForSessionAndQuestion($session_id, $question_id);
//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "(session_id:" . $session_id . ", question_id:" . $question_id. "); " . "returning analysisValueForSessionAndQuestion: $val.", LOG_DEBUG);
        return $val;
    }

    function comparisonSubscale($session_id, $subscale_id) {
        $patient = $this->controller->patient;
        $sessionSubscaleScore = 
            $this->controller->Instruments->calculate_subscale(
                $subscale_id);
        if ($sessionSubscaleScore == null){
            // likely that insufficient answers given for subscale
            // FIXME this won't work if the scale is inverted...
            $sessionSubscaleScore = -1;
        }
        return $sessionSubscaleScore;
    }

    /**
    * use like %(comparisonField User.email != null)
    */
    function comparisonField($session_id, $tableDotField) {
        $patient = $this->controller->patient;
        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r($patient, true), LOG_DEBUG);
        $explosion = explode('.', $tableDotField);
        $table = $explosion[0];
        $field = $explosion[1];
        //$this->log("comparisonField returning " . (int)($patient[$table][$field]), LOG_DEBUG);
        return (int)($patient[$table][$field]);
    }

    /**
     *
     */
    function questionValue($session_id, $question_id) {
        return true;
    }

    /**
     *
     */
    function someCheckboxCheckedForQ($session_id, $questionId)
    {
        $returnVal = false;
        $checkedOptions = $this->controller->Answer->analysisValueForSessionAndQuestion($session_id, $questionId);
        if (sizeof($checkedOptions) > 0) $returnVal = true;
//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "(session_id:" . $session_id . ", questionId:" . $questionId. "); " . "returning $returnVal.", LOG_DEBUG);
        return $returnVal;
    }

    /**
     *
     */
    function T2AorB($session_id)
    {
        return $this->T2A($session_id) || $this->T2B($session_id);
    }

    /**
     *
     */
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

    /**
     *
     */
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

    /**
     *
     */
      function PARTIAL_FINALIZATION($session_id)
      {
          return $this->controller->session["SurveySession"]["partial_finalization"] == 1;
      }

    /**
     *
     */
      function TWO_CRITICAL_SUBSCALES($session_id)
      {
          $critical_subscales = $this->controller->SessionSubscale->criticalForSession($session_id);
          return count($critical_subscales) > 1;
      }

    /**
     *
     */
      function ONE_CRITICAL_SUBSCALE($session_id)
      {
          $critical_subscales = $this->controller->SessionSubscale->criticalForSession($session_id);
          return count($critical_subscales) > 0;
      }

    /**
     *
     */
      function SHOW_TEACHING($session_id) {
          return $this->TREATMENT_GROUP($session_id) AND $this->ONE_CRITICAL_SUBSCALE($session_id);
      }

    /**
     *
     */
      function TREATMENT_GROUP($session_id)
      {
          $patient = $this->controller->patient;
          if($patient["Patient"]["study_group"] == Patient::TREATMENT) {
              return true;
          }
          return false;
      }

    /**
     *
     */
      function CONTROL_GROUP($session_id)
      {
          $patient = $this->controller->patient;
          if($patient["Patient"]["study_group"] == Patient::CONTROL) {
              return true;
          }
          return false;
      }

    /**
     * FIXME This fxn was written before staff were allowed to take
     *  assessments as patients, and will err if used that way.
     */
    function presentNewAim($session_id) {

        //return true;

        if (! $this->isT3($session_id)){
            //$this->log("IS NOT T3; "
            //        . Debugger::trace(), LOG_DEBUG);
            return false;
        }

        //$patient = $this->controller->patient;
        $user = $this->controller->user;

        //user has Clinic.site_id
        // we have access to the User model      
 
        //$this->controller->DhairLogging->logArrayContents($user, "user w/ site data, hopefully");

        // Patients at the following clinics shouldn't be presented this aim,
        // since they don't have oral meds defined
        // UWMC-RadOnc, DFCI-Surv, DFCI-TBD, DFCI-Gyn, DFCI_LNH
        $clinic = $user['Clinic']['id'];
        if (($clinic == 1) || ($clinic == 5) || 
                ($clinic == 7) || ($clinic == 9) || ($clinic == 14)){
            //$this->log("New aim not presented for this clinic ($clinic); "
            //        . Debugger::trace(), LOG_DEBUG);
            return false;
        }

        $site = $this->controller->Site->findById($user['Clinic']['site_id']);
        //$this->controller->DhairLogging->logArrayContents($site, "site");

        if(($site["Site"]["new_aim_consent_mod_date"] != null)   
            //&& (strtotime($patient["Patient"]["consent_date"]) 
            //&& ($patient["Patient"]["consent_date"]
            && ($user["Patient"]["consent_date"]
                >= $site["Site"]["new_aim_consent_mod_date"])) {
            //$this->log("consent date later than new aim consent mod date; " . Debugger::trace(), LOG_DEBUG);
            return true;
        }
        else {
            //$this->log("consent date before new aim consent mod date; " . Debugger::trace(), LOG_DEBUG);
            return false;
        }
    }

    /**
     *
     */
    function takingOralMeds ($session_id) {
        //$answer = $this->comparisonQuestion($session_id, 1001);
        //$this->controller->DhairLogging->logArrayContents($answer, "answer for oral meds Q");

        if ($this->comparisonQuestion($session_id, 1001) != null)
            return true;
        else return false;
    }

    /**
    * $clinics like 1;2;99;
    */ 
    function clinicSpecific($session_id, $clinics){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
       
        return $this->clinic($session_id, true, $clinics);
    }

    /**
    * $clinics like 1;2;99;
    */ 
    function notClinic($session_id, $clinics){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        
        return $this->clinic($session_id, false, $clinics);
    }

    /**
    * $clinics like 1;2;99;
    * can do this instead: %(comparisonField User.clinic_id == 1;2;3;4)
    */ 
    function clinic($session_id, $matchBool, $clinics){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        if ($matchBool == '==') $matchBool = true;
        else $matchBool = false;       

        $patient = $this->controller->patient;

        $match = explode(";", $clinics);

        foreach($match as $clinic){
            if($patient["User"]["clinic_id"] == $clinic) {
                /**$this->log(__FUNCTION__ . "() patient's clinic (" .
                    $patient['User']['clinic_id'] . ") MATCHED (" . 
                    $clinic . ")", LOG_DEBUG);*/
    
                if ($matchBool){
                    return true;
                }
                else{
                    return false;
                }
            }
            else {
                /**$this->log("clinic: patient's clinic (" .
                    $patient['User']['clinic_id'] . ") did not match (" . 
                    $clinic . ")", LOG_DEBUG);*/
            } 
        }
//      $this->log(__FUNCTION__ . "(): patient's clinic (" . $patient['User']['clinic_id'] . ") didn't match any", LOG_DEBUG);*/
        if ($matchBool){
            return false;
        }
        else{
            return true;
        }
    }// function clinic($session_id, $clinics, $matchBool){

    /**
    * $sites like 1;2;99;
    */ 
    function siteSpecific($session_id, $sites){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
       
        return $this->site($session_id, true, $sites);
    }

    /**
    * $sites like 1;2;99;
    */ 
    function notSite($session_id, $sites){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        
        return $this->site($session_id, false, $sites);
    }

    /**
    * $sites like 1;2;99;
    */ 
    function site($session_id, $matchBool, $siteIds){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        if ($matchBool == '==') $matchBool = true;
        else $matchBool = false;       

        $patient = $this->controller->patient;

        $match = explode(";", $siteIds);

        $clinic = $this->controller->Clinic->find('first', 
                        array('conditions' => 
                            array('Clinic.id' => 
                                        $patient['User']['clinic_id'],
                                'Clinic.site_id' => 
                                    $match),
                        'recursive' => -1));
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; clinic: " . print_r($clinic, true), LOG_DEBUG);

        if ($clinic){
//            $this->log(__FUNCTION__ . "(): patient's clinic (" . $patient['User']['clinic_id'] . ") matched a site, so returning $matchBool", LOG_DEBUG);
            return $matchBool;
        }
        else {
//            $this->log(__FUNCTION__ . "(): patient's clinic (" . $patient['User']['clinic_id'] . ") didn't match any sites", LOG_DEBUG);
            return !$matchBool;
        } 
    }// function site 

    /**
    * Check whether the user just clicked the Study Info button
    *   on the new (January 2010) participation query page
    */ 
    function justClickedShowStudyInfo($session_id){
        if ($this->controller->Session->read("ViewSurveyInfo") === true){
            $this->controller->Session->delete("ViewSurveyInfo");
            return true;
        }
        return false;
    }

    function T4Intervention($session_id) {

        if ($this->isT4($session_id) && $this->TREATMENT_GROUP($session_id)){
            return true;
        }
        return false;
    }

    function usedAnInterventionFeature($session_id) {
        $compQn1013 = $this->comparisonQuestion($session_id, 1013);
        $compQn1015 = $this->comparisonQuestion($session_id, 1015);
        $compQn1017 = $this->comparisonQuestion($session_id, 1017);
        $compQn1019 = $this->comparisonQuestion($session_id, 1019);

        // note that if they didn't answer any of these, this will return false
        return 
            (($compQn1013 >= 1) && ($compQn1013 < 6))
            ||
            (($compQn1015 >= 1) && ($compQn1015 < 6))
            ||
            (($compQn1017 >= 1) && ($compQn1017 < 6))
            ||
            (($compQn1019 >= 1) && ($compQn1019 < 6));
    }
}// class InstanceConditionFunctions extends ConditionFunctions

