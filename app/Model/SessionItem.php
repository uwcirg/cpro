<?php
/*
    * SessionItem class
    * Models a single item in a single SurveySession
    * and the calculated value for that item and session.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

class SessionItem extends AppModel
{
	var $name = "SessionItem";
	var $useTable = 'session_items';

	var $belongsTo = array("Item", "SurveySession", "SessionSubscale");

    function createWith($item_id, $subscale_id, $session_id, $value) {
        $this->DeleteAll(array("item_id" => $item_id,
                               "SessionItem.survey_session_id" => $session_id));

        return $this->save($this->create(
                      array("item_id" => $item_id,
                            "survey_session_id" => $session_id,
                            "subscale_id" => $subscale_id,
                            "value" => $value)));
    }


    /**
     *  Consider making fieldArray & containArray params to accomodate other special cases
     */
    function getBySurveySessionAndSubscaleId($session_id, $subscale_id){

        $this->Behaviors->attach('Containable');

        $fieldArray = array('SessionItem.id', 'SessionItem.value'); 
        $containArray = array('Item.id', 'Item.name', 'Item.question_id', 
                                // 'sequence' because 'order' field ignored here
                                'Item.sequence'); 
        //$order = 'Item.sequence';
        $order = array('SessionItem.value DESC', 'Item.sequence');

        $sessionItems = $this->find('all', array(
                                    'conditions' => array(
                                        'SessionItem.survey_session_id' => 
                                            $session_id,
                                        'SessionItem.subscale_id' => 
                                            $subscale_id),
                                    'fields' => $fieldArray,
                                    'contain' => $containArray,
                                    'order' => $order
));
        //$this->log("getBySessionSubscaleId($session_id, $subscale_id), returning: " . print_r($sessionItems, true), LOG_DEBUG);
        return $sessionItems;
    }
}
