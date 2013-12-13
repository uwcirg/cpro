<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Subscale extends AppModel
{
    var $name = "Subscale";
    var $useTable = "subscales";
    var $hasMany = array("Item", "SessionSubscale");
    var $belongsTo = array("Scale");


    function beforeFind($queryData){
        if (in_array("patient_associates", 
                    Configure::read('modelsInstallSpecific'))){
            $this->bindModel(array('hasAndBelongsToMany' =>
              array("PatientAssociate" => 
                array('className' => "PatientAssociate",
                        'joinTable' => "patients_associates_subscales",
                        'associationForeignKey' => "patient_associate_id",
                        'foreignKey' => 'subscale_id')
              )),
              false);
        }
        return $queryData;
    }

    function forAssociation($patient_id, $associate_id, $scale_id = null) {
        $pa = $this->PatientAssociate->forPatientAndAssociate(
            $patient_id,
            $associate_id);
        # FIXME: catch error if none found
        $pa_id = $pa["PatientAssociate"]["id"];

        $conditions = array("PatientAssociate.id = $pa_id");
        if(!is_null($scale_id)) {
            array_push($conditions, "Scale.id = $scale_id");
        }
        
        return $this->find('all', array('conditions' => $conditions));
    }

    function forScaleId($scale_id) {
        return $this->find('all', array('conditions' => array("Subscale.scale_id = $scale_id")));
    }
}
?>
