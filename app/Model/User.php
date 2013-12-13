<?php
/** 
    * User class
    *
    * Implements user login for all system users: patients, clinicians, etc.
    * DB fields: username, password
    * Currently only used for auth
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class User extends AppModel
{
    var $name = "User";
    var $useTable = 'users';
    var $hasOne = array("Patient" => array(
                            'foreignKey' => 'id', // default would be user_id
		                    'dependent' => true));
    var $hasMany = array('UserAclLeaf' => array('dependent' => true), 
                         'SurveySession' => array('dependent' => true), 
                         'Patient' => array('foreignKey' => 'consenter_id'), 
                         'Note' => array('foreignKey' => 'author_id'), 
                         'PatientViewNote' => 
                            array('foreignKey' => 'author_id'),
                         'Webkey',
    );
    var $belongsTo = array('Clinic');
    var $validate = array(
        'username' => array(
            'minLength' => array(
                'rule' => array('minLength', '2'),
                'message' => 'This username is not long enough (must be at least 2 characters).'
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This username has already been taken.'
            )
        ),
        'email' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'Another patient has that email address; please enter a different one.',
                // 'on' => 'create'
            ),
            // 'allowEmpty' => true
        ),
            // email validation seems problematic here so I'm disabling it, since we also use jquery validation on the front end.
            // 'email' => array(
                //'rule' => array('email', true), // 2nd param: attempt to verify mail server
                // 'rule' => array('email',
                            // whether to attempt to verify mail server
                            // failed for u.washington.edu !
                            // but treated others as expected...
                            // false),
                // 'allowEmpty' => true,
                // 'message' => 'This does not appear to be a valid email address.'
            // )
    );

   var $bindModels = true;


    function beforeFind($queryData){
        if ($this->bindModels){ // if bindModels hasn't been disabled for perf reasons
            if (in_array('locale_selections',
                       Configure::read('modelsInstallSpecific'))){
                $this->bindModel(
                  array('hasMany' =>
                    array(
                        'LocaleSelection' =>
                        array('className' => 'LocaleSelection',
                           'dependent' => 'true')
                    )),
                  false);
            }
        }
        return $queryData;
    }

    // Transform empty string values into nulls (to allow isUnique validation)
    function beforeValidate($options = Array()){
        foreach($this->data[$this->name] as $field => &$value){

            // When doing updates, both the new and old values are in $value array, we only need the latest
            if (is_array($value))
                $value = $value[1];

            if (trim($value) === '')
                unset($this->data[$this->name][$field]);

        }
        return true;
    }


    // User authorization methods
    // things too complicated to put in the ACL
    function canStartTicket($user_id, $ticket_id) {
        return true;
    }


    /**
    * find all UserAclLeafs with alias matching fromLeaf; change their
    *   aliases to toLeaf
    * Currently only used to switch aclPatient to aclParticipant<study group>
    * 
    * Seems like this should be in the DhairAuth component, 
    *   but components aren't accessible to models...
    */
    function swapUsersAclLeaf($userID, $fromLeaf, $toLeaf){

        //$this->log("swapUsersAclLeaf($userID, $fromLeaf, $toLeaf)", LOG_DEBUG);

        $user = $this->findById($userID);
        
        //$this->log("swapUsersAclLeaf(...), here's user: " . print_r($user, true), LOG_DEBUG);
    
        foreach ($user['UserAclLeaf'] as $userAclLeaf) {
            if ($userAclLeaf['acl_alias'] == $fromLeaf){
                $this->UserAclLeaf->id = $userAclLeaf['id'];
                $this->UserAclLeaf->saveField(
                                'acl_alias', $toLeaf);
            }
        }
    }

    /**
    *
    */
    function setPassword($userId, $hash){
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(userId: " . $userId . ", hash: " . $hash, LOG_DEBUG);
        $success = false;
        if ($userId && $hash){
            $this->id = $userId;
            $success = $this->saveField('password', $hash); 

	    if ($success) {
	        $this->saveField('change_pw_flag', 0);
            }
        }

        return $success;
    }

    function findOrCreateAssociateUserForEmail($email) {
        $user = $this->find(array("User.email" => $email));
        if(!$user) {
            return $this->createAssociateUser($email);
        } else {
            return $user;
        }
    }

    function createAssociateUser($email) {
        $user = array("User" => array("username" => $email, "email" => $email));
        $this->create($user);
        if($this->validates()) {
            $this->save($user);
            return $this->findById($this->getLastInsertId());
        } else {
            $invalidFields = $this->invalidFields();
            $errString = "";
            foreach ($invalidFields as $invalidField){
                $errString .= $invalidField . "<br>";    
            }
            $this->log("error in createAssociateUser: $errString");
            return false;
        }
    }


    /**
     * Find all staff that have access to a particular user
     * @param id User's id
     * @return Staff described, in the usual $this->data format
     */
    function getStaff($id) {
        /* users1, clinics1 and user_acl_leafs represent the staff; 
           users2 and clinics2 represent the user

           Final 'AND' clause implements the usual clinic/site logic:
              central support can see all, research staff can see all
              patients at the same site, clinical staff can see all
              patients at the same clinic
         */
        $id = intval($id);

        $queryString = "SELECT users1.id, users1.username from users as users1
            JOIN clinics as clinics1, clinics as clinics2, user_acl_leafs, 
                users as users2
            WHERE users2.id = $id
            AND clinics2.id = users2.clinic_id
            AND clinics1.id = users1.clinic_id
            AND users1.id = user_acl_leafs.user_id
            AND (user_acl_leafs.acl_alias = 'aclCentralSupport' 
            OR (user_acl_leafs.acl_alias = 'aclAdmin')
            OR (user_acl_leafs.acl_alias = 'aclResearchStaff'
                AND clinics1.site_id = clinics2.site_id)
            OR (user_acl_leafs.acl_alias = 'aclClinicStaff'
                AND users1.clinic_id = users2.clinic_id))";
        return $this->query($queryString);
    }

    /**
     * Get non-admin users
     */
    function getNonAdmin() {
        $queryString = "SELECT User.id, User.username, UserAclLeaf.acl_alias
            from users as User, user_acl_leafs as UserAclLeaf
	    WHERE User.id = UserAclLeaf.user_id"; 
        $this->Behaviors->attach('Containable');
        $results = $this->find('all', 
                                array(
                                    'contain' => 'UserAclLeaf.acl_alias',
                                    'fields' => 
                                        array('User.id', 'User.username'))); 
        //$this->log("User.getNonAdmin(), here's the search result: " . print_r($results, true), LOG_DEBUG);
        foreach ($results as $key => $result){
            foreach($result['UserAclLeaf'] as $aro){
                if ($aro['acl_alias'] == 'aclCentralSupport' ||
                    $aro['acl_alias'] == 'aclAdmin'){
                    unset($results[$key]);
                }
            }
        }
        //$this->log("User.getNonAdmin(), returning: " . print_r($results, true), LOG_DEBUG);
        return $results;
    }

    
    function getClinicStaffForClinic($clinic_id){

        // look for all users with the ClinicStaff role
        $leaves = 
            $this->UserAclLeaf->find('all', array( 
                                'conditions' => array(
                                    'UserAclLeaf.acl_alias' => 
                                        'aclClinicStaff')/**,
                                'recursive' => 2*/)); // didn't return User for some reason...
        //$this->log("getClinicStaffForClinic($clinic_id), here's leaves: " . print_r($leaves, true), LOG_DEBUG);

        $clinicStaff = array();
        foreach($leaves as $leaf){

            $user = $this->find('first', array(
                            'conditions' => array(
                                'User.id' => $leaf['UserAclLeaf']['user_id']),
                            'recursive' => -1));

            if ($user['User']['clinic_id'] == $clinic_id){
                $clinicStaff[] = $user;
            }
        }
        //$this->log("getClinicStaffForClinic($clinic_id), here's clinicStaff: " . print_r($clinicStaff, true), LOG_DEBUG);
        return $clinicStaff;
    }
}
