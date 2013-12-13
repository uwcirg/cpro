<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Scale extends AppModel
{
	var $name = "Scale";
	var $useTable = "scales";
    var $hasMany = array("SessionScale", 
                            "Subscale" => 
                                array('order' => 'Subscale.order ASC'));
    var $actsAs = array ('Containable');

    var $inclActivityDiary;
    var $excludeArray;

	/** Performs a raw sql query for performance, then parses the results for what we want
     * raw sql, so does not return data for models in the hasMany list
	 * @param $projId project id to find scales in
    *   @param $limitToOrderGreaterThan0 only retrieve those that are displayed
    *           in patient-viewable pages  
	 * @return array of scale id's and names */
	function sForProject($projId, $limitToOrderGreaterThan0 = true) {
		$projId = intval($projId);

        $sqlWhereMod = '';

        if ($limitToOrderGreaterThan0 === true){
            $sqlWhereMod .= 'AND scales.order > 0 ';
        }

        if (isset($this->excludeArray)){
            $sqlWhereMod .= 'AND scales.id NOT IN (';
            foreach($this->excludeArray as $scaleId){
                $sqlWhereMod .= $scaleId . ',';
            }
            $sqlWhereMod = substr($sqlWhereMod, 0, -1); // remove last ','
            $sqlWhereMod .= ') ';
        }

		$raw_scales = $this->query(
         "SELECT scales.id, scales.name
          FROM scales
          JOIN questionnaires 
            ON scales.questionnaire_id = questionnaires.id
          JOIN projects_questionnaires 
            ON questionnaires.id = projects_questionnaires.questionnaire_id
          JOIN projects on projects.id = projects_questionnaires.project_id
          WHERE project_id = $projId " .
          $sqlWhereMod . 
          "ORDER BY scales.order");
		$scales = array();

        $activityDiaryScaleKeyIncrement = 0;
        if ($this->inclActivityDiary) {
            if (in_array('activity_diary_entries',
                    Configure::read('modelsInstallSpecific'))){

                //$this->Scales->bindModel(ActivityDiaryEntries);

                // add ActivityDiaryEntries "scale"
                $activityDiaryScaleKeyIncrement += 1;

                $activityDiaryScale = array(
                  'id' => NON_FXNL_SCALE_ID,
                  'name' => 'Activity Diary Entries',
                  'critical' => 0);

                $scales[0] = $activityDiaryScale;
            }
        }

		foreach($raw_scales as $key => $raw_scale) {
            $scales[$key + $activityDiaryScaleKeyIncrement] = 
                                                    $raw_scale["scales"];
		}
	    //$this->log("scale.sForProject(), returning scales for $projId w/ limitToOrderGreaterThan0 == $limitToOrderGreaterThan0 : " . print_r($scales, true), LOG_DEBUG);
        return $scales;
	}

    /**
    * Returns Scales w/ member Subscales 
    *   @param $limitToOrderGreaterThan0 only retrieve those that are displayed
    *           in patient-viewable pages  
    */
	function withSubscales($scale, $limitToOrderGreaterThan0 = true) {
		$id = $scale["id"];
        //$this->log("scales.withSubscales(), here are params: " . print_r(func_get_args(), true), LOG_DEBUG);

        // add ActivityDiaryEntries scale & subscales if need be
        // assumes that we always want it displayed first
        // TODO MOVE THIS TO A FXN IN ActivityDiaryEntries MODEL, BUT NOT SURE HOW TO REFERENCE IT FROM HERE
        if (($id == NON_FXNL_SCALE_ID) && $this->inclActivityDiary){
          //return $this->ActivityDiaryEntries->withSubscales();
          $activityDiaryScale = 
            array(
              'Scale' =>
                array(
                  'id' => NON_FXNL_SCALE_ID,
                  'name' => 'Activity Diary Entries',
                  'critical' => 0),
              'Subscale' => array(
                    0 => array('name' => 'FINS Fatigue',
                                'id' => 'fatigue',
                                'base' => '0',
                                'range' => '10',
                                'critical' => '10'),
                    1 => array('name' => 'Pedometer Steps',
                                'id' => 'steps',
                                'base' => '0',
                                'range' => '10000',
                                'critical' => '10000'),
                    2 => array('name' => 'Minutes of Exercise',
                                'id' => 'minutes',
                                'base' => '0',
                                'range' => '180',
                                'critical' => '180')));
            return $activityDiaryScale;
        }

        // "contain" makes the find retrieve only Scale and Subscale data (w/out this, SessionScale would be retrieved also)
        if ($limitToOrderGreaterThan0 === true){
            $this->contain('Subscale.order > 0');
        }
        else {
           $this->contain('Subscale');
        }
        $sWSubscales = $this->findById($id);
        //$this->log("scale.withSubscales() : sWSubscales for $id w/ limitToOrderGreaterThan0 == $limitToOrderGreaterThan0 : " . print_r($sWSubscales, true), LOG_DEBUG);
		return $sWSubscales;
	}

	/**
    * Returns a Project's Scales w/ their member Subscales
    *   @param $limitToOrderGreaterThan0 only retrieve those that are displayed
    *           in patient-viewable pages 
    *   @param $inclActivityDiary whether to include the Activity Diary pseudo-scale 
    */
    function sAndSubscalesForProject($projId, 
                                        $limitToOrderGreaterThan0 = true, 
                                        $inclActivityDiary = false, 
                                        $excludeArray = null){
		
        $this->inclActivityDiary = $inclActivityDiary;
        $this->excludeArray = $excludeArray;

        $scales = $this->sForProject($projId, $limitToOrderGreaterThan0);

        $arrayMap = array_map(
            array("Scale", "withSubscales"),
            $scales,
            // create an empty array the length of $scales, each elem containing $limitToOrderGreaterThan0
            repeat(count($scales), $limitToOrderGreaterThan0)/**,
            repeat(count($scales), $inclActivityDiary)*/);
        //$this->log("sAndSubscalesForProject($projId, $limitToOrderGreaterThan0, $inclActivityDiary), returning: " . print_r($arrayMap, true), LOG_DEBUG);
		return $arrayMap;
	}

	function withSessionValue($scale, $session_id) {
		$scale_id = $scale[0]["Scale"]["id"];
		return $this->SessionScale->forScaleAndSession($scale_id, $session_id);
    }
}

function repeat($n, $item) {
    return array_pad(array(), $n, $item);
}
?>
