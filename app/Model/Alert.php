<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Alert extends AppModel {

    /**
     * returns string if alert is triggered and should be shown,
     * else returns false
     */
    function evaluateForPageAndSession($page_id, $session_id) {
        if($alert = $this->findByPageId($page_id)) {
            return $this->evaluateForSession($alert, $session_id);
        }
        return false;
    }

    /** returns string of alert, or false if not triggered */
    function evaluateForSession($alert, $session_id) {
        # find target and calculate
        
        # compare with value and return message
    } 

    function getListOfPagesWAlerts(){
        $alerts = $this->find('all');
        return Set::combine($alerts, '{n}.Alert.page_id', '{n}.Alert.page_id');
    }
}

?>
