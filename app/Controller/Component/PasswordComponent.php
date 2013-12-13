<?php
/**
    * Password component
    *
    * Helps enforce our password policy
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class PasswordComponent extends Component
{
    var $components = array('DhairAuth', 'Auth');

    /* constants for our password policy */
    /** The minimum length of a Staff user's password */
    const STAFF_MIN_PASSWORD_LENGTH = 8;
    /** The minimum length of a non-staff user's password */
    const NONSTAFF_MIN_PASSWORD_LENGTH = 6;

    /** The minimum number of distinct character groups (uppercase letters,
        lowercase letters, digits, other chars) in a staff user's password */
    const STAFF_MIN_PASSWORD_CHARGROUPS = 3;
    /** The minimum number of distinct character groups in a non-staff user's 
        password */
    const NONSTAFF_MIN_PASSWORD_CHARGROUPS = 2;
 
    /** startup: called by cakePHP automatically when included into a controller
     * @ param AppController: current controller 
     */
    function startup(Controller $controller)
    {
    	$this->controller = $controller;
    }

    /**
      Return whether a string has a lowercase letter
      @param $str The string
      @return 1 if the string contains a lowercase letter, 0 otherwise
     */
    function hasLowercase($str) {
        $lowerPattern = '/[a-z]/';
	return preg_match($lowerPattern, $str);
    }
    
    /**
      Return whether a string has a uppercase letter
      @param $str The string
      @return 1 if the string contains a uppercase letter, 0 otherwise
     */
    function hasUppercase($str) {
        $upperPattern = '/[A-Z]/';
	return preg_match($upperPattern, $str);
    }
    
    /**
      Return whether a string has a digit
      @param $str The string
      @return 1 if the string contains a digit, 0 otherwise
     */
    function hasDigit($str) {
        $digitPattern = '/[0-9]/';
	return preg_match($digitPattern, $str);
    }
    
    /**
      Return whether a string contains a non-alphanumeric character
      @param $str The string
      @return 1 if the string contains a non-alphanumeric character, 0 otherwise
     */
    function hasNonalphanumeric($str) {
        $otherPattern = '/[^0-9a-zA-Z]/';
	return preg_match($otherPattern, $str);
    }
    
    /**
      Return the number of different character groups in a string.
      The groups are:  lowercase letters, uppercase letters, digits,
         all other characters
      @param $str The string
      @return The number of groups in the string (0-4)
     */
    private function charGroups($str) {

	return $this->hasLowercase($str) + $this->hasUppercase($str) + 
	       $this->hasDigit($str) + $this->hasNonalphanumeric($str);
    }

    /**
      * Get the minimum length of the user's password
      * @param isAssociate Is the user a participant associate?
      * @return the minimum length of the current user's password
     */
    function minPasswordLength($isAssociate) {
        if ($isAssociate || $this->controller->is_staff) {
            return self::STAFF_MIN_PASSWORD_LENGTH;
        } else {
	    return self::NONSTAFF_MIN_PASSWORD_LENGTH;
	}
    }

    /**
      * Get the minimum number of character groups for the user's password
      * @param isAssociate Is the user a participant associate?
      * @return the minimum length of character groups for the current 
      * user's password
     */
    function minCharGroups($isAssociate) {
        if ($isAssociate || $this->controller->is_staff) {
	    return self::STAFF_MIN_PASSWORD_CHARGROUPS;
        } else {
            return self::NONSTAFF_MIN_PASSWORD_CHARGROUPS;
	}
    }
    
    /** 
     * Check whether a password has a sufficiently diverse number
     * of character types
     * @param new The new password
     * @param isAssociate Is the user a participant associate?
     * @return true if the number of character types is sufficient, false
     *         otherwise
     */
    private function checkCharDiversity($password, $isAssociate) {
        if ($isAssociate || $this->controller->is_staff) {
	    return $this->charGroups($password) >= 
	           $this->minCharGroups($isAssociate);
        } else {
	    // if not a staff, must have a letter and a number
	    return ($this->hasLowercase($password) || 
	            $this->hasUpperCase($password)) &&
	            $this->hasDigit($password);
	}
    }

    /**
     * Check whether a new password meets our password policy
     * @param new new password
     * @param old old password (hashed)
     * @param isAssociate Is the user a participant associate?
     * @return an array of booleans:  
     *    isSecure (if true, the other booleans are false, if false, at least
     *        one of the others is true)
     *    duplicate (old same as new)
     *    short (not long enough)
     *    similarChars (not enough different types of characters)
     *
     */
    /* The policy is as follows:  
       1. new password cannot be the same as the old.
       2. For users who are staff or associates, passwords must be at least 
          8 characters long and contain 3 of the following char groups:  
	  uppercase letters, 
          lowercase letters, digits, everything else
       3. For all other users, passwords must be at least 6 characters long and
          use at least one letter and one number
     */
    private function checkPassword($new, $old, $isAssociate) {
        $isSecure = $short = $duplicate = $similarChars = false;

	if ($this->Auth->password($new) == $old) {
	    $duplicate = true;
	} else {
	    if (strlen($new) < $this->minPasswordLength($isAssociate)) {
	        $short = true;
            } else if (!$this->checkCharDiversity($new, $isAssociate)) {
	        $similarChars = true;
	    } else {
	        $isSecure = true;
	    }
	}

	return array('isSecure' => $isSecure,
	             'duplicate' => $duplicate,
		     'short' => $short,
		     'similarChars' => $similarChars);
    }

    /**
     * Check the password of a regular user
     * @param new new password (plaintext)
     * @param old old password (hashed)
     * @return an array of booleans:  see checkPassword
     */
    function checkUserPassword($new, $old) {
        return $this->checkPassword($new, $old, false);
    }

    /**
     * Check the password of an associate
     * @param new new password (plaintext)
     * @return an array of booleans:  see checkPassword
     */
    function checkAssociatePassword($new) {
        return $this->checkPassword($new, '', true);
    }

    /**
     * Return a temporary password for a user
     * @param username Name of the user
     * @return The temporary password
     */
    function getTempPassword($username) {
        return $username . Configure::read('tempPwPostfix');
    }

    /**
     * Reset a user's password to a temporary (random) password.  
     * This method does not check for authorization. 
     * @param id Id of the patient
     * @return the temporary password
     */
    function resetPassword($id) {
        $user = $this->controller->User->findById($id);
        $username = $user['User']['username'];
        $tempPassword = CProUtils::generateRandomString(6);
        $hashedPassword = $this->Auth->password($tempPassword);

        $this->controller->User->id = $id;
        $this->controller->User->saveField('password', $hashedPassword);
        //require password to be changed on next login
        $this->controller->User->saveField('change_pw_flag', 1);
        return $tempPassword;
    }
}
?>
