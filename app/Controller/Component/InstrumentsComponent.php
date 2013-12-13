<?php

/* 
    * Instruments component
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * Methods for calculating the values of scales and subscales
    * for instruments in esrac2. 
    * 
    *   - Combination methods take an array of analysis values (possibly null)
    *     and return a single value (also possibly null, depending on its rules)
    *
    * Most Esrac2 calculations look like:
    *  1. Get an array of all analysis values for questions in one subscale
    *  2. Find the mean of defined values, or return null if more than half are null
    *  3. Invert and scale the range (e.g. from a possible 0->5 to a possible 100->0)
*/

// returns arithmetic mean of provided array of numbers
function mean($values) {
    $args = print_r(func_get_args(), true);
//    CakeLog::write(LOG_DEBUG,  "(); args:" . $args);
	return (float)array_sum($values) / count($values);
}

// returns arithmetic median of provided array of numbers
// need clarification on rounding and edge cases
function median($values) {
	trigger_error("not implemented");
}

// returns sum of provided array of numbers
function sum($values) {
//    CakeLog::write('debug', __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true));
	return array_sum($values);
}

// returns sum of provided array of numbers
// leverages item.range to create subscales with variations in item weights
function weighted_sum($values) {
//    CakeLog::write('debug', __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true));
    return apply_unless_half_null($values, 'array_sum');
}

// returns T only if the value is NULL
// remember == and === are not reliable in php with null!
function not_null($value) {
	return ! is_null($value);
}

// given values, a function, and a percentage 
// If #null/#total values is >= the percentage, return null.
// otherwise returns the result of applying function to non-null values 
// e.g. [Maybe a] -> ([a]->b) -> Maybe b
function apply_unless_half_null($values, $function, $null_ratio = 0.5) {
    $args = print_r(func_get_args(), true);
//    CakeLog::write(LOG_DEBUG,  "(); args:" . $args);
    $returnVal;
    $nonNullValues = array_filter($values, "not_null");
    if (count($nonNullValues) <= count($values) * (1 - $null_ratio)
        || count($nonNullValues) < 1) {
		$returnVal = null;
	} else {
		$returnVal = call_user_func($function, $nonNullValues);
	}

//    CakeLog::write(LOG_DEBUG,  __FUNCTION__ . "(...); returning :" . $returnVal);
    return $returnVal;
}

function mean_or_null($values) {
    return apply_unless_half_null($values, 'mean');
}

function mean_or_half_null($values) {
    return apply_unless_half_null($values, 'mean');
}

function mean_or_third_null($values) {
    // Turn down if it's exactly 1/3rd, so .33 is acceptable rounding
    return apply_unless_half_null($values, 'mean', 0.33);
}

function mean_or_more_than_half_null($values) {
    // Turn down if null for more than half the answers
    return apply_unless_half_null($values, 'mean', 0.499);
}

function mean_or_any_null($values){
    // Turn down if null for any answers
    return apply_unless_half_null($values, 'mean', 0.01);
}

function mean_or_all_null($values){
    // Turn down if null for all answers
    return apply_unless_half_null($values, 'mean', 1);
}

function sum_or_third_null($values) {
    // Turn down if it's exactly 1/3rd, so .33 is acceptable rounding
    return apply_unless_half_null($values, 'sum', 0.33);
}

function sum_or_any_null($values) {
    // Turn down if null for any answers
    return apply_unless_half_null($values, 'sum', 0.01);
}

// TRANSFORMATION FUNCTIONS
// note that php will treat NULL as 0 if you use ==, so use ===.
// These functions test for NULL and propagate it, rather than 
// a numerical result that is incorrect

// transform a score between two scales
function scaleToFrom($value, $to, $from) {
	if($value===NULL) return NULL;
	return $value / $from * $to;
}

function inverseScale($value, $outOf) {
	if($value===NULL) return NULL;
	return $outOf - $value;
}

