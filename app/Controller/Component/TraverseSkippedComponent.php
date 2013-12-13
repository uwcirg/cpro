<?php
/** 
 * Traverse Component
 * 
 * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
 *
 * Think of it like a SAX parser for our survey: you
 * define functions that it should run every time it 
 * comes to an item.
 *
 * Abstract the process of walking through the project
 * tree and executing a function at each step. Lots of
 * code (calculations, skipped questions, etc.) can be
 * refactored into this pattern.
 */

/*TODO generalize this out and use it more. that was the orig
intention with the TraverseComponent, but it was never realized,
and the upgrade to cake 2 requires that all components extend
Component (and no multiple extension in php)*/

/** TraverseSkippedComponent
 * Uses the Traverse abstract class to walk through the survey
 * session and return an array of data to show on the 
 * skipped questions page.
 *
 * Rules: keep a question if it has no analysis value and isn't
 *        on a page that should be skipped.
 * Returns: the array needed for the skipped Questions view:
 *  [ ["Questionnaire" => Title, 
 *     "Questions" => ["text" => Question Text,
 *                     "page_id" => ID of the question's page]]]
 */
class TraverseSkippedComponent extends Component
{
    function startup(Controller $controller)
    {
        $this->controller = $controller;
        $this->session_id = $this->controller->Session->read("session_id");
        $this->session = $this->controller->SurveySession->findById($this->session_id);
    }

    /**
    * Note that the code in this fxn is not specific to traversing skipped, tho the fxns it calls are.
    */
    function run()
    {
        //        $this->log(__CLASS__ . "->" . __FUNCTION__ . "()", LOG_DEBUG);

        $project = $this->controller->Project->findById(
                $this->session['Project']['id']);

        $questionnaire_results = array();
        foreach($project["Questionnaire"] as $qr) {
            $questionnaire = 
                $this->controller->Questionnaire->findById(
                        $qr['id']);

            $page_results = array();
            foreach($questionnaire["Page"] as $pg) {
                $page = 
                    $this->controller->Page->findById(
                            $pg['id']);

                $question_results = array();
                foreach($page["Question"] as $q) {
                    $question =
                        $this->controller->Question->findById(
                                $q['id']);

                    $value = $this->question($question);
                    if ($value) {
                        array_push($question_results, $value);
                    }
                }
                $value = $this->page($page, $question_results);
                if ($value) 
                    array_push($page_results, $value);

            }
            $value = $this->questionnaire($questionnaire, $page_results);
            if($value)
                array_push($questionnaire_results, $value);
        }

        //        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), returning " . print_r($questionnaire_results, true), LOG_DEBUG);
        return $questionnaire_results;
    }
    // When we come to a questionnaire, if any pages
    // were skipped, we should keep this questionnaire
    // for the result, otherwise return null.
    function questionnaire($qr, $pages) {
        if($pages && 
            $this->controller->Conditionality->showQr(
                $qr["Questionnaire"]["id"],
                $this->controller->session_id)) {
            $blank_questions = array();
            foreach($pages as $page) {
                $blank_questions = array_merge($blank_questions, $page);
            }
            return array("Questionnaire" => $qr["Questionnaire"]["FriendlyTitle"],
                         "Questions" => $blank_questions);
        } else {
            return null;
        }
    }

    function page($page, $questions) {
        return $questions;
    }

    function question($question) {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $returnVal = null;
        # keep a question if it has no current answer,
        #   is not hidden by a condition,
        #   and has some options
            
        if(!empty($question['Option']) 
          && 
            $this->controller->Answer->
                analysisValueForSessionAndQuestion(
                    $this->session_id, $question["Question"]["id"])
                === null
          &&
            $this->controller->Conditionality->
                showPage($question["Page"]["id"], $this->session_id)
          && ($question["Question"]["ignore_skipped"] != 1)
        ){
            $returnVal = array("text" => $question["Question"]["BodyText"],
                         "page_id" => $question["Page"]['id']);
        } 

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), returnVal: " . print_r($returnVal, true), LOG_DEBUG);
        return $returnVal;
    }
} 
 
?>
