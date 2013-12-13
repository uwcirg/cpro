<?php
/** 
    * Question class
    *
    * Models a single question in the survey and holds its options/answers
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Question extends AppModel
{
  var $name = "Question";
  var $useTable = 'questions';
  
  var $belongsTo = array('Page');
  var $hasMany   = array('Option' => 
                      array('order'       =>'Sequence ASC',
                            //'conditions'  => 'Sequence > 0'));
                            'conditions'  => 'Sequence > 0'),
                        'Item');

  function afterFind($results, $primary = false){
        return $this->loadTranslations($results,$this->name);
    }  

  function make($question, $options, $page_id) {
      $q = $this->create(array("page_id" => $page_id,
                               "ShortTitle" => $question));
      $q = $this->save($q);
      $id = $this->id;

      $options = $this->Option->make($id, $options);
  }

  function allShownQuestions() {
      return $this->find('all', 
        array('conditions' => 
            array("Question.Sequence > 0", 
                "Page.Sequence > 0", 
                "Page.id IS NOT NULL")));

  }

  function allShownIds() {
    $questions = $this->find("all",
      //return $this->find("all",
          array("conditions" => array("Page.sequence > 0", "Question.Sequence > 0", "Question.page_id > 0", 
                                      "Page.questionnaire_id IN (1, 2, 4,7,8,9,10,11,12,13,17,18,19,20,21,22,23,24,25)" ),
                                      # FIXME: could add a join to projects_questionnaires, but it times out 
                "recursive"  => 1));

    //$this->log('Question.allShownIds(), returning ' . print_r($questions, true), LOG_DEBUG);
    return $questions;

  }

  /* reportArray returns all questions shown in a project in a format used by the data export.
   *   Which questions?
   *     - Sequence is > 0 for Question and Page
   *     - Page is in a Questionnaire that is in the Project (for now, just assume all in Project 1) # FIXME
   *   What format?
   *     - Each as an array with keys 'id', 'type' and (for checkboxes) 'options'
    */
}