class InstrumentsComponent extends Component
{
    /**
     *
     */
    function startup(Controller $controller)
    {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);
        $this->controller = $controller;
        $this->session_id = $this->controller->session_id;
        $this->patient_id = $this->controller->authd_user_id;
        $this->debug = false;
    }

    /**
     *
     */
    function calculate_for_session($survey_session_id) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);
        //sleep(20); // simulate 20 seconds of computation time FIXME remove 
        $this->session_id = $survey_session_id;
        $survey_session = $this->controller->SurveySession->
            findById($survey_session_id);
        $this->patient_id = $survey_session["SurveySession"]["patient_id"];
        $scales = $this->controller->Scale->
            sForProject($survey_session["Project"]["id"], false);
        //$this->log("calculate_for_session; here are scales: " . print_r($scales, true), LOG_DEBUG);

        foreach($scales as $scale) {
            if ($scale["id"] == NON_FXNL_SCALE_ID) continue;
            $this->calculate_scale($scale["id"], $survey_session_id);
        }
    }

    /**
     * Calculates scale value
     */
    function calculate_scale($scale_id, $session_id) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);
        $session_scale = $this->controller->SessionScale->createWith($session_id, $scale_id, $session_scale_id);
        // $session_scale_id is passed by reference, so
        // after this function call it contains the id
        // of the newly-created session_scale record.
        
        $values = array();
        $subscales = 
            $this->controller->Subscale->find(
                    'all',
                    array(
                      'conditions' => array('Subscale.scale_id' => $scale_id), 
                      'recursive' => -1,
                      'order' => array('Subscale.order ASC'))
            );
