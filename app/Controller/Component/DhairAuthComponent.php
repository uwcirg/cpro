<?php
/** 
 *  Handles much of the authorization, and some authentication for DHAIR 
 *
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
 *
 *  Authentication answers: who is the user? are they who they say they are?
 *  Authorization: are they allowed to view their request?
 *
 *  Steps in the process of a normal request:
 *
 *  1. Authenticate username and password
 *  This is handled automatically by the CakePHP Auth lib 
 *
 *  2. If the requesting controller/action has been passed to 
 *    Auth->allow, it will not undergo further authorization checks
 *
 *  3. For calls which requires authorization 
 *    (i.e. any that has not been passed to Auth->allow in its controller) 
 *    Since we set Auth's authorize method to 'controller' here, 
 *    authorization is determined by $controller->isAuthorized(). 
 *    Our customization of this fxn is in app_controller
 *
 *  4. isAuthorizedForUrl (defined here)
 *    Determines whether the current user is authorized to access a url
 *    Checks the ACL trees for permissions
 *    Note that this is also used when determining which nav elements & links 
 *      should be added to the page
 *
 *  About DHAIR's ACL Authorization
 *    ACL trees are used to define role and resource heirarchies
 *    ARO tree (defines roles):
 *      There is one ARO tree, which defines the role heirarchy
 *      Membership in a child ARO means membership in its parent(s) also
 *    ACO trees (define resources):
 *      Access to a parent ACO also grants access to its children
 *      There are two ACO trees:
 *      1. Controllers ACO tree (under "Controllers" ACO node)
 *          Heirarchy of controllers and actions
 *          AROS are typically granted permissions on a per action basis
 *          Note: we're not using ACL's ability to distinguish CRUD types
 *      2. Cross-user ACO tree (under "acoUsers" ACO node)
 *          This should only be applied within an action,
 *            and should probably be limited to cross-staff edits
 *            if used at all (e.g. determining which staff acos are editable
 *            by the current user - see crudableUserAcos in Admin controller 
 *    User Membership in the ACL trees is defined in the UserAclLeafs model
 *      If a user has an acl* alias record, he is a member of aro* and aco*.
 *        e.g. if a user has a record w/ acl_alias = aclPatient, that user
 *            belongs to aroPatient and acoPatient
 *      Users can be assigned multiple ACL aliases 
 *      This model and its fxnality are our own construction.
 *    To initialize empty acl tables, use the "buildAclTrees" function 
 *    Note:  while clinic-specificity is not defined in the ACL trees, 
 *      it is applied in this component.
 *    Viewing/modding the trees using the cake console 
 *      Examples (run app/Console/cake) 
 *        cake acl view aro (or aco) (displays all trees)
 *        cake acl create aco (or aro) aliasParent aliasChild
 *        e.g. cake acl create aco Controllers/Surveys summary
 *        cake acl delete aco Controllers/Junk
 *        cake acl grant aroParticipantTreatment Controllers/Journals/index all 
 *        cake acl grant aroAliasX acoAliasY read 
 *        cake acl check aroAdmin Controllers/Surveys/summary read
*
*/


class DhairAuthComponent extends Component
{
    var $components = array('Auth', 'Acl', 'DhairLogging', 'RequestHandler',
                            'Session');
    var $uses = array('UserAclLeaf', 'User', 'Inflector', 'String');

    // the list of UserAclLeaf's aka roles this user is a member of
    // Only set once per request, during initialize of this component
    var $userAclLeafs;
    // list of cross-user acos the current user has some CRUD permissions on

    // Acl static class instance used to call its lib
    var $userAclLeafInstance;
    var $inflectorInstance;

    // Access to these will not be restricted 
    //   (i.e., the controllers will pass them to Auth->allow)
    var $allowedActions = array(
            'Clinicians' => array('survey'),
            'Users' => array(
                'about',
                'contact',
                'logout',
                'help',
                'edit', // FIXME remove
                //'selfRegister',
                // 'registerEdit',
                'identify',
                'setLanguageForSelf',
                'login_assist',
            ),
            'Patients' => array('optOut'),
            'Associates' => array('register', 'registerFinish'));

    //called before Controller::beforeFilter()
    function initialize(Controller $controller) {
    //function initialize(&$controller) {
        // saving the controller reference for later use
        $this->controller = $controller;
        //$this->controller =& $controller;

        $this->userAclLeafInstance = 
            ClassRegistry::init('UserAclLeaf');
        $this->inflectorInstance = $this->controller->Inflector;

        /**$this->log("DhairAuth initialize(); controller name:" . 
                    $this->controller->name .
                    "; action name: " . $this->controller->action . 
                    "; trace:" . 
                    Debugger::trace(),
                    LOG_DEBUG);
        */

        $this->userAclLeafs = $this->userAclLeafInstance->
            findAllByUserId($this->Auth->user('id'));
        //$this->log("DhairAuthComponent.initialize(); here are this->userAclLeafs for user " . $this->Auth->user('id') . " : " . print_r($this->userAclLeafs, true), LOG_DEBUG);
        /**
        $this->crudableUserAcos['read'] =
            $this->getCRUDableSubAcos('acoUsers', 'read');
        $this->crudableUserAcos['create'] =
            $this->getCRUDableSubAcos('acoUsers', 'create');
        */

        $authn_classes = array('Form',);
        if (defined('OAUTH_LOGIN') && OAUTH_LOGIN)
            array_push($authn_classes, 'OAuth');
        if (true || defined('UWNETID_LOGIN') && UWNETID_LOGIN)
            array_push($authn_classes, 'Shib');
        $this->Auth->authenticate = $authn_classes;

        if ($this->controller->is_staff) 
            $this->Auth->loginRedirect = array(
                'controller' => 'patients', 'action' => 'viewAll');

        $this->Auth->authError = 'Please log in to the system.';
        $this->Auth->authorize = array('Controller');

    }

    //called after Controller::beforeFilter()
    /**
    function startup(Controller $controller) {
    }
    */

    /**
    *   Checks whether a user belongs to or inherits a role
    *   @param $roleName The name of the role 
    *               ('aro' prefix can be included but need not be)
    *               e.g. 'aroCentralSupport'; 'CentralSupport'
    *   @return boolean 
    */
    function checkForRole($userID, $roleName){

        if (!(strpos($roleName, 'aro') === 0)) {
            $roleName = 'aro' . $roleName;
        }
        
        return $this->checkWhetherUserBelongsToAro($userID, $roleName);
    }

    /**
      Was a bad id field passed into form (and similar) requests?
      @param vars Array of vars that should contain the id
      @return true if the id field was not present or doesn't match;
              false if it was present and matches
     */
    function badId($vars) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(vars) w/ vars = " . print_r($vars, true), LOG_DEBUG);

        if (!$this->Session->check(AppController::ID_KEY)) {
	        $returnval = true;
        }

