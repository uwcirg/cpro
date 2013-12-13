<?php
/** 
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * Sets session var factorsForPatient
    *   An ordered non-recursive cake model array of the P3pTeachings to be presented to this patient in the factors tab. Each elem includes the label and subscale_id virtual fields, along w/ regular fields.
    * Sets session var factorSubscalesToDisplayForPatient
    *   An ordered non-recursive cake model array of the P3pTeaching subscales (eg Outcomes, People, Current Symptoms) to be presented to this patient in the factors tab. 
    *    
    *
*/

class InfluenceReaderComponent extends Component
{
    var $components = array("Session");


    function __construct(ComponentCollection $collection, $settings = array()) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...)", LOG_DEBUG);

        parent::__construct($collection, $settings);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...), done", LOG_DEBUG);
    }


    //called before Controller::beforeFilter()
    function initialize($controller) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...)", LOG_DEBUG);

        // saving the controller reference for later use
        $this->controller = $controller;

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), will call parent::initialize next.", LOG_DEBUG);
        parent::initialize($controller);
    }
 

    /**
     *  Every factor has one unique item
     *  "factor groups" are implemented as subscales
     */
    function readFactorsAndFilterByAnswer(
                                    $patientId, 
                                    $reportableSessionId){

        $factors = array(); // cakephp model data
        // ordered cakephp model array of only those factorSubscales to display
        // eg [0] => Array ([id] => 46, [name] => Outcomes, etc),
        //    [1] => Array ([id] => 48, [name] => People, etc)
        $factorSubscalesToDisplay = array(); // TODO remove this so stateless

        if ($this->Session->check(
                    'factorsForPatient-' . $patientId )
                && $this->Session->check(
                    'factorSubscalesToDisplayForPatient-' . $patientId )
                && Configure::read('isProduction') === true
            ){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), reading factors and factorSubscalesToDisplay from session var", LOG_DEBUG);
            $factors = $this->Session->read('factorsForPatient-' . 
                                                            $patientId);
            $factorSubscalesToDisplay = 
                $this->Session->read('factorSubscalesToDisplayForPatient-' . 
                                                            $patientId);
        }
        else {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), neither factors nor factorSubscalesToDisplay were previously set in session var, so doing full calculation", LOG_DEBUG);

            // components aren't intended to use models, so we need to do:
            $instanceSessionSubscale = ClassRegistry::init('SessionSubscale');

            $sessionSubscales = 
                    $instanceSessionSubscale->reportablesForScalesAndPatient(
                array(INFLUENTIAL_FACTORS_PERSONAL_PROFILE_SCALE, 
                        INFLUENTIAL_FACTORS_SYMPTOMS_SCALE), 
                $patientId, array($reportableSessionId), true, true);
            // note: $sessionSubscales now ordered correctly simply because their id's are in the desired sequence - instead, sort by scale and subscale order within the above call (see FIXME there); items within each subscale are (correctly) ordered by SessionItem.value DESC then Item.sequence (no fix needed for this part)
//            $this->log(__CLASS__ . "." . __FUNCTION__ . '(), sessionSubscales: ' . print_r($sessionSubscales, true), LOG_DEBUG);

            $orderedItemIdsToInclude = array();
            $subscaleNamesIndexedById = array();
            foreach ($sessionSubscales as $subKey => $sessionSubscale) {

                $subscaleName = $sessionSubscale['Subscale']['name'];
                $critical = $sessionSubscale['Subscale']['critical'];
                $subscaleNamesIndexedById[$sessionSubscale['Subscale']['id']] = 
                    $sessionSubscale['Subscale']['name'];

                foreach ($sessionSubscale['SessionItems'] as 
                                        $key => $sessionItemPair){

                    $name = $sessionItemPair['Item']['name'];
                    $val = $sessionItemPair['SessionItem']['value'];
                    if ($val >= $critical){

                        if (!in_array($sessionSubscale['Subscale'], 
                                            $factorSubscalesToDisplay)){
                            $factorSubscalesToDisplay[] = 
                                                $sessionSubscale['Subscale'];
                        }

                        $orderedItemIdsToInclude[] = 
                                        $sessionItemPair['Item']['id']; 
                    }
                }
            }
//            $this->log(__CLASS__ . "." . __FUNCTION__ . '(), factorSubscalesToDisplay: ' . print_r($factorSubscalesToDisplay, true), LOG_DEBUG);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . '(), orderedItemIdsToInclude: ' . print_r($orderedItemIdsToInclude, true), LOG_DEBUG);
            $instanceP3pTeaching = 
                ClassRegistry::init('P3pTeaching');
            $instanceP3pTeaching->Behaviors->attach('Containable');
            $factorsUnfiltered = $instanceP3pTeaching->find(
                'all',
                array(
                    'conditions' => array('P3pTeaching.action' => 'factors'),
                    //'recursive' => -1,
                    'recursive' => 0,
                    'contain' => array('Item'),
                    //'contain' => array('P3pTeaching', 'Item'),
                    'fields' => array(
                        'P3pTeaching.id',
                        // 'P3pTeaching.item_name'  /*TODO once item name table is translated, append . $this->Session->read('Config.i18nPostfix')*/, 
                        'P3pTeaching.intervention_text' . $this->Session->read('Config.i18nPostfix'),
                        'P3pTeaching.video',
                        // 'P3pTeaching.item_id', 
                        'Item.subscale_id',
                        'Item.name' . $this->Session->read("Config.i18nPostfix")
                    )
                ));
//            $this->log(__CLASS__ . "." . __FUNCTION__ . '(), factorsUnfiltered: ' . print_r($factorsUnfiltered, true), LOG_DEBUG);
            //$this->log('p3p/factors(), factorsUnfiltered: ' . print_r($factorsUnfiltered[0]['Item'], true), LOG_DEBUG);
            // $this->log('subscaleNamesIndexedById: ' . print_r($subscaleNamesIndexedById, true), LOG_DEBUG);

            foreach($orderedItemIdsToInclude as $key => $item_id){
                foreach($factorsUnfiltered as $factor){
//                    $this->log('item-id:' . print_r($factor['Item']['id'].','.$item_id.','.$key, true), LOG_DEBUG);
                    if ($factor['Item']['id'] == $item_id){
                        $factors[$key] = $factor;
                        // add subscale name to factors
                        $factors[$key]['Item']['subscale_name'] = 
                            $subscaleNamesIndexedById[$factors[$key]['Item']['subscale_id']];
                    }
                }
            }
//            $this->log(__CLASS__ . "." . __FUNCTION__ . '(), factors, post-processing (will Session->write these next): ' . print_r($factors, true), LOG_DEBUG);

            $this->Session->write('factorsForPatient-' . $patientId, 
                                    $factors);
//            $this->log('factorSubscalesToDisplay, post-processing (will Session->write these next): ' . print_r($factorSubscalesToDisplay, true), LOG_DEBUG);
            $this->Session->write(
                'factorSubscalesToDisplayForPatient-' . $patientId, 
                $factorSubscalesToDisplay);
        }// ELSE on if ($this->Session->check(

//        $this->log(__CLASS__ . "." . __FUNCTION__ . '(), end.', LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . '(), end, set-ting factors: ' . print_r($factors, true), LOG_DEBUG);
        $this->controller->set('factors', $factors);

    }// private function readFactorsAndFilterByAnswer(){


}

