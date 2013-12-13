<?php
/** 
    * Questionnaire class
    *
    * Models a single qr in the survey and holds its pages.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Questionnaire extends AppModel
{
  var $name = "Questionnaire";
  var $useTable = 'questionnaires';
  
  var $hasMany = array('Page' =>
                          array('conditions'   => 'Page.Sequence > 0',
                                'order'        => 'Page.Sequence ASC'));
  
  var $hasAndBelongsToMany = array('Project' =>
                                   array('with' => 'ProjectsQuestionnaires'));                            
  /* Load the translations */ 
  function afterFind($results,$primary=false){
        return $this->loadTranslations($results,$this->name);
    }  

  /*
   *
   */ 
  function first_page($id)
  {
//    $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
      $page = $this->Page->find('first', 
                            array('conditions' => array("questionnaire_id = $id",
                                      "Sequence > 0"),
                                'fields' => array("id", "Sequence", "questionnaire_id"),
                                'order' => "Sequence ASC"));
//    $this->log("Questionnaire first_page($id); here's page: " . print_r($page, true), LOG_DEBUG);
//    $this->log("Questionnaire first_page($id); here's page['Page']: " . print_r($page['Page'], true), LOG_DEBUG);
//    $this->log(__CLASS__ . "." . __FUNCTION__ . "($id); : ", LOG_DEBUG);
      return $page;
  }

  /*
   *
   */
    function last_page($id) {
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
        $page = $this->Page->find('first', array(
            'conditions' => array("questionnaire_id = $id", 'Sequence > 0'),
            'fields' => array('id', 'Sequence', 'questionnaire_id'),
            'order' => 'Sequence DESC'
        ));
        return $page;
    }
 
  /*
   *
   */ 
  function previous_qr_for_project($id, $project_id)
  {
    //$this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);
    $qr = $this->findById($id);
    $qr = $qr['Questionnaire'];
     
    $pqs = $this->ProjectsQuestionnaires->find(
                    'all', 
                    array('conditions' => array("project_id = $project_id",
                                                    "Sequence > 0"),
                        'field' => array("id", "questionnaire_id", "Sequence"),
                        'order' => array("Sequence DESC")));
     $found = false;
     foreach($pqs as $key => $pq) {
       $pq = $pq["ProjectsQuestionnaires"];
       if ($found) {
         return $pq['questionnaire_id'];
       } 
       if ($pq['questionnaire_id'] == $id) {
         $found = true;
       }
       
     }
     
     return null;
  }
  
  /*
   *
   */ 
  function next_qr_for_project($id, $project_id)
  {
//    $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

      $returnVal = null;

      $qr = $this->findById($id);
      $qr = $qr['Questionnaire'];
      
      $pqs = $this->ProjectsQuestionnaires->find(
                      'all', 
                      array('conditions' => 
                              array("project_id = $project_id",
                                        "Sequence > 0"),
                            'fields' => 
                              array("id", "questionnaire_id", "Sequence"),
                            'order' => array("Sequence ASC")));
      $found = false;
      foreach($pqs as $key => $pq) {
        $pq = $pq["ProjectsQuestionnaires"];
        if ($found) {
          $returnVal = $pq['questionnaire_id'];
          break;
        } 
        if ($pq['questionnaire_id'] == $id) {
          $found = true;
        }
        
      }

//      $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning $returnVal", LOG_DEBUG);  
      return $returnVal; 
  }

    /**
     *
     */
    function findQuestionsInSequence($qr_id){

        $questions = array();

        $qr = $this->findById($qr_id);
        //$this->log("Questionnaire.findQuestionsInSequence($qr_id), here's that questionnaire: " . print_r($qr, true), LOG_DEBUG);

        foreach ($qr["Page"] as $page) {
            //$this->log("Questionnaire.findQuestionsInSequence($qr_id), will consider page " . $page['id'] . " next.", LOG_DEBUG);
            $qs = $this->Page->findQuestionsInSequence($page['id']);
            $questions = array_merge($questions, $qs);
        }

        //$this->log("Questionnaire.findQuestionsInSequence($qr_id), returning questions:" . print_r($questions, true), LOG_DEBUG);
        return $questions;
    }
    
}
