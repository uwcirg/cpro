<?php
/** 
    * Project class
    *
    * Projects are independent from one another, eg an assessment can be available for from more than one project at a time.
    * Models a single project and its joins with Qrs
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Project extends AppModel
{
	var $name = "Project";
	var $useTable = 'projects';
	
	var $hasAndBelongsToMany = array('Questionnaire' =>
									 array('with' => 'ProjectsQuestionnaires',
                                           'conditions' => 'Sequence > 0',
										   'order' => 'Sequence'));
	function questionnaires($id) {
		$project = $this->findById($id);
		return $project["Questionnaire"];
    }

    /** 
     * IN PROGRESS: trying to abstract this common pattern
     * not done yet!
     * traverse a project tree, executing given functions
     * for the project and each questionnaire, subscale, 
     * page, question and option. 
     */
    function questions_loop($questions, $q_f, $return) {
        foreach($questions as $question) {
            $options_callbcack = $make_options_callback($question);

        }
    }
    function make_options_callback($question) {
        $question_id = $question["Question"]["id"];
        return create_function('$return',
            'options_loop('.$q_id.', $return);');
    }
    function options_loop($options, $o_f, $return) {
        foreach($options as $id) {
            $option = $this->Option->findById($id);
            $o_f($return, $option);
        }
    }
    function traverse($id, $p_f, $qr_f, $ss_f, $page_f, $q_f, $o_f) {
        $return = "";
        // ... get down to the level of questions
        
        $questions = array();
        $options_callback = create_function('$return',
            'foreach($options as $option) {
                $o_f($option, $return);   
            ');

        $questions_callback = create_function('$return',
            'foreach($questions as $id) {
                $question = $this->Question->findById($id);
                $options = $question["Options"];
                $options_callback = options_callback($options);

                $q_f($question, $options_callback);
            }');
        

        $q_f($question, $item, $options_callback);
    }

    function q_f($return, $question, $item, $options_f) {
        // do something with the question
        $options_f($return); // call the rest of the tree
        
    }

    function first_questionnaire($project_id){
        
        $this->bindModel(
                array('hasAndBelongsToMany' =>
                    array('Questionnaire' =>
                        array('className' => 'Questionnaire',
                            'with' => 'ProjectsQuestionnaires',
                            'order' => 'Sequence',
                            'conditions' => 
                                array('ProjectsQuestionnaires.Sequence >' => 0)
        ))), false);
        $questionnaires = $this->questionnaires($project_id);
        
        $first_questionnaire = $questionnaires[0];

        $qnr_id = $first_questionnaire['id'];
        //$this->log("Project.first_questionnaire($project_id), returning $qnr_id", LOG_DEBUG);
        return $qnr_id;
    }

    /**
     *
     */
    function findQuestionsInSequence($proj = 1){

        $questions = array();
        $project = $this->findById($proj);
        //$this->log("Project.findQuestionsInSequence($proj), here's the project:" . print_r($project, true), LOG_DEBUG);

        foreach ($project['Questionnaire'] as $qr) {
            $qs = $this->Questionnaire->findQuestionsInSequence($qr['id']);
            $questions = array_merge($questions, $qs);
        }

        //$this->log("Project.findQuestionsInSequence($proj), returning questions:" . print_r($questions, true), LOG_DEBUG);

        return $questions;
    }

}