        elseif (empty($vars) || empty($vars[AppController::ID_KEY]) ||
            $vars[AppController::ID_KEY] !=
	        $this->Session->read(AppController::ID_KEY))
        {
            // dump a bunch of variables and abort


            if (empty($vars)) {
                $this->log('vars empty');
            } else if (empty($vars[AppController::ID_KEY])) {
                $this->log('vars[ID_KEY] empty');
            } else {
                $this->log('bad session id: ' . $this->Session->read(AppController::ID_KEY) . ' vs ' . $vars[AppController::ID_KEY]);
            }

            if ($this->controller->request->isPost()) {
                $this->log('isPost');
            } else if ($this->controller->request->isGet()) {
                $this->log('isGet');
            }

                // don't log secret fields
            if (!empty($this->controller->params['data']) &&
                !empty($this->controller->params['data']['User']))
            {
                unset($this->controller->params['data']['User']['password']);
                unset($this->controller->params['data']['User']['password_confirm']);
            }
            /* [Patient][secret_phrase] is also secret, but all of Patient
               is unset below */

                // unset sensitive fields
            if (!empty($this->controller->params['data']) &&
                !empty($this->controller->params['data']['Patient']))
            {
                unset($this->controller->params['data']['Patient']);
            }

            if (!empty($this->controller->params['data']) &&
                !empty($this->controller->params['data']['Note']))
            {
                unset($this->controller->params['data']['Note']);
            }

            if (!empty($this->controller->params['data']) &&
                !empty($this->controller->params['data']['PatientViewNote']))
            {
                unset($this->controller->params['data']['PatientViewNote']);
            }

            // $this->log('params: ' . print_r($this->controller->params, true));

            if (!empty($this->passedArgs)) {
                $this->log('passedArgs: ' . print_r($this->passedArgs, true));
            }

            $returnVal = true;

        } else {
            // $this->log('session id matches', LOG_DEBUG);
	        $returnVal = false;
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() returning " . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// function badId($vars)

    /** Actions that can or should be accessed only as POST.
     * Key = controller.action,
     * value = whether the action can be accessed as both a GET and POST
     */
    private static $postActions = 
        array('activity_diary_entries.edit' => false,
              'associates.create' => false,
              'associates.edit' => false,
              'associates.registerFinish' => false,  
              'associates.phraseEntry' => false,
              'audio_files.assignCoders' => true,
              'audio_files.code' => true,
              'audio_files.edit' => false,
              'audio_files.upload' => false,
              'chart_codings.code' => true,
              'charts.assignCoders' => true,
              'clinicians.add' => true,
              'clinicians.changePriority' => false,
              'clinicians.consents' => true,
              'clinicians.edit' => true,
              'clinicians.survey' => true,
              'journals.delete' => false,
              'journals.create' => false,
              'journals.updateText' => false,
              'patients.add' => true,
              'patients.edit' => true,
              'patients.changeUsername' => true,
              'patients.search' => true,
              'patients.deleteNote' => false,
              'patients.editNote' => false,
              'patients.consents' => true,
              'patients.medications' => true,
              'appointments.add' => false,
              'surveys.answer' => false,
              'users.login' => true,    
              'users.updateTimeout' => false,    
              'users.setLanguageForSelf' => true,    
              'users.changePasswordOfAuthdUser' => true);

    /**
     * Does this HTTP request use the proper method?
     * @return false if they perform a GET when they should have performed a 
     *         POST
     */
    private function properMethod() {
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        $arrayKey = $this->controller->params['controller'] . '.' . 
            $this->controller->params['action'];
        $isPost = $this->controller->request->isPost();

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); arrayKey:$arrayKey, isPost:$isPost", LOG_DEBUG);
        $returnVal;

        if ($isPost || !array_key_exists($arrayKey, self::$postActions)) {
            // Don't need to check, but...

            if ($isPost && !array_key_exists($arrayKey, self::$postActions)) {
	            // this is somewhat suspicious
//                $this->log("Action $arrayKey accessed via POST method, but it is not in the list of postActions in dhair_auth.", LOG_DEBUG);
            }
            //$this->log("properMethod() returning true");
            $returnVal = true;
        } 
        else {   
        /* We know it's a GET on an action that sometimes/always uses POST
       These are only okay if you can sometimes use GET, and you
       don't pass any data */
            $getSometimesOkay = self::$postActions[$arrayKey];
            $noData = empty($this->controller->request->query);
            //$noData = empty($this->controller->request->data);
//            if (!empty($this->controller->request->query)) $this->log(__CLASS__ . "." . __FUNCTION__ . "(), controller->request->query not empty, here it is: " . print_r($this->controller->request->query, true), LOG_DEBUG);
            //$this->log("properMethod() getSometimesOkay:$getSometimesOkay, noData:$noData", LOG_DEBUG);
            
            $returnVal = $noData && $getSometimesOkay;
            //$this->log("properMethod() returnVal:$returnVal");
        }
        
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for $arrayKey; returnVal:" . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// private function properMethod()

    /** Does the request look like a potential cross-site request forgery?
      * @return Whether the request looks like a cross-site request forgery
      */
    /* All POST requests must be checked unless there is no authorized user
       We assume the only GET requests 
       that must be checked are those that change the database state,
       or the special kiosk mode changes.  These actions are listed
       below (kiosk only performs an action if a parameter is passed).  

       Any future exceptions will have to be added
       to the else clause below
     */
    function possibleXsrf() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", heres params[url]: " . print_r($this->controller->params['url'], true) . ", heres empty(" . $this->controller->params['url'] . "): " . empty($this->controller->params['url']) . ", and heres sizeof(" . $this->controller->params['url'] . "): " . sizeof($this->controller->params['url']) /* . ", and here's the stack trace: " . Debugger::trace()*/, LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", heres request[pass]: " . print_r($this->controller->request->query, true) . ", heres empty(" . $this->controller->request->query . "): " . empty($this->controller->request->query) . ", and heres sizeof(" . $this->controller->request->query . "): " . sizeof($this->controller->request->query) /* . ", and here's the stack trace: " . Debugger::trace()*/, LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", heres request[data]: " . print_r($this->controller->request->data, true) . ", heres empty(request->data): " . empty($this->controller->request->data) . ", and heres sizeof(request->data): " . sizeof($this->controller->request->data) /* . ", and here's the stack trace: " . Debugger::trace()*/, LOG_DEBUG);

        $returnVal;

        if (!$this->properMethod()) {
//            $this->log("Improper GET request on: " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ' ' . print_r($this->controller->request->data, true), LOG_DEBUG);
            $returnVal = true;
        }

