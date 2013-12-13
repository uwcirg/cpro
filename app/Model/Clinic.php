<?php
/** 
    * Clinic class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Clinic extends AppModel
{
    var $name = "Clinic";
    var $useTable = 'clinics';
    var $hasMany = array('User');
    var $belongsTo = array('Site');

    /**
     * Check whether two clinic ids are from the same site
     * @param $id1 First id
     * @param $id2 Second id
     * return true if the ids are from the same site
     */
    function sameSite($id1, $id2) {
        $clinic1 = $this->findById($id1);
        $clinic2 = $this->findById($id2);

	return !empty($clinic1) && !empty($clinic2) && 
	       $clinic1['Clinic']['site_id'] == $clinic2['Clinic']['site_id'];
    }

    /**
     * Get the timezone string for a clinic
     * @param id clinic id
     * @return the timezone string
     */
    function getTimeZone($id) {
        $clinic = $this->findById($id);
	return $clinic['Site']['timezone'];
    }
}
