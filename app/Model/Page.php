<?php
/** 
    * Page class
    *
    * Models a single page in the survey and holds its questions.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Page extends AppModel
{
  var $name = "Page";
  var $useTable = 'pages';
  
  var $belongsTo = array('Questionnaire' =>
                          array('className'    => 'Questionnaire',
                                // next is prob redundant
                                'conditions'   => 'Page.Sequence > 0',
                                'order'        => '',
                                'dependent'    =>  true,
                                'foreignKey'   => 'questionnaire_id'
                          )
                    );

  var $hasMany = array('Question' => array(
                    'sort' => 'Question.Sequence ASC',
                    'conditions'=> 'Question.Sequence > 0'));

    function afterFind($results, $primary = false){
        if ($primary)
            return $this->loadTranslations($results,$this->name);
        else
            return $results;
    }    
  /*
   *
   */
    function make($q_id, $title, $header, $text, $seq, $question, $options)
    {
        $page = $this->create(array("questionnaire_id" => $q_id,
            "Title"             => $title,
            "Header" => $header,
            "BodyText" => $text,
            "Sequence" => $seq));
        $page = $this->save($page);
        $this->Question->make($question, $options, $this->id);
        return $page;
    }

    /*
     *
     */
    function previous_page_for_project($current_page_id, $project_id) {
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $current_page = $this->findById($current_page_id);
        $current_page = $current_page['Page'];
        $qr_id = $current_page['questionnaire_id'];
        $sequence = $current_page['Sequence'];

        $prev_page = $this->find('first', array(
            'conditions' => array(
                'questionnaire_id' => $qr_id,
                array('Sequence <' => $sequence),
                array('Sequence >' =>  0)
            ),
            'fields' => array('id', 'Sequence', 'iterable'),
            'order' => 'Sequence DESC',
            'recursive' => 0)
        );

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "; retrieved prev_page: " . print_r($prev_page, true), LOG_DEBUG);

        if ($prev_page['Page']['id'])
            return $prev_page;
        else {
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "; didn't find prev page in qnr; will find prev qnr and then its last page", LOG_DEBUG);

            $previous_qr_id =
                $this->Questionnaire->previous_qr_for_project($qr_id, $project_id);
            if ($previous_qr_id){

                $firstPageNextQr = $this->Questionnaire->last_page($previous_qr_id);
                //$this->log(__CLASS__ . "." . __FUNCTION__ . "; returning last page of prev qnr; page ID = " . $firstPageNextQr, LOG_DEBUG);
                return $firstPageNextQr;
            }

            //$this->log(__CLASS__ . "." . __FUNCTION__ . "; returning null", LOG_DEBUG);
            return null;
        }
    }

  /*
   *
   */
  function next_page_for_project($current_page_id, $project_id)
  {
//    $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

    $current_page = $this->findById($current_page_id);
    $current_page = $current_page['Page'];
    $qr_id = $current_page['questionnaire_id'];
    $sequence = $current_page['Sequence'];

    $next_page = $this->find(
        "first",
        array("conditions" => 
                array("questionnaire_id" => $qr_id,
                        array("Sequence >" => $sequence),
                        array("Sequence >" =>  0)),
            "fields" => array('id'),
            "order" => "Sequence ASC",
            "recursive" => 0));

//    $this->log(__CLASS__ . "." . __FUNCTION__ . "; retrieved next_page: " . print_r($next_page, true), LOG_DEBUG);
                            
    if(array_key_exists('Page', $next_page)) {
        $next_page_id = $next_page['Page']['id']; 
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning: " . $next_page_id, LOG_DEBUG);
    
        return $next_page_id;
    } 
    else {

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; didn't find next page in qnr; will find next qnr and then its first page", LOG_DEBUG);
        $next_qr_id = 
            $this->Questionnaire->next_qr_for_project($qr_id, $project_id);
     
        if ($next_qr_id){

            $firstPageNextQr = $this->Questionnaire->first_page($next_qr_id);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning first page of next qnr; page ID = " . $firstPageNextQr, LOG_DEBUG);
            return $firstPageNextQr['Page']['id'];
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning null", LOG_DEBUG);
        return null;
    }
  }// function next_page_for_project($current_page_id, $project_id)
 
  /*
   *
   */
  function inProject($page_id, $project_id)
  {
    $page = $this->findById($page_id);
    $qr_id = $page["Questionnaire"]["id"];
    //$this->log("Page.inProject($page_id, $project_id), qr_id = $qr_id)", LOG_DEBUG);
    $qr = $this->Questionnaire->findById($qr_id);
    $projects = $qr["Project"];
    
    foreach($projects as $_k => $project) {
      if ($project["id"] == $project_id)
        return true;
    }
    
    return false;
  }

    /**
     *
     */ 
    public function findQuestionsInSequence($pageId){
        //$this->log("Page.findQuestionsInSequence($pageId), just entered.", LOG_DEBUG); 
        $questions = 
            $this->Question->find('all',
                                    array(
                                        'conditions' => array(
                                            'Page.id' => $pageId), 
                                        'recursive' => 1));   
        //$this->log("Page.findQuestionsInSequence($pageId), returning questions:" . print_r($questions, true), LOG_DEBUG); 
        return $questions;
    }
 
}