//        $this->log("calculate_scale for id $scale_id; here are its subscales: " . print_r($subscales, true), LOG_DEBUG);
        foreach($subscales as $subscale) {
            //array_push($values, $this->calculate_subscale($subscale["id"], $session_scale_id, false));
            array_push($values, $this->calculate_subscale($subscale["Subscale"]["id"], $session_scale_id, false));
        }

        // TODO This is hard-coded to ignore Scale.combination and just sums the subscales,
        //          and doesn't report anything if a subscale has a null score.
        //          This was to meet the spec of the one project which uses Scale scores.
        $scaleScore = 0;
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); values from calculate_subscale:" . print_r($values, true), LOG_DEBUG);
        foreach($values as $value){
            if (is_null($value)){
                $scaleScore = null;
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); found a null value for a subscale, scaleScore set to null ($scaleScore) for scale_id $scale_id", LOG_DEBUG);
                break;
            }
            $scaleScore += $value;
        }
        $this->controller->SessionScale->id = $session_scale_id;
        $this->controller->SessionScale->saveField('value', $scaleScore);

    }// function calculate_scale($scale_id, $session_id) {

    /**
     * Calculates subscale value
     *
     * @param $session_scale_id : if null, don't save record 
     * @param $return_ratio : 
     *          if false, return the interpretable score
     *          if true, return a float between 0 and 1 indicating proportion of range, to be used in scale calculations (E.g. a 5 on a 0-10 subscale will return 0.455)
     * @return float between 0 and 1
     *
     */
    function calculate_subscale($subscale_id, $session_scale_id = null, 
                                            $return_ratio = false) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);

        $patient_id = $this->patient_id;
        $subscale = $this->controller->Subscale->findById($subscale_id);
        $combination = $subscale["Subscale"]["combination"];
        $value = null;
        $itemValues = array();
        foreach($subscale["Item"] as $item) {
            // ration: whether to get the score as a decimal between 0 and 1 (so that we can scale it to whatever range we like)
            $ratio = true;
            // if simple sum
            if (strpos($combination, 'sum', 0) === 0) 
                $ratio = false;
            array_push($itemValues, $this->calculate_item($item["id"], $subscale_id, $ratio));
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); combination fxn:$combination; itemValues:" . print_r($itemValues, true), LOG_DEBUG);
        if(function_exists($combination)) {
            $combined = call_user_func($combination, $itemValues);
        }

        //TODO will likely need to clean this up for sum

        if(!is_null($combined)) {
            // Modify and combine according to db Subscale
            $range = $subscale["Subscale"]["range"];
            $invert = $subscale["Subscale"]["invert"];
            $base = $subscale["Subscale"]["base"];

//            $this->log(__CLASS__ . "." . __FUNCTION__ . "() subscale $subscale_id; range:$range; invert:$invert; base:$base; combination fxn:$combination; combined (result of that fxn), before inversion: $combined", LOG_DEBUG);

            // if simple sum
            if (strpos($combination, 'sum', 0) === 0){ 
                $value = $combined;
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "() subscale $subscale_id; simple sum, value = $value", LOG_DEBUG);
            }
            else {
                if($invert)
                    $combined = 1 - $combined;
                $value = $combined * $range + $base;
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "() subscale $subscale_id; post any inversion ($invert), setting value =  combined ($combined) * range ($range) + base ($base), namely: $value", LOG_DEBUG);
            }

        }

        $returnVal = null;
        if($return_ratio) {
            $returnVal = $combined; // for use in scale calculation
        } elseif (isset($combined)){
            $returnVal = $value; // to compare with a final value
        }

        if (isset($session_scale_id)){
            $this->controller->SessionSubscale->createWith($subscale_id,
                                                       $session_scale_id,
                                                       $this->session_id,
                                                       $patient_id,
                                                       $value);
        }

        // If this is the PRE_CONSENT_QUALIFIER_SUBSCALE and the score is at or above critical, and the patients.consent_status is "usual care", change to "pre-consent"        
        if (defined('PRE_CONSENT_QUALIFIER_SUBSCALE') 
                && $subscale_id == PRE_CONSENT_QUALIFIER_SUBSCALE) {
            $patient = $this->controller->Patient->findById($patient_id);
            //$this->log("calculate_subscale PRE_CONSENT_QUALIFIER_SUBSCALE; here's patient: " . print_r($patient, true), LOG_DEBUG);

            if ($value >= $subscale["Subscale"]["critical"]){

                if ($patient["Patient"]["consent_status"] == 
                        Patient::USUAL_CARE){
                    //$this->log("calculate_subscale PRE_CONSENT_QUALIFIER_SUBSCALE and value > critical, and patient is USUAL_CARE, so switching to PRECONSENT", LOG_DEBUG);
                    $patient["Patient"]["consent_status"] = Patient::PRECONSENT;
                    $this->controller->Patient->save($patient);
                }
            }
            elseif (($value != null) && 
                    ($patient["Patient"]["consent_status"] == 
                                                    Patient::USUAL_CARE)){

                $this->controller->Patient->addClinicToFindResult($patient);

                if ($patient["Clinic"]["one_usual_care_session"] == 1 ){
                    //$this->log("calculate_subscale PRE_CONSENT_QUALIFIER_SUBSCALE, value < critical, and clinic has one_usual_care_session, so switching to OFF_PROJECT", LOG_DEBUG);
                    $patient["Patient"]["consent_status"] = 
                        Patient::OFF_PROJECT;
                    $this->controller->Patient->save($patient);
                }
            }
        }
        else {
            //$this->log("calculate_subscale ($subscale_id) !(PRE_CONSENT_QUALIFIER_SUBSCALE and value > critical)", LOG_DEBUG);
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() returning " . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// function calculate_subscale


    /**
     * Calculates the stored database value and the used calculation
     * value for an Item. Note that "item" fxnality is only impld for radio q's 
     * @param $subscale_id : if null, don't save record 
     * @param $return_ratio : 
     *          if false, return the interpretable score
     *          if true, return a float between 0 and 1 indicating proportion of range, to be used in subscale calculations (E.g. a 3 on a 1-5 Item will return a .5)
     */
    function calculate_item($item_id, $subscale_id, 
                                        $return_ratio=false){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);

        $returnVal;
        $item = $this->controller->Item->findById($item_id);
        $question_id = $item["Item"]["question_id"];
        $range = $item["Item"]["range"];

        // This value comes from the radio Option's Sequence,
        // which is 1-based, so all items are 1-based
        $value = $this->controller->Answer->analysisValueForSessionAndQuestion(
                        $this->session_id,
                        $question_id);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); range:$range; value:$value", LOG_DEBUG);

        if (isset($subscale_id)){
            $this->controller->SessionItem->createWith($item_id, $subscale_id, $this->session_id, $value);
        }

        if(!$return_ratio) {
            $returnVal = $value;
        } 
        else {
            // items are 1-based, so transform to 0-based 
            // with a range of 1 for the calculate_subscale
            if(!isset($value)) {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); value !isset", LOG_DEBUG);
                $returnVal = null;
            } 
            else {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); value isset, so setting returnVal = (value - 1) / range", LOG_DEBUG);
                $returnVal = ($value - 1) / $range;
            }
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() item $item_id, range $range, value $value, subscale $subscale_id, returning " . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// function calculate_item


}

?>