        elseif (($this->controller->request->isPost() && $this->Auth->user())
                || $this->controller->request->isPut()) {
            if (!empty($this->controller->request->data['AppController'])) {
                $returnVal = $this->badId($this->controller->request->data['AppController']);
            } 
            else {
                $returnVal = $this->badId(null);	// sure to fail
            }
        } 
        else if ($this->controller->request->isGet() && 
            ($this->controller->params['action'] == 'delete' ||
            ($this->controller->params['action'] == 'kiosk' && 
                !empty($this->controller->params['pass'])) ||
            $this->controller->params['action'] == 'resetPassword' ||
            $this->controller->params['action'] == 'changeDates' ||
            $this->controller->params['action'] == 'generateWebkeys' ||
            $this->controller->params['action'] == 'saveDatabase' ||
            $this->controller->params['action'] == 'reloadDatabase')) 
        {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . "; special block"/*,  stack: " . Debugger::trace()*/, LOG_DEBUG);
            if (sizeof($this->controller->request->query) > 0) 
            //if (sizeof($this->controller->request->pass) > 0) 
            //if (!empty($this->controller->request->pass)) 
            {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", ; special block, ! empty this->controller->request->pass, will check badId on that next", LOG_DEBUG);
                $returnVal = $this->badId($this->controller->request->query);
            }    
            /**$this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . "; special block,  stack: " . Debugger::trace(), LOG_DEBUG);
            if (!empty($this->controller->params) && 
                !empty($this->controller->params['url'])) 
            {
                $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", ; special block, ! empty params[url], will check badId on that next", LOG_DEBUG);
                $returnVal = $this->badId($this->controller->params['url']);
            }  */  
            else {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", ; special block, empty this->controller->request->query, sure to fail on badId(null) next", LOG_DEBUG);
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->params['controller'] . '.' . $this->controller->params['action'] . ", ; special block, empty params[url], sure to fail on badId(null) next", LOG_DEBUG);
                $returnVal = $this->badId(null);	// sure to fail
            }
        } 
        else {
            $returnVal = false;
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returnVal:" . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// function possibleXsrf()

  /**isAuthorizedForUrl(String $url) -> Boolean
   * $url should be a cakephp url, relative to the clinic's location, not an absolute url or filepath.
   *
   * checks both ACL and custom business rules to see if current
   * session's user may access the given url. 
   * This is the single authoritative answer to the question "can he see this?"
   * The methods it calls should not be called directly except in extreme cases
   * If the url isn't defined in the aco tree, it won't be accessible
   */
  function isAuthorizedForUrl($controller, $action) {
    
//      $this->log(__CLASS__ . "." . __FUNCTION__ . "($controller, $action)", LOG_DEBUG);
      $authorized = false;

    //$parsedUrl = $this->parseUrlControllerAndAction($url);
    //$controller = $parsedUrl['controller'];
    //$action = $parsedUrl['action'];
    
    $controller = $this->inflectorInstance->camelize($controller);
    //$this->log(__CLASS__ . "." . __FUNCTION__ . "(...), camelize'd controller: $controller", LOG_DEBUG);

    /**  
     *     If the action is in the allowedActions array, return true.
     *     Note that Auth won't call isAuthorized to check these actions,
     *     but we still need to weed them out here because this fxn is used
     *     to determine whether to display links to actions
     */
    if (!empty($this->allowedActions[$controller]) 
            && in_array($action, $this->allowedActions[$controller])) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); action in allowedActionsList so setting authorized to true", LOG_DEBUG);
        $authorized = true;
    }
    elseif (!($this->Auth->user('id'))){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); action not in allowedActionsList, and !(this->Auth->user(id)) so setting authorized to false", LOG_DEBUG);
        // publicly accessible urls must be in the allowedActionsList
        $authorized = false;
    }
      
    //  $this->log("all session vars: " . print_r($this->Session->read(), true), LOG_DEBUG);

    //elseif (false /*FIXME hotwired to ignore session var FIXME*/){
    elseif ($this->Session->check(CONTROLLERS_ACTIONS_AUTHZN)){
        $controllersActionsAuthzn = 
            $this->Session->read(CONTROLLERS_ACTIONS_AUTHZN); 
//        $this->log("isAuthorizedForUrl(controller:$controller,action:$action), controllersActionsAuthzn exists: " . print_r($controllersActionsAuthzn, true), LOG_DEBUG);
        if (array_key_exists($controller, $controllersActionsAuthzn)){
            if (array_key_exists($action, 
                    $controllersActionsAuthzn[$controller])){
                $authorized = 
                    $controllersActionsAuthzn[$controller][$action];
            }
            else {
                $authorized = $this->_checkDbForAuthzAndStoreInSession(
                    $controller, $action);
            }
        } 
        else {
//            $this->log("isAuthorizedForUrl(controller:$controller,action:$action), controllersActionsAuthzn[$controller] didn't exist" , LOG_DEBUG);
            $authorized = $this->_checkDbForAuthzAndStoreInSession(
                                $controller, $action);
        }
    }
    else {
//        $this->log("isAuthorizedForUrl(controller:$controller,action:$action), session var didn't exist, so will _checkDbForAuthzAndStoreInSession next" , LOG_DEBUG);
        $authorized = $this->_checkDbForAuthzAndStoreInSession(
                                $controller, $action);
    }

    // accommodate instance specific action (eg clinician_report_sarcoma) w/out needing to add to acl trees
    $actionWithoutInstanceId = $this->controller->actionNameWithoutInstanceId($action);
//    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); actionNameWithoutInstanceId: " . $action, LOG_DEBUG);

    // Attempt isAuthorizedForUrl() without Instance id
    if (!$authorized and $action != $actionWithoutInstanceId)
        $authorized = $this->isAuthorizedForUrl(
            $controller,
            $actionWithoutInstanceId
        );


