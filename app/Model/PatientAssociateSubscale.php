<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class PatientAssociateSubscale extends AppModel {
    var $useTable = "patients_associates_subscales";

    var $belongsTo = array("Subscale", "PatientAssociate");

    function sharedSubscales($patient_associate_id) {
        return $this->find('all', array("patient_associate_id = $patient_associate_id"));
    }
    /**
    *   returns array of subscale IDs which are shared to this patientAssociate
    */
    function getListOfSharedSubscales($patientAssociateId){

        $pASubscales = $this->find('all',
                        array(
                        'conditions' => array(
                            "patient_associate_id" => $patientAssociateId)));

        $sharedSubscaleArray = array();

        foreach($pASubscales as $pASubscale){
            if ($pASubscale['PatientAssociateSubscale']['shared'] == 1){
                $sharedSubscaleArray[] = $pASubscale['Subscale']['id'];
            }
        }

        /**ob_start();
        var_dump($sharedSubscaleArray);
        $debugStr = ob_get_contents();
        ob_end_clean();
        $this->log("PAS.getListOfSharedSubscales(" . $patientAssociateId . 
                ") = " . $debugStr . "; "  
                . Debugger::trace(), LOG_DEBUG);*/

        return $sharedSubscaleArray;
    }

    function countForPatientAndSubscale($patient_id, $subscale_id) {
        return $this->find(array("PatientAssociate.patient_id = $patient_id",
                                 "PatientAssociateSubscale.subscale_id = $subscale_id",
                                 "PatientAssociateSubscale.shared IS TRUE"),
                          "COUNT(PatientAssociate.id) as count");
    }

    /**
     * Delete all the records for a particular patient
     * @param patientId Id of the patient
     */
    function deleteForPatient($patientId) {
        $this->deleteAll(array("PatientAssociate.patient_id = $patientId"));
    }
}

?>
