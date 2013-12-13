<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class JournalEntry extends AppModel {
    var $belongsTo = array('Patient');

    /** findDisplayed: array of entries to be shown to patient or associate
     */
    function findDisplayed() {
        return $this->find('all', array('conditions' => 
                                    array('display' => true)));
    }

    function displayedFor($patient_id) {
        return $this->find('all', 
                            array(
                              'conditions' => 
                                array('display' => 1, 
                                      'patient_id' => $patient_id),
                              'order' => 
                                array("date DESC")));
    }
}
?>
