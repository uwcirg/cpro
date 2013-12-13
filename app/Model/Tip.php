<?php
/** 
    * Teaching Tip class
    * Models a teaching tip
    * The returned 'text' field may contain substitutions 
    * that can only be done after knowing the patient's id.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Tip extends AppModel
{
    var $name = "TeachingTips";
    var $useTable = 'teaching_tips';
  
    var $belongsTo = array("Subscale");
    var $hasMany = array("TeachingTipsPercentages");

    /** given an associative array of scales and their subscales,
     *  add the TeachingTip information to each subscale 
     *  If the scale doesn't have subscales w/ teaching tips, 
     *      remove that scale from the array
    */
    function forScalesAndPatient(&$scales, $patient_id) {
        foreach($scales as $key => &$scale) {
            foreach($scale["Subscale"] as $keyOfSubscale => &$subscale) {
                $this->forSubscaleAndPatient($subscale, $patient_id);
                if ($subscale == null) {
                    unset($scale["Subscale"][$keyOfSubscale]);
                }
            }
            if (sizeof($scale["Subscale"]) == 0){
                unset($scales[$key]);
            }
        }
        return $scales;
    }

    function forSubscaleAndPatient(&$subscale, $patient_id) {
        $tip = $this->find('first', 
                        array('conditions' => 
                            array('subscale_id' => $subscale['id'])));
        if (!$tip) {
            $subscale = null;
            return;
        }

        $subscale["TeachingTip"] = $tip["Tip"];
        $tip_id = $tip["Tip"]["id"];

        $user_model = new User;
        $user = $user_model->findById($patient_id);
        $timezone = $user_model->getTimeZone($patient_id);
        $clinic_id = $user["User"]["clinic_id"];

        $afterTreatment;
        if (Configure::read('postTreatment')){
            $afterTreatment = true;
        }
        else {
            if($user["Patient"]["treatment_start_date"] != "") {
                $afterTreatment = 
                    $this->secondsAfterNow(
                        $user["Patient"]["treatment_start_date"], $timezone) 
                    < 0;
            } else {
                $afterTreatment = false; # default to pre-treatment if it isn't set
            }
        }

        // replace templates with patient-specific text
        if($afterTreatment) {
            $status = "after";
        } else {
            $status = "before";
        }

        $percentages = 
            $this->TeachingTipsPercentages->find(
                'first', array('conditions' => 
                array("teaching_tip_id = $tip_id",
                        "after_treatment != " . (int)$afterTreatment,
                        "clinic_id = $clinic_id")));
        if (isset($percentages['TeachingTipsPercentages'])){
            $percentage = 
                $percentages["TeachingTipsPercentages"]["percentage"] . "%";
            $search  = array("[XX%]", "[before/after]");
            $replace = array($percentage, $status); 
            $subscale["TeachingTip"]["text"] = 
                str_replace($search, $replace, $subscale["TeachingTip"]["text"]);
        }
    }

}
