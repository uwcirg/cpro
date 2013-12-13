<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class ShibAuthenticate extends BaseAuthenticate {

/**
 *
 */
    public function authenticate(CakeRequest $request, CakeResponse $response) {

        $returnVal = $this->myGetUser($request);
//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(), returning:' . print_r($returnVal, true));
        return $returnVal;
    }

/**
 * Get a user based on information in the request. Used by cookie-less auth for stateless clients.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
    public function myGetUser($request) {
//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(...)');
//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(), heres _SERVER: ' . print_r($_SERVER, true));
        $returnVal = false;

        if (!(array_key_exists('Shib-Session-ID', $_SERVER) &&
                (array_key_exists('REMOTE_USER', $_SERVER)))){
//            CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(), NOT logged in to shib');

        }
        else {
            $remoteUser = $_SERVER['REMOTE_USER'];
            // remove @domain
            $remoteUser = explode('@', $remoteUser);
            $remoteUser = $remoteUser[0];
            $returnVal = $this->_findUser($remoteUser);
//            CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(), logged in to shib as ' . $_SERVER['REMOTE_USER'] . ', and returning ' . print_r($returnVal, true));
        }

        return $returnVal;
    }


/**
 * Find a user record using the standard options.
 *
 * @param string $conditions The username/identifier.
 * @param string $password Unused here, already authenticated by Shibboleth.
 * @return Mixed Either false on failure, or an array of user data (and associated tables's data, eg Clinic).
 */
    protected function _findUser($conditions, $password = null) {
//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(...), w/ conditions: ' . print_r($conditions, true) . ", and settings: " . print_r($this->settings, true));

        $returnVal;

        $userModel = $this->settings['userModel'];
        list(, $model) = pluginSplit($userModel);
        $fields = $this->settings['fields'];

        if (!is_array($conditions)) {
            $username = $conditions;
            $conditions = array(
                $model . '.' . $fields['username'] => $username
            );
        }
        if (!empty($this->settings['scope'])) {
            $conditions = array_merge($conditions, $this->settings['scope']);
        }
        $result = ClassRegistry::init($userModel)->find('first', array(
            'conditions' => $conditions,
            'recursive' => $this->settings['recursive'],
            'contain' => $this->settings['contain'],
        ));
        if (empty($result) || empty($result[$model])) {
            return false;
        }
        $user = $result[$model];
        unset($result[$model]);
        $returnVal = array_merge($user, $result);

//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . "($conditions), returning " . print_r($returnVal, true));
//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . "($conditions), returning array w/ User elem:" . print_r($returnVal['User'], true));
        return $returnVal;
    }

/**
 *
 */
    public function logout($user){
//        CakeLog::write(LOG_DEBUG, __CLASS__ . '.' . __FUNCTION__ . '(...)');

        $this->_Collection->getController()->redirect(FULL_BASE_URL . '/Shibboleth.sso/Logout?return=' . urlencode(Router::url('/users/login', true)) . '&entityID=urn:mace:incommon:washington.edu');
    }

}

?>
