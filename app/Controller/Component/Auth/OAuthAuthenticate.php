<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class OAuthAuthenticate extends BaseAuthenticate {

/**
 * Called by the auth layer anytime authentication is required, if
 * this class is part of the $this->Auth->authenticate array.
 */
    public function authenticate(CakeRequest $request, CakeResponse $response) {
        //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
          //  '(), request:' . print_r($request, true));
        // After a roundtrip to the IdP via opauth, double check 'validated'.
        if (array_key_exists('data', $request) &&
            array_key_exists('validated', $request->data) &&
            $request->data['validated']) {
            $returnVal = $this->myGetUser($request);
            //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
              //  '(), returning:' . print_r($returnVal, true));
            return $returnVal;
        } else {
            //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
              //  '(), returning false');
            return false;
        }
    }

/**
 * Get a user based on information in the request.  The request should
 * already be populated by what is returned from the IdP round trip
 * via opauth.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
    public function myGetUser($request) {
        //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(...)');
        $returnVal = $this->_findUser(array('email' =>
            $request->data['auth']['info']['email']));
        if ($returnVal) {
            //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
              //  '(), logged in to Oauth as ' . $returnVal['username'] .
              //  ', and returning ' . print_r($returnVal, true));
        }
        return $returnVal;
    }


/**
 * Find a user record by conditions.  For this authorization class, we
 * match only on email - as a 3rd party IdP makes it too easy to spoof
 * a user name, etc.
 *
 * @param string $conditions Array with email address to lookup.
 * @param string $password Unused here, already authenticated by Opauth.
 * @return Mixed Either false on failure, or an array of user data (and associated tables's data, eg Clinic).
 *
 * NB _SESSION['auth_error'] may be set with failure info
 */
    protected function _findUser($conditions, $password = null) {
        //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
          //  '(...), w/ conditions: ' . print_r($conditions, true) .
          //  ", and settings: " . print_r($this->settings, true));
        if (empty($conditions)) {
            //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
              //  " no conditions to lookup, returning false");
            return false;
        }
        $userModel = $this->settings['userModel'];
        list(, $model) = pluginSplit($userModel);

        $result = ClassRegistry::init($userModel)->find('first', array(
            'conditions' => $conditions,
            'recursive' => $this->settings['recursive'],
            'contain' => $this->settings['contain'],
        ));
        if (empty($result) || empty($result[$model])) {
            //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
              //  " no match, returning false");
            $_SESSION['auth_error'] = $conditions['email'] . ' ' .
                 __('not a valid user');
            return false;
        }
        $user = $result[$model];
        unset($result[$model]);
        $returnVal = array_merge($user, $result);

        //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ .
          //  "($conditions), returning " . print_r($returnVal, true));
        return $returnVal;
    }

    public function logout($user){
        //CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(...)');
        session_destroy();
    }

}

?>