//    $this->log(__CLASS__ . "->" . __FUNCTION__ . "() checked controller: " . $controller . "; action: " . $action . "; " . "returning authorized: " . $authorized . "; ", LOG_DEBUG);

    return $authorized;
  }// function isAuthorizedForUrl($controller, $action) {


    /**
    */
    function requestIsForAllowableAction(){

        $controller = 
            $this->inflectorInstance->camelize($this->controller->request->controller);

        if (!empty($this->allowedActions[$controller]) 
            && in_array($this->controller->request->action, 
                        $this->allowedActions[$controller])) {
            $returnVal = true;
        }
        else $returnVal = false;

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $controller . "/" . $this->controller->request->action . ", returning $returnVal", LOG_DEBUG);
        return $returnVal;
    }


    /**
    *  No need to call this if the controller & action have already been
    *   looked up, because it will be stored in the session var
    */
    function _checkDbForAuthzAndStoreInSession(
                $controller, $action){
        
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($controller, $action)", LOG_DEBUG);

        $authorized = ($this->_aclCheckForArosThatUserBelongsTo(
                        $this->Auth->user('id'),
                        'Controllers/' . $controller .
                            '/' . $action , 
                        '*'));

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($controller, $action); authorized=$authorized", LOG_DEBUG);

        $controllersActionsAuthzn = array();

        if ($this->Session->check(CONTROLLERS_ACTIONS_AUTHZN)){
            $controllersActionsAuthzn = 
                $this->Session->read(CONTROLLERS_ACTIONS_AUTHZN);
            if (array_key_exists($controller, $controllersActionsAuthzn)){
                $controllersActionsAuthzn[$controller][$action] = $authorized; 
            }
            else {
                $controllersActionsAuthzn[$controller] = 
                    array($action => $authorized);
            }
        }
        else {
            $controllersActionsAuthzn = 
                    array($controller => array($action => $authorized));
        }
 
        $this->Session->write(CONTROLLERS_ACTIONS_AUTHZN,
            $controllersActionsAuthzn);
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($controller, $action), controllersActionsAuthzn post modification: " . print_r($this->Session->read(CONTROLLERS_ACTIONS_AUTHZN), true), LOG_DEBUG);
        return $authorized;
    }

    /**
    *
    *   Using user_acl_leafs table
    *
    */
    function _aclCheckForArosThatUserBelongsTo(
                    $userID, $acoAliasToCheck, $crudType){
/**        $this->log(__CLASS__ . "." . __FUNCTION__ . "() " .
                "userID: " . $userID . "; " .
                "acoAliasToCheck: " . $acoAliasToCheck . "; " .
                "crudType: " . $crudType. "; \n" .
                Debugger::trace(), LOG_DEBUG);*/
        if ($userID != $this->Auth->user('id')) {
            $userAclLeafs = $this->userAclLeafInstance->findAllByUserId($userID);
        } else {
            $userAclLeafs = $this->userAclLeafs;
        }

        // for each aro that this user belongs to, 
        // see if they have access to acoAlias
        foreach($userAclLeafs as $userAclLeaf){
            $aroAlias = str_replace('acl', 'aro',
                $userAclLeaf['UserAclLeaf']['acl_alias']);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), next is Acl->check($aroAlias, $acoAliasToCheck, $crudType)", LOG_DEBUG);
            try{ 
                if ($this->Acl->check($aroAlias, $acoAliasToCheck, $crudType)){
//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning true", LOG_DEBUG);
                    return true;
                }
            } catch (Exception $e) {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(), caught exception, returning false", LOG_DEBUG);
                return false;
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning false", LOG_DEBUG);
        return false;
    }

    /**
    *  Given an aco alias, retrieve an array of sub-acos 
    *  which the currently authenticated user has 
    *  permission to perform some crud operation on 
    *  This is only used by the cross-user ACO tree (not currently used)
    *  
    *  @param $acoBaseName - alias of the aco to look under (e.g. 'acoUsers')
    *  @param $crudType - 'create', 'read', 'update', 'delete', '*' 
    */
    function getCRUDableSubAcos($acoBaseName, $crudType,
                                    $includeThisAlias = false){

        $aco = $this->Acl->Aco;
        $listOfAccessibleAcos = array();

        $acoBase = $aco->findByAlias($acoBaseName);
        $acosUnderBase = $aco->children($acoBase['Aco']['id']);
        foreach($acosUnderBase as $acoUnderBase){
            if ($this->_aclCheckForArosThatUserBelongsTo(
                            $this->Auth->user('id'),
                            $acoUnderBase['Aco']['alias'],
                            $crudType))
            {
                $listOfAccessibleAcos[] = $acoUnderBase['Aco']['alias'];
                //$this->log("CRUDableSubAco: " . 
                //                $acoUnderBase['Aco']['alias'] . "; " .
                //            Debugger::trace(), LOG_DEBUG);
            }
        }
        return $listOfAccessibleAcos;
    }


    /**
     *  Given the alias of a cross-User group aco ("acoUsers*"), 
     *  return an approprate array 
     *      of the user records which are acting as aco*
     *      (acl* as specified in user_acl_leafs.acl_alias)
     *
     *  'Appropriate' here refers to all users, users in the same site
     *  as the authenticated user, or users in the same clinic as the
     *  authenticated user.
     *
     *  @param $acoUsersGroup the alias of a user aco; must be "acoUsers*" e.g. "acoUsersResearchStaff"
     *  @param $wholeAcoBranch include Users in entire Aco branch (a la standard aco behavior)
     *  @param $sameSite Only return users in the same site
     *  @param $sameClinic Only return users in the same clinic
     */
    function getUsersInAcoUsers($acoUsersGroupAlias,
                                    $wholeAcoBranch = true,
                                    $sameSite = true,
                                    $sameClinic = true)
    {
        //$this->log("acoUsersGroupAlias: " . 
        //                 $acoUsersGroupAlias . "; " .
        //                 Debugger::trace(), LOG_DEBUG);

        $arrayOfUsersToReturn = array();

        if ($wholeAcoBranch == true){
            // get all parents of this acoNode
            $aco = $this->Acl->Aco;

            $acoUsersX =  $aco->findByAlias($acoUsersGroupAlias);
            $parentAcos = $aco->getpath($acoUsersX['Aco']['id']);

            foreach ($parentAcos as $parentAco){
                $aclNodeParentAlias =
                    str_replace('acoUsers', 'acl',
                                    $parentAco['Aco']['alias']);
                $arrayOfUsersToReturn =
                     array_merge($arrayOfUsersToReturn,
                                 $this->_getUsersDirectlyUnderAclNode(
                                     $aclNodeParentAlias, 
		                     $sameSite, 
				     $sameClinic));
            }
        }
        else {
            $aclNodeParentAlias = str_replace('acoUsers', 'acl',
                                            $acoUsersGroupAlias);
            $arrayOfUsersToReturn =
                $this->_getUsersDirectlyUnderAclNode(
                    $aclNodeParentAlias, $sameSite, $sameClinic);
        }
        
        /**
        ob_start();
        var_dump($arrayOfUsersToReturn);
        $debugStr = ob_get_contents();
        ob_end_clean();
        $this->log("for acoUsersGroupAlias param: " . $acoUsersGroupAlias . 
                    ", direct users = " . $debugStr . "; "  
                    . Debugger::trace(), LOG_DEBUG);
        */
        return $arrayOfUsersToReturn;
    }

    /** 
      Check whether a pair of users passes the site test
      @param sameSite Whether the two users have to be from the same site
      @param user1 first user
      @param user2 second user
      @return true if the two users have the same site_id, or sameSite
          is false
     */
    function siteOkay($sameSite, $user1, $user2) {
        return !$sameSite || 
	    $user1['Clinic']['site_id'] == $user2['Clinic']['site_id'];
    }

    /** 
      Check whether a pair of users passes the clinic test
      @param sameClinic Whether the two users have to be from the same clinic
      @param user1 first user
      @param user2 second user
      @return true if the two users have the same clinic_id, or sameClinic
          is false
     */
    private function clinicOkay($sameClinic, $user1, $user2) {
        return !$sameClinic || 
	    $user1['User']['clinic_id'] == $user2['User']['clinic_id'];
    }

    /**
     * Is a given userid an admin user?
     * @param id Id to check
     * @return does the id belong to an admin user?
     */
    function centralSupport($id = null) {
        if ($id == null){$id = $this->Auth->user('id'); }
        return $this->checkWhetherUserBelongsToAro(
                $id, 'aroCentralSupport');
    }

    /**
     * Is a given userid research staff?
     * @param id Id to check
     * @return does the id belong to research staff?
     */
    function researchStaff($id = null) {
        if ($id == null){$id = $this->Auth->user('id'); }
        return $this->checkWhetherUserBelongsToAro(
                $id, 'aroResearchStaff');
    }

    /**
     * Is a given userid clinic staff?
     * @param id Id to check
     * @return does the id belong to research staff?
     */
    function clinicStaff($id = null) {
        if ($id == null){$id = $this->Auth->user('id'); }
        return $this->checkWhetherUserBelongsToAro(
                $id, 'aroClinicStaff');
    }

    /**
     * Check whether a clinic id value is valid for the
     * authenticated user
     * @param clinicId Clinic id for the user
     * @param authUser Authenticated user
     */
    function validClinicId($clinicId, $authUser) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), clinicId:$clinicId, authUser: " . print_r($authUser, true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...)", LOG_DEBUG);

        $returnVal;
        $authUserId = $authUser['User']['id'];

        // an empty value is invalid
        if (empty($clinicId)) {
            $returnVal = false;
        } else {
            $authUserClinicId = $authUser['User']['clinic_id'];

            if ($clinicId == $authUserClinicId) {
                // matching values are valid
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(), same clinic", LOG_DEBUG);
                $returnVal = true;
            } else if ($this->centralSupport($authUserId)) {
                // admin users can access all patients
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(), centralSupport, so okay", LOG_DEBUG);
                $returnVal = true;
            } else {
                // research staff have privileges on any user at the same site
                $returnVal = $this->researchStaff($authUserId) &&
                       $this->controller->Clinic->sameSite($clinicId, 
                                                           $authUserClinicId);
            } 
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning $returnVal", LOG_DEBUG);
        return $returnVal;
    }

    /**
     * Check whether a patient is valid for the
     * authenticated user
     * @param patientId Id of the patient
     * @param authUserId Id of the authenticated user
     */
    function validPatientId($patientId, $authUserId) {
        $patient = $this->controller->Patient->findById($patientId);
        if (empty($patient)) return false;
        $clinicId = $patient['User']['clinic_id'];
        $authUser = $this->controller->User->findById($authUserId);
        return $this->validClinicId($clinicId, $authUser);
    }


    /**
    *   Given an acl node alias (e.g. aclResearchStaff), 
    *       read the user_acl_leafs table 
    *           to determine that acl node's direct children, 
    *   and return a clinic-limited, site-limited, or all-clinic array 
    *       of the user records for those children 
    *       
    *   @param $aclNodeParentAlias acl alias to look under 
    *   @param $sameSite whether to limit returned records to those with the same site as the authorized user (true by default) 
    *   @param $sameClinic whether to limit returned records to those with the same clinic as the authorized user (true by default) 
    */
    function _getUsersDirectlyUnderAclNode($aclNodeParentAlias,
                                           $sameSite = true,
                                           $sameClinic = true) 
    {
        //$this->log("aclNodeParentAlias param: $aclNodeParentAlias ;  " . 
        //                Debugger::trace(), LOG_DEBUG);

        $userInstance = ClassRegistry::init('User');
        $authUser = $userInstance->findById($this->Auth->user('id'));

        $users = array();
        $userAclLeafs =
            $this->userAclLeafInstance->findAllByAclAlias($aclNodeParentAlias);
        foreach ($userAclLeafs as $userAclLeaf){
            $user =
                $userInstance->findById($userAclLeaf['UserAclLeaf']['user_id']);

            if ($this->clinicOkay($sameClinic, $user, $authUser) &&
		$this->siteOkay($sameSite, $user, $authUser))
            {
                $users[] = $user;
            }
        }

        /** 
        ob_start();
        var_dump($users);
        $debugStr = ob_get_contents();
        ob_end_clean();
        $this->log("for aclNodeParentAlias param: " . $aclNodeParentAlias . 
                    ", direct users = " . $debugStr . "; "  
                    . Debugger::trace(), LOG_DEBUG);
        */
        return $users;
    }


    /**
    *   Search UserAclLeaf for all records matching $userID
    *   for each UserAclLeaf
    *       if it's alias matches $aroParentAlias
    *           return true
    *       else check all the aro's children for the same
    */
    function checkWhetherUserBelongsToAro($userID, $aroParentAlias){

        //$this->log("checkWhetherUserBelongsToAro($userID, $aroParentAlias); here is  this->userAclLeafs : " . print_r($this->userAclLeafs, true), LOG_DEBUG);

        $aro = $this->Acl->Aro;
        $aclNodeParentAlias = str_replace('aro', 'acl', $aroParentAlias);
        $aroParent =  $aro->findByAlias($aroParentAlias);
        // note that this returns all children (not just direct children)
        $subAros = $aro->children($aroParent['Aro']['id']);

        if (($userID != $this->Auth->user('id')) ||
                empty($this->userAclLeafs) || 
                !isset($this->userAclLeafs)){
            $userAclLeafs = $this->userAclLeafInstance->findAllByUserId($userID);
        }
        else {
            $userAclLeafs = $this->userAclLeafs;
        }

        //$this->log("checkWhetherUserBelongsToAro($userID, $aroParentAlias); here are userAclLeafs : " . print_r($userAclLeafs, true) . "\n here's aroParent : " . print_r($aroParent, true), LOG_DEBUG);

        foreach ($userAclLeafs as $userAclLeaf){
            $userAclLeafAlias = $userAclLeaf['UserAclLeaf']['acl_alias'];
            if ($userAclLeafAlias == $aclNodeParentAlias){
                //$this->log("checkWhetherUserBelongsToAro($userID, $aroParentAlias), found match in aclNodeParentAlias so returning true", LOG_DEBUG);
                return true;
            }
            reset($subAros);
            foreach ($subAros as $subAro) {
                if ($subAro['Aro']['alias'] ==
                        str_replace('acl', 'aro', $userAclLeafAlias)){
                    //$this->log("checkWhetherUserBelongsToAro($userID, $aroParentAlias), found match in subAro so returning true", LOG_DEBUG);
                    return true;
                }
            }
        }
        //$this->log("checkWhetherUserBelongsToAro($userID, $aroParentAlias), returning false", LOG_DEBUG);
        return false;
    }


    /**
    * Used to initialize the aros, acos, and aros_acos tables.
    * If used to re-populate these tables, the tables should be emptied first.
    * One way to run this would be to temporarily insert it into
    *   a page which isAuthorized is not called for (e.g. users/login)
    */
    function _buildDhairAclTrees(){

        $this->_buildDhairAroTree();
        $this->_buildAcoTreeForControllers();
        //$this->_buildAcoTreeForCrossUserEdits(); // this has never been used
        $this->_assignAclPermissionsForControllers();
        $this->_assignAclPermissionsForCrossUserEdits();
    }

    /**
    *   Create the Aro tree for DHAIR roles. 
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _buildDhairAroTree(){

        $this->_addAro('aroNonStaff', 'User', null, null);
        $this->_addAro('aroPatientIneligible', 'User', 'aroNonStaff', null);
        $this->_addAro('aroPatient', 'User', 'aroNonStaff', null);
        $this->_addAro('aroParticipant', 'User', 'aroPatient', null);
        $this->_addAro('aroParticipantControl', 'User', 'aroParticipant', null);
        $this->_addAro('aroParticipantTreatment', 'User', 
                        'aroParticipantControl', null);
        $this->_addAro('aroParticipantAssociate', 'User', 'aroNonStaff', null);
        $this->_addAro('aroStaff', 'User', null, null);
        $this->_addAro('aroFrontDeskStaff', 'User', 'aroStaff', null);
        $this->_addAro('aroClinicStaff', 'User', 'aroFrontDeskStaff', null);
        $this->_addAro('aroResearchStaff', 'User', 'aroClinicStaff', null);
        $this->_addAro('aroCentralSupport', 'User', 'aroResearchStaff', null);
        $this->_addAro('aroAdmin', 'User', 'aroCentralSupport', null);
        $this->_addAro('aroSurveyEditor', 'User', 'aroStaff', null);
        $this->_addAro('aroResearcher', 'User', 'aroStaff', null);
        $this->_addAro('aroAudioCoder', 'User', 'aroStaff', null);
    }

    /**
    *   Create the Aco tree for DHAIR controllers. 
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _buildAcoTreeForControllers(){
        $this->_addAco('Controllers', null, null, null);
        
        $this->_addAco('Users', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Users', null);
        $this->_addAco('about', null, 'Controllers/Users', null);
        $this->_addAco('contact', null, 'Controllers/Users', null);
        $this->_addAco('help', null, 'Controllers/Users', null);
        $this->_addAco('edit', null, 'Controllers/Users', null);
        $this->_addAco('logout', null, 'Controllers/Users', null);
        $this->_addAco('changePasswordOfAuthdUser', null, 
                        'Controllers/Users', null);
        $this->_addAco('settings', null, 'Controllers/Users', null);
        $this->_addAco('updateTimeout', null, 'Controllers/Users', null);
        $this->_addAco('selfRegister', null, 'Controllers/Users', null);
        $this->_addAco('registerEdit', null, 'Controllers/Users', null);
        $this->_addAco('edit', null, 'Controllers/Users', null);
        $this->_addAco('login_assist', null, 'Controllers/Users', null);
        $this->_addAco('identify', null, 'Controllers/Users', null);
        
        $this->_addAco('Logs', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Logs', null);
        $this->_addAco('add', null, 'Controllers/Logs', null);
        
        $this->_addAco('Admin', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Admin', null);
        $this->_addAco('kiosk', null, 'Controllers/Admin', null);
        $this->_addAco('viewNonAdminUsers', null, 'Controllers/Admin', null);
        $this->_addAco('createStaff', null, 'Controllers/Admin', null);
        $this->_addAco('reloadDatabase', null, 'Controllers/Admin', null);
        $this->_addAco('saveDatabase', null, 'Controllers/Admin', null);
        $this->_addAco('viewDatabaseSnapshots', null, 
	               'Controllers/Admin', null);
        $this->_addAco('resetPassword', null, 'Controllers/Admin', null);
        
        $this->_addAco('Surveys', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Surveys', null);
        $this->_addAco('new_session', null, 'Controllers/Surveys', null);
        $this->_addAco('show', null, 'Controllers/Surveys', null);
        $this->_addAco('restart', null, 'Controllers/Surveys', null);
        $this->_addAco('answer', null, 'Controllers/Surveys', null);
        $this->_addAco('summary', null, 'Controllers/Surveys', null);
        $this->_addAco('complete', null, 'Controllers/Surveys', null);
        $this->_addAco('break_session', null, 'Controllers/Surveys', null);
        $this->_addAco('questionnaire', null, 'Controllers/Surveys', null);
        $this->_addAco('questionnaires', null, 'Controllers/Surveys', null);
        $this->_addAco('generate_se_test', null, 'Controllers/Surveys', null);
        $this->_addAco('summary_csv', null, 'Controllers/Surveys', null);
        $this->_addAco('edit', null, 'Controllers/Surveys', null);
        $this->_addAco('overview', null, 'Controllers/Surveys', null);
        $this->_addAco('edit_project', null, 'Controllers/Surveys', null);
        $this->_addAco('reopen_test_session', null, 'Controllers/Surveys', null);
        $this->_addAco('finish_test_session', null, 'Controllers/Surveys', null);
        $this->_addAco('log_click_to_external_resource', null, 
                        'Controllers/Surveys', null);
        $this->_addAco('log_teaching_tip_expansion', null, 
                        'Controllers/Surveys', null);
        
        $this->_addAco('Results', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Results', null);
        $this->_addAco('show', null, 'Controllers/Results', null);
        $this->_addAco('showJournals', null, 'Controllers/Results', null);
        $this->_addAco('others', null, 
                        'Controllers/Results', null);
        $this->_addAco('othersReportsList', null, 
                        'Controllers/Results', null);
        $this->_addAco('showToOthers', null, 
                        'Controllers/Results', null);
        $this->_addAco('showJournalsToOthers', null, 
                        'Controllers/Results', null);
        $this->_addAco('data_export', null, 'Controllers/Results', null);
        $this->_addAco('show_activity_diary_data', null, 
                            'Controllers/Results', null);
        $this->_addAco('log_click_to_external_resource', null, 
                        'Controllers/Results', null);
        $this->_addAco('log_teaching_tip_expansion', null, 
                        'Controllers/Results', null);
        
        $this->_addAco('MedicalRecords', null, 'Controllers', null);
        $this->_addAco('clinic_report', null, 
                        'Controllers/MedicalRecords', null);
        $this->_addAco('clinic_report_pdf', null, 
                        'Controllers/MedicalRecords', null);
        $this->_addAco('clinic_report_p3p', null, 
                        'Controllers/MedicalRecords', null);
        $this->_addAco('clinic_report_p3p_pdf', null, 
                        'Controllers/MedicalRecords', null);

        $this->_addAco('Images', null, 'Controllers', null);
        $this->_addAco('view', null, 'Controllers/Images', null);

        $this->_addAco('Teaching', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Teaching', null);
        $this->_addAco('log_click_to_external_resource', null, 
                        'Controllers/Teaching', null);
        $this->_addAco('log_teaching_tip_expansion', null, 
                        'Controllers/Teaching', null);
        $this->_addAco('manage_fatigue', null, 'Controllers/Teaching', null);

        $this->_addAco('P3P', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/P3p', null);
        $this->_addAco('statistics', null, 'Controllers/P3p', null);
        $this->_addAco('factors', null, 'Controllers/P3p', null);
        $this->_addAco('control', null, 'Controllers/P3p', null);
        $this->_addAco('next_steps', null, 'Controllers/P3p', null);
        $this->_addAco('print_links', null, 'Controllers/P3p', null);
        $this->_addAco('log_next_step_view', null, 'Controllers/P3p', null);
        $this->_addAco('log_statistic_view', null, 'Controllers/P3p', null);
        $this->_addAco('edit', null, 'Controllers/P3p', null);
        $this->_addAco('overview', null, 'Controllers/P3p', null);
        $this->_addAco('whatdoyouthink', null, 'Controllers/P3p', null);
        
        $this->_addAco('Patients', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Patients', null);
        $this->_addAco('add', null, 'Controllers/Patients', null);
        $this->_addAco('edit', null, 'Controllers/Patients', null);
        $this->_addAco('view', null, 'Controllers/Patients', null);
        $this->_addAco('viewAll', null, 'Controllers/Patients', null);
        $this->_addAco('resetPassword', null, 'Controllers/Patients', null);
        $this->_addAco('changeUsername', null, 'Controllers/Patients', null);
        $this->_addAco('calendar', null, 'Controllers/Patients', null);
        $this->_addAco('deleteNote', null, 'Controllers/Patients', null);
        $this->_addAco('editNote', null, 'Controllers/Patients', null);
        $this->_addAco('delete', null, 'Controllers/Patients', null);
        $this->_addAco('changeDates', null, 'Controllers/Patients', null);
        $this->_addAco('checkAgainCalendar', null, 
	               'Controllers/Patients', null);
        $this->_addAco('noCheckAgain', null, 'Controllers/Patients', null);
        $this->_addAco('oneWeekFollowup', null, 'Controllers/Patients', null);
        $this->_addAco('oneMonthFollowup', null, 'Controllers/Patients', null);
        $this->_addAco('sixMonthFollowup', null, 'Controllers/Patients', null);
        $this->_addAco('accrualReport', null, 'Controllers/Patients', null);
        $this->_addAco('interested_report', null, 'Controllers/Patients', null);
        $this->_addAco('offStudy', null, 'Controllers/Patients', null);
        $this->_addAco('search', null, 'Controllers/Patients', null);
        $this->_addAco('consents', null, 'Controllers/Patients', null);
        $this->_addAco('activityDiary', null, 'Controllers/Patients', null);
        $this->_addAco('loginAs', null, 'Controllers/Patients', null);
        $this->_addAco('takeSurveyAs', null, 'Controllers/Patients', null);
        $this->_addAco('medications', null, 'Controllers/Patients', null);
        $this->_addAco('dashboard', null, 'Controllers/Patients', null);
        $this->_addAco('dashboard_pdf', null, 'Controllers/Patients', null);
        $this->_addAco('dashboardForSelf', null, 'Controllers/Patients', null);
        $this->_addAco('dashboardPdfForSelf', null, 'Controllers/Patients', null);
        $this->_addAco('createAppt', null, 'Controllers/Patients', null);

        $this->_addAco('Appointments', null, 'Controllers', null);
        $this->_addAco('add', null, 'Controllers/Appointments', null);
        $this->_addAco('edit', null, 'Controllers/Appointments', null);

        $this->_addAco('Clinicians', null, 'Controllers', null);
        $this->_addAco('add', null, 'Controllers/Clinicians', null);
        $this->_addAco('edit', null, 'Controllers/Clinicians', null);
        $this->_addAco('view', null, 'Controllers/Clinicians', null);
        $this->_addAco('viewAll', null, 'Controllers/Clinicians', null);
        $this->_addAco('changePriority', null, 'Controllers/Clinicians', null);
        $this->_addAco('generateWebkeys', null, 'Controllers/Clinicians', null);
        $this->_addAco('emailSurveyLink', null, 'Controllers/Clinicians', null);
        $this->_addAco('consents', null, 'Controllers/Clinicians', null);
        
        $this->_addAco('Journals', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Journals', null);
        $this->_addAco('create', null, 'Controllers/Journals', null);
        $this->_addAco('edit', null, 'Controllers/Journals', null);
        $this->_addAco('delete', null, 'Controllers/Journals', null);
        $this->_addAco('listForReadOnly', null, 'Controllers/Journals', null);
        $this->_addAco('updateText', null, 'Controllers/Journals', null);
        
        $this->_addAco('ActivityDiaries', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/ActivityDiaries', null);
        $this->_addAco('edit', null, 'Controllers/ActivityDiaries', null);
        $this->_addAco('test', null, 'Controllers/ActivityDiaries', null);
        $this->_addAco('read', null, 'Controllers/ActivityDiaries', null);
        
        $this->_addAco('Associates', null, 'Controllers', null);
        $this->_addAco('create', null, 'Controllers/Associates', null);
        $this->_addAco('delete', null, 'Controllers/Associates', null);
        $this->_addAco('edit', null, 'Controllers/Associates', null);
        $this->_addAco('phraseEntry', null, 'Controllers/Associates', null);
        $this->_addAco('edit_list', null, 'Controllers/Associates', null);
        
        $this->_addAco('AudioFiles', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/AudioFiles', null);
        $this->_addAco('upload', null, 'Controllers/AudioFiles', null);
        $this->_addAco('download', null, 'Controllers/AudioFiles', null);
        $this->_addAco('code', null, 'Controllers/AudioFiles', null);
        $this->_addAco('review', null, 'Controllers/AudioFiles', null);
        $this->_addAco('assignCoders', null, 'Controllers/AudioFiles', null);
        $this->_addAco('viewAll', null, 'Controllers/AudioFiles', null);
        $this->_addAco('viewMine', null, 'Controllers/AudioFiles', null);
        $this->_addAco('edit', null, 'Controllers/AudioFiles', null);

        $this->_addAco('Medications', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/Medications', null);
        $this->_addAco('edit', null, 'Controllers/Medications', null);
        
        $this->_addAco('DataAccess', null, 'Controllers', null);
        $this->_addAco('index', null, 'Controllers/DataAccess', null);
        $this->_addAco('data_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('scores_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('non_tscores_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('options_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('questions_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('demographics_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('intervention_dose_export', null, 'Controllers/DataAccess', null);
        $this->_addAco('time_submitted_export', null, 'Controllers/DataAccess', null);

        $this->_addAco('Charts', null, 'Controllers', null);
        $this->_addAco('assignCoders', null, 'Controllers/Charts', null);
        $this->_addAco('viewAll', null, 'Controllers/Charts', null);

        $this->_addAco('ChartCodings', null, 'Controllers', null);
        $this->_addAco('code', null, 'Controllers/ChartCodings', null);
        $this->_addAco('review', null, 'Controllers/ChartCodings', null);

    }

    /**
    *   Create the Aco tree for cross-user CRUD. 
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _buildAcoTreeForCrossUserEdits(){
        $this->_addAco('acoUsers', null, null, null);
        $this->_addAco('acoUsersAdmin', null, 'acoUsers', null);
        $this->_addAco('acoUsersCentralSupport', null, 'acoUsersAdmin', null);
        $this->_addAco('acoUsersResearchStaff', null, 'acoUsersCentralSupport', null);
        $this->_addAco('acoUsersClinicStaff', null, 'acoUsersResearchStaff', null);
        $this->_addAco('acoUsersFrontDeskStaff', null, 'acoUsersClinicStaff', null);
        $this->_addAco('acoUsersSurveyEditor', null, 'acoUsers', null);
        $this->_addAco('acoUsersResearcher', null, 'acoUsers', null);
        $this->_addAco('acoUsersAudioCoder', null, 'acoUsers', null);
        $this->_addAco('acoUsersParticipantTreatment', null, 'acoUsers', null);
        $this->_addAco('acoUsersParticipantControl', null, 
                            'acoUsersParticipantTreatment', null);
        $this->_addAco('acoUsersPatient', null, 
                            'acoUsersParticipantControl', null);
        $this->_addAco('acoUsersParticipantAssociate', null, 'acoUsers', null);
    }

    /**
    *   Grant acl permissions to aros for access to controllers & actions acos
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _assignAclPermissionsForControllers(){

        $this->Acl->allow('aroNonStaff', 'Controllers/Users/index'); 
        $this->Acl->allow('aroNonStaff', 'Controllers/Users/edit'); 
        $this->Acl->allow('aroNonStaff', 
                                'Controllers/Users/changePasswordOfAuthdUser'); 
        $this->Acl->allow('aroNonStaff', 'Controllers/Users/settings'); 
        $this->Acl->allow('aroNonStaff', 'Controllers/Users/updateTimeout'); 
        $this->Acl->allow('aroNonStaff', 'Controllers/Logs/add');

        $this->Acl->allow('aroStaff', 'Controllers/Users/index'); 
        $this->Acl->allow('aroStaff', 'Controllers/Users/edit'); 
        $this->Acl->allow('aroStaff', 
                                'Controllers/Users/changePasswordOfAuthdUser'); 
        $this->Acl->allow('aroStaff', 'Controllers/Users/settings'); 
        $this->Acl->allow('aroStaff', 'Controllers/Users/updateTimeout'); 
        $this->Acl->allow('aroStaff', 
                            'Controllers/Logs/add');
        $this->Acl->deny('aroStaff', 'Controllers/Users/index');


        $this->Acl->allow('aroPatient', 'Controllers/Surveys/index');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/new_session');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/show');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/restart');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/answer');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/complete');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/break_session');

        $this->Acl->allow('aroPatient', 'Controllers/Surveys/questionnaire');
        $this->Acl->allow('aroPatient', 'Controllers/Surveys/questionnaires');
        $this->Acl->allow('aroPatient', 
                        'Controllers/Surveys/log_click_to_external_resource');
        $this->Acl->allow('aroPatient', 
                        'Controllers/Surveys/log_teaching_tip_expansion');

        $this->Acl->allow('aroPatient', 
                            'Controllers/Results/index');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Results/show');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Results/showJournals');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Results/show_activity_diary_data');
        $this->Acl->allow('aroPatient', 
                        'Controllers/Results/log_click_to_external_resource');
        $this->Acl->allow('aroPatient', 
                        'Controllers/Results/log_teaching_tip_expansion');
        
        $this->Acl->allow('aroPatient', 
                        'Controllers/Teaching/index');
        $this->Acl->allow('aroPatient', 
                        'Controllers/Teaching/log_click_to_external_resource');
        $this->Acl->allow('aroPatient', 
                        'Controllers/Teaching/log_teaching_tip_expansion');
       
        $this->Acl->allow('aroPatient', 
                            'Controllers/Journals/index');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Journals/create');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Journals/edit');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Journals/updateText');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Journals/delete');

        $this->Acl->allow('aroPatient', 
                            'Controllers/Associates/create');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Associates/edit');
        $this->Acl->allow('aroPatient',
                            'Controllers/Associates/delete');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Associates/edit_list');

        $this->Acl->allow('aroPatient', 
                            'Controllers/Appointments/add');
        $this->Acl->allow('aroPatient', 
                            'Controllers/Appointments/edit');

        $this->Acl->allow('aroPatient', 'Controllers/Images/view');

        $this->Acl->allow('aroParticipantControl', 'Controllers/P3p/next_steps');
        $this->Acl->allow('aroParticipantControl', 'Controllers/P3p/print_links');

        $this->Acl->allow('aroParticipantTreatment',
                            'Controllers/ActivityDiaries');

        $this->Acl->allow('aroParticipantTreatment',
                            'Controllers/Teaching');

        $this->Acl->allow('aroParticipantTreatment', 
                            'Controllers/P3p');

        $this->Acl->allow('aroParticipantAssociate', 
                            'Controllers/Results/others');
        $this->Acl->allow('aroParticipantAssociate', 
                            'Controllers/Results/othersReportsList');
        $this->Acl->allow('aroParticipantAssociate', 
                            'Controllers/Results/showJournalsToOthers');
        $this->Acl->allow('aroParticipantAssociate', 
                            'Controllers/Results/showToOthers');
        $this->Acl->allow('aroParticipantAssociate', 
                            'Controllers/Associate/phraseEntry');
        
        $this->Acl->allow('aroParticipantAssociate', 
                            'Controllers/Journals/listForReadOnly');
        
        $this->Acl->allow('aroFrontDeskStaff', 
                            'Controllers/Admin/index');
        
        /**
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/index');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/add');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/view');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/viewAll');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/resetPassword');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/changeUsername');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients/calendar');
        */        
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Patients');

        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Appointments');

        $this->Acl->deny('aroClinicStaff',
                            'Controllers/Patients/dashboardForSelf');
        $this->Acl->deny('aroClinicStaff',
                            'Controllers/Patients/dashboardPdfForSelf');

        $this->Acl->allow('aroClinicStaff',
                            'Controllers/Admin/kiosk');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/MedicalRecords');
        
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Surveys/summary');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Surveys/reopen_test_session');
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Surveys/finish_test_session');
        
        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/ActivityDiaries/read');

        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Logs/index');

        $this->Acl->allow('aroClinicStaff', 'Controllers/Patients/oneWeekFollowup');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Patients/oneMonthFollowup');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Patients/sixMonthFollowup');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/new_session');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/show');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/restart');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/answer');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/complete');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/next_page');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/previous_page');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Surveys/complete');
        $this->Acl->allow('aroClinicStaff', 
                          'Controllers/Surveys/break_session');

        $this->Acl->allow('aroClinicStaff', 
                          'Controllers/Surveys/questionnaire');
        $this->Acl->allow('aroClinicStaff', 
                          'Controllers/Surveys/questionnaires');

        $this->Acl->allow('aroClinicStaff', 
                            'Controllers/Medications');
        $this->Acl->allow('aroClinicStaff', 'Controllers/Images/view');

        /**$this->Acl->allow('aroResearchStaff', 
                            'Controllers/Patients');*/
        $this->Acl->allow('aroResearchStaff', 
                            'Controllers/Surveys/generate_se_test');
        /**$this->Acl->allow('aroResearchStaff', 
                            'Controllers/Clinicians');*/
        /*$this->Acl->allow('aroResearchStaff', 
                            'Controllers/Surveys/summary');*/
        $this->Acl->allow('aroResearchStaff', 
                            'Controllers/Surveys/summary_csv');
        // research staff can do all audio files functions except assign coders
        /**$this->Acl->allow('aroResearchStaff', 
                            'Controllers/AudioFiles');
        $this->Acl->deny('aroResearchStaff', 
                            'Controllers/AudioFiles/assignCoders');
        $this->Acl->allow('aroResearchStaff', 
                            'Controllers/Charts/viewAll');
        $this->Acl->allow('aroResearchStaff', 
                            'Controllers/ChartCodings');*/

        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Admin/viewNonAdminUsers');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Admin/resetPassword');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Admin/createStaff');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Admin/viewDatabaseSnapshots');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Admin/saveDatabase');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Admin/reloadDatabase');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Results/data_export');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/DataAccess');
        /* central support can do all audio files functions 
	   including assign coders */
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/AudioFiles/assignCoders');
        $this->Acl->allow('aroCentralSupport', 
                            'Controllers/Charts/assignCoders');
        
        $this->Acl->allow('aroAdmin', 
                            'Controllers/Logs/index');

        $this->Acl->allow('aroResearcher', 
                            'Controllers/DataAccess');
        $this->Acl->allow('aroResearcher', 
                            'Controllers/Surveys/summary');

        $this->Acl->allow('aroSurveyEditor', 'Controllers/Surveys/edit');
        $this->Acl->allow('aroSurveyEditor', 'Controllers/Surveys/overview');
        $this->Acl->allow('aroSurveyEditor', 'Controllers/Surveys/edit_project');
        $this->Acl->allow('aroSurveyEditor', 'Controllers/Surveys/summary');
        
        $this->Acl->allow('aroSurveyEditor', 'Controllers/P3p/overview');
        $this->Acl->allow('aroSurveyEditor', 'Controllers/P3p/edit');
    }

    /**
    *   Assign acl permissions to aros for CRUD on (non self) users
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _assignAclPermissionsForCrossUserEdits(){

        $this->Acl->allow('aroFrontDeskStaff', 'acoUsersPatient', 'read');
        
        $this->Acl->allow('aroClinicStaff', 'acoUsersPatient', 
                            array('create', 'read', 'update'));

        $this->Acl->allow('aroResearchStaff', 'acoUsersParticipantTreatment', 
                            array('create', 'read', 'update'));

        $this->Acl->allow('aroCentralSupport', 'acoUsersResearchStaff', 
                            array('create', 'read', 'update'));
        
        $this->Acl->allow('aroAdmin', 'acoUsersAdmin', 'read');
        $this->Acl->allow('aroAdmin', 'acoUsersCentralSupport', 
                            array('create', 'read', 'update'));
        $this->Acl->allow('aroAdmin', 'acoUsersSurveyEditor', 
                            array('create', 'read', 'update'));
        $this->Acl->allow('aroAdmin', 'acoUsersResearcher', 
                            array('create', 'read', 'update'));
        $this->Acl->allow('aroAdmin', 'acoUsersAudioCoder', 
                            array('create', 'read', 'update'));

        $this->Acl->allow(
                    'aroParticipantAssociate', 'acoUsersParticipantTreatment', 
                            array('read'));
    }

    /**
    *   @author Justin McReynolds
    *   @version 0.2
    *   @param $parentAliasPath - If parent alias is not unique, pass the full path to parent, e.g. "Controllers/Users"; if parent alias is unique, simply pass that.
    */
    function _addAro($alias, $model = null,
                        $parentAliasPath = null, $foreignKey = null){

        $aro = $this->Acl->Aro;
        $parentId = null;

        // Find the direct parent ID
        if ($parentAliasPath != null){
            $tokens = String::tokenize($parentAliasPath, '/');
            foreach ($tokens as $parentAlias){
                $parent = $aro->findByAlias($parentAlias);
                $parentId = $parent['Aro']['id'];
            }
        }

        $aro->create();
        $aro->save(array(
            'model'=>$model,
            'foreign_key'=>$foreignKey,
            'parent_id'=>$parentId,
            'alias'=>$alias));
    }
 
    /**
    *   @author Justin McReynolds
    *   @version 0.2
    *   @param $parentAliasPath - If parent alias is not unique, pass the full path to parent, e.g. "Controllers/Users"; if parent alias is unique, simply pass that.
    */
    function _addAco($alias, $model = null,
                        $parentAliasPath = null, $foreignKey = null){

        $aco = $this->Acl->Aco;
        $parentId = null;

        // Find the direct parent ID
        if ($parentAliasPath != null){
            $tokens = String::tokenize($parentAliasPath, '/');
            foreach ($tokens as $parentAlias){
                $parent = $aco->findByAlias($parentAlias);
                $parentId = $parent['Aco']['id'];
            }
        }

        $aco->create();
        $aco->save(array(
            'model'=>$model,
            'foreign_key'=>$foreignKey,
            'parent_id'=>$parentId,
            'alias'=>$alias));
    }

    /**
    *   TODO: call directly
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _addUserAclLeaf($userId, $parentAlias){

        $this->User->addUserAclLeaf($userId, $parentAlias);
    }



    /**
    *   Add some user acl memberships for users for app init. 
    *   @author Justin McReynolds
    *   @version 0.1
    */
    function _addUserAclLeafsForInitUsers(){

        $this->_addUserAclLeaf('9', 'aclAdmin');
        $this->_addUserAclLeaf('5', 'aclAdmin');
        $this->_addUserAclLeaf('15', 'aclAdmin');
        $this->_addUserAclLeaf('10', 'aclParticipantTreatment');
        $this->_addUserAclLeaf('6', 'aclPatient');
        $this->_addUserAclLeaf('12', 'aclFrontDeskStaff');
        $this->_addUserAclLeaf('13', 'aclClinicStaff');
        $this->_addUserAclLeaf('14', 'aclCentralSupport');
        $this->_addUserAclLeaf('23', 'aclResearchStaff');
    }

}
