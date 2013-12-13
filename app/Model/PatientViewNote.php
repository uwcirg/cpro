<?php
/** 
    * Patient View Note class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class PatientViewNote extends AppModel
{
    var $name = "PatientViewNote";
    var $useTable = 'patient_view_notes';
    var $belongsTo = array('Patient', 
                           'User' => array('className' => 'User',
                                           'foreignKey' => 'author_id'
        )
    ); 

    // change times to GMT before save
    function beforeSave($options = Array()) {
        $timezone = $this->User->getTimeZone(
	    $this->data['PatientViewNote']['patient_id']);

        if (!empty($this->data['PatientViewNote']['lastmod'])) {
            $this->data['PatientViewNote']['lastmod'] = 
	        $this->localToGmt($this->data['PatientViewNote']['lastmod'], 
		                  $timezone);
        }

        return true;
    }

    // change T-times back to local time after save
    function afterSave($created) {
        $timezone = $this->User->getTimeZone(
	    $this->data['PatientViewNote']['patient_id']);

        if (!empty($this->data['PatientViewNote']['lastmod'])) {
            $this->data['PatientViewNote']['lastmod'] = 
	        $this->gmtToLocal($this->data['PatientViewNote']['lastmod'], 
		                  $timezone);
        }

        return true;
    }
    
    // change T-times to local time after retrieved
    function afterFind($results, $primary = false) {
        foreach ($results as $key => $val) {
	    /* If there is no patient id (e.g., find('count')),
	       timezone conversion is irrelevant */
	    if (!empty($val['PatientViewNote']) && 
	        !empty($val['PatientViewNote']['patient_id'])) 
            {
                $timezone = $this->User->getTimeZone(
		    $val['PatientViewNote']['patient_id']);

       	        if (isset($val['PatientViewNote']['lastmod'])) {
	            $results[$key]['PatientViewNote']['lastmod'] = 
		        $this->gmtToLocal($val['PatientViewNote']['lastmod'], 
			                  $timezone);
	        }
            }
        }

        return $results;
    }
}
