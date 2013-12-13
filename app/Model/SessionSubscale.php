<?php
/*
    * SessionSubscale class
    * Models a single subscale's value for one survey session
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

class SessionSubscale extends AppModel
{
    var $name = "SessionSubscale";
    var $useTable = "session_subscales";
    
    var $belongsTo = array("Subscale", "SessionScale", "SurveySession");
    var $hasMany = array("SessionItem");


    /** @function data: returns an array of subscale data
     * for a given patient that should be showed to a given user
     * @arg $subscale: id or array of subscale we are viewing
     * @arg $patient_id: id of patient whose data is being viewed
     * @arg $associate_id: id of user who is trying to view the data
     */
    function data($subscale, $patient_id, $associate_id = null) {
        if(is_null($associate_id)) { // by default, shown to patient
            $associate_id = $patient_id;
        }

        if($patient_id == $associate_id) {
            # array of subscales in the scale
            $subscale = $this->Subscale->getRecord($subscale);
            $subscales = $this->Subscale->forScaleId(
                $subscale["Subscale"]["scale_id"]);
        } else {
            # filter array by permissions
            $subscale = $this->Subscale->getRecord($subscale);
            $scale_id = $subscale["Subscale"]["scale_id"];
            $subscales = $this->Subscale->
                forAssociation($patient_id, $associate_id, $scale_id);
        }

        return $subscales;
        # fill in values for each subscale and session
    }

    /**
     *
     */
    function sforSubscaleAndPatient($subscale_id, $patient_id) {
	return $this->find('all', array('conditions' =>  
        array("SessionSubscale.subscale_id = $subscale_id",
              //"Subscale.order > 0", 
              "SessionSubscale.patient_id = $patient_id")));
    }


    /**
     *  Get all reportable SessionSubscales that correspond to a particular
     *      patient id and scale(s)
     *  @param scale_ids a scale ID, or an array of scale IDs
     *  @param other params like reportablesForSubscaleAndPatient
     */
    function reportablesForScalesAndPatient(
                $scale_ids, $patient_id, $sessionIdList = null, 
                $requirePartialFinalization = true,
                $include_session_items = false){

//        $this->log(__CLASS__ . '=>' . __FUNCTION__ . '(... patient_id: ' . $patient_id . "; sessionIdList: " . print_r($sessionIdList, true) . ")", LOG_DEBUG);
        // FIXME order by scales.order, and then subscales.order (rename both 'order' fields to 'sequence' so they can be properly contained?)

        $subscales = $this->Subscale->find(
                'all',
                array(
                    'conditions' => array(
                        'Subscale.scale_id' => $scale_ids),
                    'fields' => array('Subscale.id', 'Subscale.name'),
                    'recursive' => -1 
        ));
        //$this->log('subscales: ' . print_r($subscales, true), LOG_DEBUG);

        $subscale_ids = array();
        foreach($subscales as $key => $subscale){
            $subscale_ids[] = $subscale['Subscale']['id'];
        }

        $reportables = $this->reportablesForSubscaleAndPatient(
                                $subscale_ids, $patient_id, $sessionIdList, 
                                $requirePartialFinalization,
                                $include_session_items);
//        $this->log('reportablesForScalesAndPatient returning: ' . print_r($reportables, true), LOG_DEBUG);
        return $reportables;
    }


    /**
      Get all reportable SessionSubscales that correspond to a particular
      subscale and patient id
      @param subscaleId ID of the subscale, or an array of subscale ID's
      @param patientId ID of the patient
      @param sessionIdList search should be limited to only these sessions. 
      @param requirePartialFinalization Results limited to sessions which have been partially finalized. Rules about whether a particular subscale requires session to be 'finished' (ie beyond 'partial_finalization') are not applied here
      @return array of all corresponding sessions
     */
    function reportablesForSubscaleAndPatient(
                $subscale_id, $patient_id, $sessionIdList = null,
                $requirePartialFinalization = true,
                $include_session_items = false) {
//        $this->log(__CLASS__ . '=>' . __FUNCTION__ . "($subscale_id, ..., sessionIdList : " . print_r($sessionIdList, true), LOG_DEBUG);
        
        $searchArray = array("SessionSubscale.subscale_id" => $subscale_id,
                    "SessionSubscale.patient_id = $patient_id");
        if ($requirePartialFinalization == true) {
		    $searchArray[] = "SurveySession.partial_finalization = 1";
        }
	    if ($sessionIdList == null){
            $result = $this->find('all', 
                                array('conditions' => $searchArray));
        }
        else {
            if (sizeof($sessionIdList) == 1){
                $searchArray['SurveySession.id'] = $sessionIdList[0]; 
            }
            else{
                $sessionSearchArray = array();
                foreach($sessionIdList as $sessionId){
                    $sessionSearchArray[] = 'SurveySession.id = ' . $sessionId; 
                }
                $searchArray['OR'] = $sessionSearchArray; 
            }
            //$this->logArrayContents($searchArray, "searchArray");
            $result = $this->find('all', 
                                array('conditions' => $searchArray)
                                );
        }
        //$this->log("reportablesForSubscaleAndPatient($subscale_id, ...), will iterate and populate results next: " . print_r($result, true), LOG_DEBUG);

        if ($include_session_items === true){
            foreach ($result as $key => $sessionSub){
                $result[$key]['SessionItems'] = 
                    $this->SessionItem->getBySurveySessionAndSubscaleId(
                        $sessionSub['SessionSubscale']['survey_session_id'],
                        $sessionSub['SessionSubscale']['subscale_id']);
            }
        }

//        $this->log(__CLASS__ . "=>" . __FUNCTION__ . "($subscale_id, ...), returning " . print_r($result, true), LOG_DEBUG);
        return $result;
    }

    /**
     *
     */
    function createWith($subscale_id, $session_scale_id, $session_id, $patient_id, $value) {
        $this->deleteAll(array("SessionSubscale.survey_session_id" => $session_id,
                                "SessionSubscale.subscale_id" => $subscale_id));

        return $this->save($this->create(array(
                    "survey_session_id" => $session_id,
                    "subscale_id"       => $subscale_id,
                    "session_scale_id"  => $session_scale_id,
                    "patient_id"        => $patient_id,
                    "value"             => $value)));
    }

    /**
     *
     */
    function criticalForSession($session_id) {
      return $this->find(
            'all', 
            array('conditions' => 
                array("SessionSubscale.survey_session_id = $session_id",
                      "OR" => // ie and one or more ofthe following must be true
                        array(
                            array("SessionSubscale.value >= Subscale.critical",
                                "Subscale.invert" => 0),
                            array("SessionSubscale.value <= Subscale.critical",
                                "Subscale.invert" => 1)))));

    }
}
