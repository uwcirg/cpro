<?php
/** 
    * Option class
    *
    * Models a single option in the survey
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Option extends AppModel
{
  var $name = "Option";
  var $useTable = 'options';
  
  var $belongsTo = array('Question');

  function for_question($question_id)
  {
    return $this->find('all', 
                    array('conditions' => 
                      array("question_id = $question_id",
                            "Option.Sequence    > 0")));
  }
  function afterFind($results, $primary = false){
        return $this->loadTranslations($results,$this->name);
    }  
}
