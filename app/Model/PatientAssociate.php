<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class PatientAssociate extends AppModel {
    var $name = "PatientAssociate";
    var $useTable = "patients_associates";
    var $primaryKey = "id";

    var $belongsTo = array("Patient", "Associate");

    var $hasAndBelongsToMany = array("Subscale" => 
        array('className'             => "Subscale",
            'joinTable'               => "patients_associates_subscales",
            'foreignKey'              => "patient_associate_id",
            "dependent"               => true,
            'associationForeignKey'   => 'subscale_id'
        ));

    var $validate = array(
        'webkey' => array(
            //'required' => array(
            array(
                //'rule' => 'required',
                //'rule' => array('required', true),
                //'required' => true, // THIS SEEMED TO WORK AT ONE POINT...
                'rule' => 'notEmpty',
                'message' => 'webkey is required.'
            ),
            //'isUnique' => array(
            array(
                'rule' => 'isUnique',
                'message' => 'This webkey has already been taken.'
            )
            /**,
            'notEmpty' => array(
                //'rule' => 'notEmpty',
                'rule' => array('minLength', 1),
                'message' => 'This webkey cannot be empty.'
            )*/
            //,'required' => true
        ),
        'patient_id' => array(
            array(
                'rule' => 'notEmpty',
                'message' => 'Patient ID is required.'
            )
        ),
        'associate_id' => array(
            array(
                'rule' => 'notEmpty',
                'message' => 'Associate ID is required.'
            )
        )
    );

    # given Associate and Patient users, see if a patientAssociate exists.
    # If not, create it with the approrpaite data
    # If so, return false
    function createOrFalse($associate, $patient, $data) {
        #check if there is already a patient_associate record
        $patientAssociate = $this->findByPatientIdAndAssociateId(
                $patient["User"]["id"],
                $associate["User"]["id"]);

        if($patientAssociate) {
            return false;
        } else {
            return $this->createWith($associate, $patient, $data);
        }
    }


    function createWith($associate, $patient, $data) {
        $data["PatientAssociate"]["patient_id"] = $patient["Patient"]["id"];
        $data["PatientAssociate"]["associate_id"] = $associate["User"]["id"];
        $data["PatientAssociate"]["has_entered_secret_phrase"] = 0;
        $data["PatientAssociate"]["webkey"] = rand(1, 1000000000);
        $this->create($data);

        while (! $this->validates()){
            $invalidFields = $this->invalidFields();
            if (array_key_exists('webkey', $invalidFields)){
                $data["PatientAssociate"]["webkey"] = rand(1, 1000000000); 
            } else {
                return false;
            }
        }

        $this->save($data);
        $this->allowSubscales($this->getLastInsertId(), $data);
        return $data;
    }

    function forPatient($patient_id) {
        $patntAssociates = $this->find('all',
                        array(
                        'conditions' => array(
                            "Patient.id" => $patient_id),
                        //'recursive' => 2)
                        'recursive' => 1)
                        );
        foreach ($patntAssociates as $key => $patntAssociate) {
            $associate = 
                $this->Associate->findById(
                                $patntAssociate['Associate']['id']); 

            /**$this->log("associate[User][email]: " . 
                    $associate['User']['email']  . 
                    "; " . 
                    Debugger::trace(), LOG_DEBUG);
            */
            $patntAssociates[$key]['Associate']['User'] = $associate['User'];
        }
        return $patntAssociates;
    }

    function forAssociate($associate_id) {
        $patntAssociates = $this->find('all',
                        array(
                        'conditions' => array(
                            "Associate.id" => $associate_id),
                        'recursive' => 1)
                        );
        foreach ($patntAssociates as $key => $patntAssociate) {
            $patient = 
                $this->Patient->findById(
                                $patntAssociate['Patient']['id']); 

            /**$this->log("associate[User][email]: " . 
                    $associate['User']['email']  . 
                    "; " . 
                    Debugger::trace(), LOG_DEBUG);
            */
            $patntAssociates[$key]['Patient']['User'] = $patient['User'];
        }
        return $patntAssociates;
    }

    function isUserAssociateOfPatient($userId, $patientId) {
        $count = $this->find('count',
                        array(
                        'conditions' => array(
                            "Associate.id" => $userId),
                            "Patient.id" => $patientId)
                        );
        // there should only be one...
        return ($count >= 1);
    }

    function deleteBy($p_a, $patient_id) {
        $p_a = $this->getRecord($p_a);
        if((int)$p_a["PatientAssociate"]["patient_id"] == (int)$patient_id) {
            $this->delete($p_a["PatientAssociate"]["id"], false);
            return true;
        } else {
            return false;
        }
    }

    function forPatientAndAssociate($patientId, $userId) {
        return $this->find('first',
                        array(
                        'conditions' => array(
                            "associate_id" => $userId,
                            "patient_id" => $patientId))
                        );
    }

    function allowSubscales($pa_id, $data) {
        $this->PatientsAssociatesSubscale->deleteAll(
                                            array(
                                            'patient_associate_id' =>
                                            $pa_id
                                        ));
        // POST args are only passed if some checkboxes are checked
        if (array_key_exists("PatientAssociate", $data)){
            if (array_key_exists("Subscale", $data["PatientAssociate"])){
                if ($subscales = $data["PatientAssociate"]["Subscale"]){
                    $patientAssociateSubscales = array();
                    foreach($subscales as $id => $record) {
                        $patientAssociateSubscales[$id]["subscale_id"]  = $id;
                        $patientAssociateSubscales[$id]["patient_associate_id"] 
                                            = $pa_id;
                        $patientAssociateSubscales[$id]["shared"] = true;
                    }
                    $this->PatientsAssociatesSubscale->saveAll(
                                                $patientAssociateSubscales);
                }
            }
        }
    }

    function countPatientsForAssociate($associateID){
        
        $count = $this->find('count',
                        array(
                        //'fields' => 'COUNT(DISTINCT patients_associates.patient_id) as count',
                        'conditions' => array(
                            "Associate.id" => $associateID),
                        //'recursive' => 2)
                        'recursive' => 1)
                        );
        return $count;
    }
    function countForPatientJournal($patient_id) {
        $count = $this->find('count', array('conditions' =>
                        array("patient_id = $patient_id", 
                              "share_journal = 1")));
        return $count;
    }

}
