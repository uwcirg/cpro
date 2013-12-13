<?php
/** 
    * Condition class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Condition extends AppModel
{
  var $name = "Condition";
  var $useTable = 'conditions';
  var $condition_functions = null;
  
  function for_page($page_id)
  {
    return $this->find('all', 
                        array(
                          'conditions' => array(
                                "target_type = 'Page'",
                                "target_id = '$page_id'")));
  }
  
  function for_qr($qr_id)
  {
      return $this->find('all', 
                          array(
                            'conditions' => array(
                                "target_type = 'Questionnaire'",
                                "target_id = '$qr_id'")));

  }  
  
  function for_option($option_id)
  {
      return $this->find('all', 
                          array(
                            'conditions' => array(
                                "target_type = 'Option'",
                                "target_id = '$option_id'")));
  }


  function getAsHtml($targetType, $targetId){
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(); args: " . print_r(func_get_args(), true), LOG_DEBUG);

        $surveyHtml = '';
        $conditions = $this->find('all',
                                array('conditions' => array(
                                    'target_type' => $targetType,
                                    'target_id' => $targetId),
                                'recursive' => -1));
        foreach($conditions as $condition){
            $surveyHtml.= "<i>Condition #" . $condition["Condition"]['id'] .
                " " . $condition["Condition"]["condition"] .
                " applies to this $targetType.</i><br/>";
        }
//        if ($surveyHtml != '') $this->log(__CLASS__ . "->" . __FUNCTION__ . "($targetType, $targetId); returning: " . $surveyHtml, LOG_DEBUG);
        return $surveyHtml;
  }




}
