<?php
/** 
    * Associate
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    * 
*/

class Associate extends AppModel
{
    var $name = "Associate";
    var $useTable = 'associates';
  
    var $belongsTo = array("User" => array('foreignKey' => 'id'));

    var $hasAndBelongsToMany = array("Patient" =>
      array('className'             => "Patient",
          'joinTable'               => "patients_associates",
          "foreign_key"             => "associate_id",
          #"conditions"              => array("Associate.verified = true"),
          "associationForeignKey"   => "patient_id"
    ));
    
    function sForPatient($patient_id) {
        return $this->find('all', array('conditions' =>  
                                    array("Patient.id = $patient_id"))
        );
    }

    /**
    *   Overidden because we need to create a user_acl_leafs record also
    */
    function save($data = null, 
                  $validate = true, 
                  $fieldList = array()){
      
        if (parent::save($data, $validate, $fieldList)){
            $this->User->UserAclLeaf->create();
            return $this->User->UserAclLeaf->save(array(
                'user_id'=>$data['User']['id'],
                'acl_alias'=>'aclParticipantAssociate'));
        } else {
            return false;
        }
    }

    function findOrCreate($associateUser) {
        if (!isset($associateUser['Associate']['id'])){
            $this->createForUser($associateUser);
            return $this->User->findById($associateUser['User']['id']);
        } else {
            return $associateUser;
        }
    }

    function createForUser($user) {
        $user["Associate"] = array("id" => $user["User"]["id"]);
        $this->create($user);
        if($this->save($user)) {
            return true;
        } else { 
            return false;
        }
    } // this should return something...

}
