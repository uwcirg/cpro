<?php
/** 
    * Note class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Note extends AppModel
{
    var $name = "Note";
    var $useTable = 'notes';
    var $belongsTo = array('Patient', 
                           'User' => array('className' => 'User',
                                           'foreignKey' => 'author_id'
        )
    ); 

    // change times to GMT before save
    // function beforeSave($options = Array()) {
        // if (!empty($this->data['Note']) &&
	    // !empty($this->data['Note']['patient_id']))
        // {
            // $timezone =
	        // $this->User->getTimeZone($this->data['Note']['patient_id']);

            // if (!empty($this->data['Note']['created'])) {
                // $this->data['Note']['created'] =
	            // $this->localToGmt($this->data['Note']['created'],
		                      // $timezone);
            // }
        // }

        // return true;
    // }

    // change T-times back to local time after save
    function afterSave($created) {
        if (!empty($this->data['Note']) &&
	    !empty($this->data['Note']['patient_id'])) 
        {
            $timezone = 
	        $this->User->getTimeZone($this->data['Note']['patient_id']);

            if (!empty($this->data['Note']['created'])) {
                $this->data['Note']['created'] = 
	            $this->gmtToLocal($this->data['Note']['created'], 
		                      $timezone);
            }
        }

        return true;
    }
    
    // change T-times to local time after retrieved
    function afterFind($results, $primary = false) {
        foreach ($results as $key => $val) {
	    /* If there is no patient id (e.g., find('count')),
	       timezone conversion is irrelevant */
	    if (!empty($val['Note']) && !empty($val['Note']['patient_id'])) {
                $timezone = 
		    $this->User->getTimeZone($val['Note']['patient_id']);

       	        if (isset($val['Note']['created'])) {
	            $results[$key]['Note']['created'] = 
		        $this->gmtToLocal($val['Note']['created'], $timezone);
	        }
            }
        }

        return $results;
    }
}
