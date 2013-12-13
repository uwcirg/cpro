<?php 
/*
    * SessionScale class
    * Models a single scale and its value 
    * for one SurveySession
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

class SessionScale extends AppModel
{
	var $name = "SessionScale";
	var $useTable = 'session_scales';

	var $belongsTo = array("SurveySession", "Scale");
	var $hasMany = array("SessionSubscale");

	function forScaleAndSession($scale_id, $session_id) {
		return $this->find(array("SessionScale.scale_id = $scale_id",
								 "SessionScale.survey_session_id = $session_id"));
	}

    function createWith($session_id, $scale_id, &$id) {
        $this->DeleteAll(array("survey_session_id" => $session_id,
                        "SessionScale.scale_id" => $scale_id));

        $session_scale = $this->save($this->create(
            array("scale_id" => $scale_id,
            "survey_session_id" => $session_id)));

        $id = $this->id;
        return $session_scale;
    }

}
