<?php
/** 
    * Consent class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *   
    * Consent is just a dummy table that helps synchronize the 4-block 
    *   randomization algorithm 
*/
class Consent extends AppModel
{
    var $name = "Consent";
    var $useTable = 'consents';
    var $belongsTo = array('Patient');

    /**
     * Get the highest numbered id in the table
     * @return the highest numbered id
     */
    function getLastId() {
        $lastRecord = 
	    $this->find('first', array('order' => array('Consent.id DESC')));

	if (empty($lastRecord)) {
	    return 0;
	} else {
            return $lastRecord['Consent']['id'];
        }
    }

    /**
     * Get the last 3 consented patients of a particular user type
     * @param userType The user type we are interested in
     * @return The last 3 consented patients, as an array, last to first
     */
    function getLastThreeConsents($userType) {
        $result = $this->find('all', array(
            'conditions' => array('Patient.user_type' => $userType),
            'order' => array('Consent.id DESC')));

        if (count($result) < 3) {
            return $result;
        } else {
            return array_slice($result, 0, 3);
        }
    }
}
