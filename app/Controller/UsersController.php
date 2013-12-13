<?php
/**
    * Users Controller
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
App::uses('CakeEmail', 'Network/Email');
class UsersController extends AppController
{
    var $components = array('Password', 'DhairDateTime'/**, 'Security'*/);
    var $uses = array("User", "UserAclLeaf", "Scale", "SurveySession",
                        "Patient", 'PatientViewNote', 'Clinic', 'Webkey');
    var $helpers = array("Html", "Form", "InstanceSpecifics");


    /**
     *
     */
    function beforeFilter() {
        parent::beforeFilter();
        //$this->log("users.beforeFilter(), after calling parent::beforeFilter()", LOG_DEBUG);
        
        //$this->Security->requirePut('edit');

        $this->Auth->autoRedirect = false; // allows us to log logins
    }// function beforeFilter() {

    /**
     *
     */
    function beforeRender() {
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('cpro.p3p.js'));
        parent::beforeRender();
    }
    
    /** 
     * About function
     *
     * Displays information about the survey, possibly tailored to the user
     */
    function about()
    {
        $this->TabFilter->show_normal_tabs(); 
        $this->render(CProUtils::getInstanceSpecificViewName(
            $this->name,
            $this->request->action
        ));
    }

    /**  
     * Contact function
     *
     * Displays user-specific contact information
     */
    function contact()
    {
        $this->TabFilter->show_normal_tabs(); 
        $this->render(CProUtils::getInstanceSpecificViewName(
            $this->name,
            $this->request->action
        ));
    }

    /**
     *
     */
    function help()
    {
        $this->TabFilter->show_normal_tabs();

        //data for the view
        $this->set(array(
            'firstName' => __("First Name: ") . $this->user['User']['first_name'],
            'lastName' =>  __("Last Name: ") . $this->user['User']['last_name'],
            'userName' => __("User Name: ") . $this->user['User']['username']
        ));
        
        $clinics = $this->Clinic->find('all');
        $this->set('clinics', $clinics);
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
        // Always check at this page, but don't redirect unless isProduction
                array('browser.detect.js', 
                        CProUtils::get_instance_specific_js_name('check.browser.compat') . '.js'));
        $this->render(CProUtils::getInstanceSpecificViewName(
            $this->name,
            $this->request->action
        ));
    }

    /** Index action
     *
     * before filter: loggedIn redirects to Home if logged in
     *
     * otherwise, display main layout with login screen
     */
    function index(){
        $this->set('array', $this->request->params);
        if ($this->user['User']['first_name'])
            $welcomeText = String::insert(__('Welcome, :first_name'), array('first_name' => $this->user['User']['first_name']));
        else
            $welcomeText = __('Welcome');
        if (
            in_array('associates', $this->modelsForThisDhairInstance) and
            $this->DhairAuth->checkWhetherUserBelongsToAro(
                $this->Auth->user('id'),
                'aroParticipantAssociate'
            )
        ){
            $this->loadModel('PatientAssociate');
            $welcomeText .= String::insert(
                __(' :patient_count patient(s) have given you permission to view their symptom charts. Please use the "View Reports" tab to view their charts or use the "Quick Links" on this page.'),
                    array(
                        'patient_count' =>
                            $this->PatientAssociate->countPatientsForAssociate($this->Auth->user('id'))
                    )
            );
            $welcomeText .= '<br/><br/>';
        }
        // TODO move this to initMyHomeTabNav ?
        $this->set('welcome_text', $welcomeText);
        
        // Use this to see if any sessions have been initiated
        if (isset($this->patient['SurveySession']))
            $this->set('start_check', count($this->patient['SurveySession']));
        else
            $this->set('start_check', 0);

        $this->TabFilter->selected_tab("My Home");
        $this->TabFilter->show_normal_tabs();

        if (!empty($this->patient)){

            $this->set('treatment', 
                $this->patient["Patient"]["study_group"] == Patient::TREATMENT);
                   
            if (!empty($this->patient['PatientViewNote'])) {
                $this->set('note', $this->patient['PatientViewNote'][0]);
            }
        }
        if (Configure::read('isProduction')){
            $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
                array('browser.detect.js', 
                        'check.browser.compat.' . INSTANCE_ID . '.js'));
        }
        $this->render(CProUtils::getInstanceSpecificViewName(
            $this->name,
            $this->request->action
        ));
    }

    /**
     *
     */
    function login() {
//        $this->log(__CLASS__ . '->' . __FUNCTION__ . "()", LOG_DEBUG);

        /** TODO read $flashMsg from cookie
        if ($flashMsg && in_array($flashMsg, $this->LOGIN_FLASH_MSGS)){
            $this->Session->setFlash(__($flashMsg));
        }
        */

        if ($this->Auth->login()) {
//            $this->log(__CLASS__ . '->' . __FUNCTION__ . '(), Auth->login() true', LOG_DEBUG);

            // save a key we can use to make cross-site request forgeries less likely
            $this->Session->write(self::ID_KEY, mt_rand());
            $user = $this->Auth->user();

            // $this->log("users.login " . $this->Session->read(self::ID_KEY) . " " . $user['User']['username'], LOG_DEBUG);
            // $this->log('user: '.print_r($user, true), LOG_DEBUG);

            // If this is a participant and there is missing activity diary entry data from the past week, alert and redirect to activity diary tab
            if ($this->DhairAuth->checkWhetherUserBelongsToAro(
                $user['id'], 'aroParticipantTreatment')){
                // $this->log('user/login(), user is aroParticipantTreatment', LOG_DEBUG);

                if (in_array("activity_diary_entries",
                                $this->modelsForThisDhairInstance)
                    && defined('ACTIVITY_DIARY_REDIRECT_ON_LOGIN')
                    && ACTIVITY_DIARY_REDIRECT_ON_LOGIN){
                    // $this->log('user/login(), activity_diary_entries in models, will search for recent next', LOG_DEBUG);
                    // $this->loadModel("ActivityDiaryEntries");
                    $this->loadModel("ActivityDiaryEntry");

                    if ($this->ActivityDiaryEntry->hasMissingDataPastWeek($this->Auth->user('id'))){
                        // TODO alert
                        $this->Session->setFlash('Your activity diary has missing entries over the past week; you can fill them in here.');
//                        $this->log(__CLASS__ . '->' . __FUNCTION__ . '(), redirecting to /activity_diaries', LOG_DEBUG);
                        return $this->redirect('/activity_diaries');
                    }
                }
            }

            if ($this->Auth->redirectUrl() == '/users/selfRegister'){
                $this->Auth->redirectUrl('/users/index');//avoiding loop
            }

//            $this->log(__CLASS__ . '->' . __FUNCTION__ . '(), redirecting to Auth->redirectUrl: ' . $this->Auth->redirectUrl(), LOG_DEBUG);
            return $this->redirect($this->Auth->redirectUrl());
        }// if ($this->Auth->login()) 
        else {
//            $this->log(__CLASS__ . '::' . __FUNCTION__ . '(), Auth->login() false', LOG_DEBUG);
            // don't want to timeout this page
            $this->set('timeout_for_layout', '');
            if ($this->request->is('post')) {
                if ($this->Session->read('auth_error')) {
                    $this->Session->setFlash($this->Session->read('auth_error'));
                    $this->Session->delete('auth_error');
                } else {
                    $this->Session->setFlash(__('The username and password did not match.'));
               }
            }
        }

        # $this->DhairAuth->_buildAcoTreeForControllers();
        $this->TabFilter->show_normal_tabs();
        $this->render(CProUtils::getInstanceSpecificViewName(
            $this->name,
            $this->request->action
        ));
//        $this->log(__CLASS__ . '->' . __FUNCTION__ . '(), exiting', LOG_DEBUG);
    }// function login() {


    /**
     *
     */
    function logout() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), heres request->params[url]: " . print_r($this->request->params, true), LOG_DEBUG);
        $flashMsg = "Logged out.";
        if (
            !empty($this->request->params['url']) && 
            !empty($this->request->params['url']['timeout'])
        ){
            $flashMsg = "Your session has timed out.";
//            $this->log("users.logout by timeout " . $this->Auth->user('id'));
        }
        // FIXME use cookie instead
/**        if ($this->Session->check(self::LOGOUT_FLASH)){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), LOGOUT_FLASH", LOG_DEBUG);
            $flashMsg = $this->Session->read(self::LOGOUT_FLASH);
        }
*/
        // FIXME This won't work because the Session will be whacked at logout.
        // TODO write $flashMsg to cookie instead
        //$this->Session->setFlash($flashMsg);

        $this->deleteVariablesOnLogout();
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), next is redirect to whatever Auth->logout() returns"/** . $this->Auth->logoutRedirect()*/, LOG_DEBUG);
        $this->redirect($this->Auth->logout());
    }

    /**
     * Change the password of the authenticated user
     *
     */
    /**
    FIXME No longer used, Settings tab replaced w/ Share My Reports
    function settings(){
        $this->initShareMyReportsTabNav();
    }
    */

    /**
     * Change the password of the authenticated user
     *
     */
    function changePasswordOfAuthdUser(){
        $this->TabFilter->show_normal_tabs(); 

        $pwSetErrMessage = $this->_validateAndSetPw();
        if (isset($pwSetErrMessage)){
            $baseMessage = 
                __('Sorry, your password was not successfully changed');

            if (empty($pwSetErrMessage)) {
                $this->Session->setFlash($baseMessage . '.');
            } else {
                $this->Session->setFlash($baseMessage . ":  $pwSetErrMessage");
            }
        }
        else if ($this->request->data) {
            $this->Session->setFlash(
                    __('Your password was successfully changed.'));

//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), redirecting to /users", LOG_DEBUG);
            $this->redirect("/users");

            // TODO clear session, and force re-login?
            //$this->redirect("/users/logout");
        }

        //data for the view
        $this->set(array(
            'minLength' => $this->Password->minPasswordLength(false),
            'minCharGroups' => $this->Password->minCharGroups(false),
            'userName' => $this->user['User']['username']
        ));

        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('jquery.validate.js', 'cpro.jquery.validate.js'));
    }// function changePasswordOfAuthdUser(){

    /**
     * If request data for password validates, set it.
     * @return null for okay, or error message
     */
    function _validateAndSetPw(){
        // $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's this->request->data:" . print_r($this->request->data, true), LOG_DEBUG);

        if (!isset($this->User->id) or !$this->User->id){
            if (isset($this->user))
                $this->User->id = $this->user['User']['id'];
            else if (!empty($this->request->data['User']))
                $this->User->id = $this->request->data['User']['id'];
        }
            
        $result = false;
        $errMsg = null;

        if (
            !empty($this->request->data)
            and !empty($this->request->data['User'])
            and $this->User->id
        ) {
            // $this->DhairLogging->logArrayContents($this->request->data, "data");
            if ($this->request->data['User']['password'] ==
                $this->request->data['User']['password_confirm'])
            {
                $pwSecurity = $this->Password->checkUserPassword(
                    $this->request->data['User']['password'],
                    $this->user['User']['password']);

                if ($pwSecurity['isSecure']) {
                    // $this->log(__CLASS__ . "." . __FUNCTION__ . "(), next is setPassword; heres this->Auth->password(" . $this->request->data['User']['password'] . "): " . $this->Auth->password($this->request->data['User']['password']), LOG_DEBUG);
                    $result = $this->User->setPassword(
                        $this->User->id,
                        $this->Auth->password(
                            $this->request->data['User']['password']));
                    if (!$result)
                        $errMsg = false;
                    // $this->log(__CLASS__ . "." . __FUNCTION__ . "(), heres result of setPassword: " . $result, LOG_DEBUG);
                } else {
                    if ($pwSecurity['duplicate']) {
                        $errMsg = __('New password is the same as the old.');
                    } else if ($pwSecurity['short']) {
                        $errMsg = __('New password is too short.');
                    } else { // not enough character groups
                        if ($this->is_staff) {
                            $errMsg = __('New password does not contain enough different types of characters.');
                        } else {
                            // make the errMsg easy for non-staff
                            $errMsg = __('New password must contain a letter and a number.');
                        }
                    }
                }
            } else {
                $errMsg = __('Your passwords did not match.');
            }

            $this->request->data['User']['password'] =
                $this->request->data['User']['password_confirm'] = '';
            // $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning errMsg: " . $errMsg, LOG_DEBUG);
            return $errMsg;
        }

    }// function _validateAndSetPw(){

    /**
     * Set the language of the current user by POSTing JSON
     * @param data[User][locale], the locale to change to, eg en_US
     */
    function setLanguageForSelf($lang=null){
        $this->autoRender = false;
        $this->layout = 'ajax';
        $this->header('Content-Type: application/json');

        if (! in_array("locale_selections", Configure::read('modelsInstallSpecific')))
            return false;

        $languages = Configure::read('i18nLanguages');
        $result = array(
            'ok' => false,
            // 'debug' => $this->data,
            'message' => 'response could not be saved',
        );

        if ($this->Auth->user()){
            $this->loadModel('LocaleSelection');
            if ($this->request->isGet()){
                $lang = $this->LocaleSelection->find(
                    'first',
                    array(
                        'conditions' => array('LocaleSelection.user_id' => $this->user['User']['id']),
                        'recursive' => -1,
                        'order' => array('LocaleSelection.time DESC'),
                ));
                $result['ok'] = true;
            }

            else if ($this->request->isPost()){
                $selectedLanguage = $this->request->data['User']['locale'];

                if ($languages && in_array($selectedLanguage, array_values($languages))) {
                    $lang = array(
                        'LocaleSelection' => array(
                            'user_id' => $this->user['User']['id'],
                            'locale' => $selectedLanguage,
                            'time' => $this->DhairDateTime->usersCurrentTimeStr(),
                    ));

                    $lang = $this->LocaleSelection->save($lang);
                    $this->Session->write('Config.language', $selectedLanguage);

                    // Delete session variables on language change (they are language-specific and need to be remade)
                    $this->Session->delete('statsForPatient.' . $this->user['User']['id']);
                    $this->Session->delete('factorsForPatient-' . $this->user['User']['id']);
                    $result['ok'] = true;
                }
            }
        }
        // User isn't logged in, write language to session variable
        else{
            if (
                $this->request->isGet() and
                $this->Session->check('Config.language')
            ){
                $lang = $this->Session->read('Config.language', $selectedLanguage);
                $result['ok'] = true;
            }
            else if ($this->request->isPost()){
                $selectedLanguage = $this->request->data['User']['locale'];
                if (in_array($selectedLanguage, array_values($languages))){
                    $this->Session->write('Config.language', $selectedLanguage);
                    $result['ok'] = true;
                    $lang = $selectedLanguage;
                }
            }
        }

        // $result['data'] = $lang;
        $result['message'] = $lang;

        $result = json_encode($result);
        echo $result;

        // $this->log('posted result: '.print_r($result, true), LOG_DEBUG);
    }// function setLanguageForSelf($lang=null){


    /**
     *   @author Justin McReynolds
     *   @version 0.1
     *   TODO couldn't call this from Patients.add, sql broke for some reason
     */
    function addUserAclLeaf($userId, $parentAlias){

        $this->UserAclLeaf->create();
        $this->UserAclLeaf->save(array(
            'user_id'=>$userId,
            'acl_alias'=>$parentAlias));
    }

    /**
     * update the timeout for a session
     * Should only be called via Javascript
     */
    function updateTimeout() {
        /* don't actually have to do anything, since AppController 
           updates the timeout automatically for every authorized action */
        $this->render('updateTimeout', 'ajax');
    }


    /**
     * @param @webkey User.webkey 
     */
    function selfRegister(){

        if ($this->user['User']['registered']){
            $this->Session->setFlash('Your account has already been registered');
                $this->redirect(array('controller' => 'users',
                                'action' => 'login'));
        } 

        $this->Session->write(self::ID_KEY, mt_rand());

        // check whether another user is already using this person's email
        // as a username. If so, set default to blank.
        $this->user['User']['username_tmp'] = $this->user['User']['username'];
        if ($this->user['User']['username_tmp'] == ''){
            $userNameClash = 
                $this->User->find('count', 
                            array('recursive' => false,
                                'conditions' => array(
                                    'User.email' => $this->user['User']['email'],
                                    /**'User.id <>' => 
                                    $this->request->data['User']['id']*/)));
            if ($userNameClash == 0){
                $this->user['User']['username_tmp'] = $this->user['User']['email'];
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's user before set:" . print_r($this->user, true), LOG_DEBUG);

        //data for the view
        $this->set(array(
            'minLength' => $this->Password->minPasswordLength(false),
            'user' => $this->user
        ));
        
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array('jquery.validate.js', 'cpro.jquery.validate.js'));

    }


    /**
     * Edit a patient 
     * RESTful 
     * PUT to this service 
     * @param "data[json]", a PUT param. Sample:
     *  {"patient_id": "6",  // required 
     *  "datetime": "2012-08-15 21:00:00",  // required; local i.e. not GMT
     *  "location": "clinic B, front desk",  // optional
     *  "staff_id": "99"}  // optional
     * @param "data[AppController][AppController_id]", a PUT param. Pass the "acidValue" js var.
     * @return json w/ fields "ok" (bool) and "message". Samples:
     *  {"ok": true, "message": "999 [the id of the new appt]"}
     *  {"ok": false, "message": "Appointments must be at least 48 hours apart."}
     */
    function edit($id){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() w/ args: " . print_r(func_get_args(), true), LOG_DEBUG);

        // request->data should already be cakephp data array format
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's request data:" . print_r($this->request->data, true), LOG_DEBUG);

        /**
        $json = $this->request->data['json'];
        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's json:" . $json, LOG_DEBUG);

        // json to cakephp data
        $userData = json_decode($json, true);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's userData constructed from json:" . print_r($userData, true), LOG_DEBUG);
        */

    }

    /**
     * RESTful service
     * Call w/ PUT
     */
    function registerEdit(){

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's request data:" . print_r($this->request->data, true), LOG_DEBUG);

        $result = array();

        $userToRegister = $this->User->find('first', array('recursive' => false,
                                    'conditions' => array(
                                        'User.id' => 
                                            $this->request->data['User']['id'],
                                        'User.registered' => null)));
        if (empty($userToRegister)){
            $this->response->statusCode(403);
            $result['ok'] = false;
            $result['message'] = 'No unregistered user found.';
        }
        else{
            $userNameClash = 
                $this->User->find('count', 
                            array('recursive' => false,
                                'conditions' => array(
                                    'User.username' => 
                                    $this->request->data['User']['username'],
                                    'User.id <>' => 
                                    $this->request->data['User']['id'])));
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's userNameClash:" . $userNameClash, LOG_DEBUG);
            if ($userNameClash != 0){
                $this->response->statusCode(403);
                $result['ok'] = false;
                $result['message'] = 'That username is not available.';
            }
            elseif(strlen($this->request->data['User']['username']) < 3) {
                $this->response->statusCode(403);
                $result['ok'] = false;
                $result['message'] = 'Username must be at least 3 characters long.';
            }
            else {
                $pwSetErrMessage = $this->_validateAndSetPw();
                if (isset($pwSetErrMessage)){
                    $this->response->statusCode(403);
                    $result['ok'] = false;
                    $result['message'] = $pwSetErrMessage;
                }
                else {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(), all clear, setting fields.", LOG_DEBUG);
                    $this->User->id = $this->request->data['User']['id'];
                    $this->User->saveField('username', 
                                            $this->request->data['User']['username']);
                    $this->User->saveField('registered', 
                                            $this->DhairDateTime->currentGmt());
                    $this->User->saveField('change_pw_field', 0);
    
                    $this->response->statusCode(200);
                    $result['ok'] = true;//fixme
                    $result['message'] = $this->User->id;
                }
            }
        }

        $this->set($result); 
        $this->set('_serialize', array('ok', 'message')); 

    }// function registerEdit(){

    /**
     *
     * RESTful service
     */
    function login_assist(){

        if (!defined('PATIENT_UNASSISTED_PW_RESET') 
                || !PATIENT_UNASSISTED_PW_RESET ){
            $this->response->statusCode(403);
            $result['ok'] = false;
            $result['message'] = '';
        }
        elseif ($this->request->isAjax()){
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), ajax request, heres the request data: " . print_r($this->request->data, true), LOG_DEBUG);

            $userToIdentify = $this->User->find('first', array('recursive' => false,
                                    'conditions' => array(
                                        'User.email' =>
                                            $this->request->data['User']['email']
                                        )));
            if (empty($userToIdentify)){
                $this->response->statusCode(403);
                $result['ok'] = false;
                $result['message'] = '';
            }
            else {
                $patientToIdentify = 
                    $this->Patient->find('first', 
                                        array('recursive' => false,
                                        'conditions' => array(
                                            'Patient.id' =>
                                                $userToIdentify['User']['id']
                                        )));
                if (empty($patientToIdentify)){
                    $this->response->statusCode(403);
                    $result['ok'] = false;
                    $result['message'] = '';
                }
                else {
                    $clinicEmail = ADMIN_EMAIL_ADDRESS;
                    $patientClinic = $this->Clinic->find('first', array('recursive' => false,
                                                            'conditions' => array(
                                                                'Clinic.id' => $userToIdentify['User']['clinic_id']
                                                            )));
                    
                    if(!empty($patientClinic ))
                        $clinicEmail = $patientClinic['Clinic']['support_email'];
                        
                    $webkey = array('Webkey' => array());
                    $webkey['Webkey']['purpose'] = 'login_assist';
                    $webkey['Webkey']['user_id'] = $userToIdentify['User']['id'];
                    $this->Webkey->save($webkey);

                    $webkey = $this->Webkey->findById($this->Webkey->id);
   
                    $email = new CakeEmail();
                    $email->template('login_assist')
                        ->emailFormat('html')
                        ->from(array($clinicEmail => SHORT_TITLE))
                        ->to($patientToIdentify['User']['email'])
                        ->subject('Login assistance for '.SHORT_TITLE);
                    $email->viewVars(array('patient' => $patientToIdentify, 
                                            'webkey' => $webkey));
                    $email->send();

//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "(), ajax request, doing saveField sent_on w/ this->Webkey->id: " . $this->Webkey->id, LOG_DEBUG);
                    $this->Webkey->saveField('sent_on', $this->DhairDateTime->currentGmt());
 
                    $this->response->statusCode(200);
                    $result['ok'] = true;
                    $result['message'] = '';
                }
            }

            $this->set($result);
            $this->set('_serialize', array('ok', 'message'));
        }

    }// function login_assist(){

    /**
     *
     */
    function identify($webkeyText = null){

        if (!$this->request->isAjax()){
            // $this->log(__CLASS__ . "." . __FUNCTION__ . "($webkeyText), not ajax.", LOG_DEBUG);

            $webkey = $this->Webkey->findByText($webkeyText);
            $loggedIn = $this->Auth->user();

            $user = $this->User->findById($webkey['Webkey']['user_id']);
            // $this->log(__CLASS__ . "." . __FUNCTION__ . "($webkeyText), found user:" . print_r($user, true), LOG_DEBUG);

            $purpose = $webkey['Webkey']['purpose'];

            // Redirect if webkey isn't valid
            if (!$webkey){
                $this->Session->setFlash(__('Not a valid URL'));
                $this->redirect(array('controller' => 'users','action' => 'help'));
            }

            if ($loggedIn){
                $this->Session->setFlash(__('You are already logged in'));
                $this->redirect(array('controller' => 'users','action' => 'help'));
            }

            if ($purpose == 'self-register' and $user['User']['registered']){
                $this->Session->setFlash(__('You are already registered'));
                $this->redirect(array('controller' => 'users','action' => 'help'));
            }

            if ($webkey['Webkey']['used_on'] and $purpose != 'anonymous_access'){
                $this->Session->setFlash(__('This login request has already been used'));
                $this->redirect(array('controller' => 'users','action' => 'help'));
            }

            if ($purpose == 'login_assist'){
                $one_day_after_sent_on 
                    = new DateTime($webkey['Webkey']['sent_on']);
                $one_day_after_sent_on->add(new DateInterval('P1D'));
                $currentGmt = new DateTime($this->DhairDateTime->currentGmt());
        
                if ($one_day_after_sent_on < $currentGmt) {

                    $this->Session->setFlash(__("You're responding to a login assistance email that was sent more than 24 hours ago, and is therefore no longer valid. Please submit your request for login assistance again."));
                    $this->redirect(array('controller' => 'users',
                                    'action' => 'help'));
                }
            }

            $this->Session->write(self::ID_KEY, mt_rand());

            if ($purpose == 'anonymous_access'){
                if ($user['User']['last_name']){
                    if ($user['User']['registered']){
                        $this->Session->setFlash(__('Please log in with your username and password. If you have forgotten or lost either, please click the "Help" button'));
                        $this->redirect('/');
                    }
                    else {
                        $this->Webkey->id = $webkey['Webkey']['id'];
                        $this->Webkey->saveField('used_on', gmdate(MYSQL_DATETIME_FORMAT));
                    }
                }
                else {
                    $this->Webkey->id = $webkey['Webkey']['id'];
                    $this->Webkey->saveField('used_on', gmdate(MYSQL_DATETIME_FORMAT));
                    $this->Auth->login(array('id' => $user['User']['id']));
                    $this->redirect(array(
                        'controller' => 'users',
                        'action' => 'index'
                    ));
                }
            }

            $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
                array('jquery.validate.js', 'cpro.jquery.validate.js'));
            $this->set(compact(
                'webkeyText',
                'user',
                'purpose'
            ));
        }

        else{ //ajax
            // $this->log(__CLASS__ . "." . __FUNCTION__ . "($webkeyText), ajax request, heres the request data: " . print_r($this->request->data, true), LOG_DEBUG);

            $userConditions = array(
                'User.id' =>
                    $this->request->data['User']['id'],
                'User.first_name' =>
                    $this->request->data['User']['first_name'],
                'User.last_name' =>
                    $this->request->data['User']['last_name']
            );

            $userToIdentify = $this->User->find('first', array(
                'conditions' => $userConditions,
                'recursive' => -1,
            ));

            // Attempt looser last_name matching
            if (!$userToIdentify){
                // Use alphabetical character set, may need to change to include some punctuation
                preg_match_all('/[A-Za-z]/', $userConditions['User.last_name'], $matches);
                $pattern = join('.?', $matches[0]);

                unset($userConditions['User.last_name']);
                $userConditions += array("last_name REGEXP '$pattern'");

                $userToIdentify = $this->User->find('first', array(
                    'conditions' => $userConditions,
                    'recursive' => -1,
                ));

            }

            // Attempt looser first_name and last_name matching
            if (!$userToIdentify){
                // Use alphabetical character set, may need to change to include some punctuation
                preg_match_all('/[A-Za-z]/', $userConditions['User.first_name'], $matches);
                $pattern = join('.?', $matches[0]);

                unset($userConditions['User.first_name']);
                $userConditions += array("first_name REGEXP '$pattern'");

                $userToIdentify = $this->User->find('first', array(
                    'conditions' => $userConditions,
                    'recursive' => -1,
                ));
            }

            $patientToIdentify =
                $this->Patient->find('first', array('recursive' => false,
                            'conditions' => array(
                                'Patient.id' =>
                                    $this->request->data['User']['id'],
                                'Patient.birthdate' =>
                                    $this->request->data['Patient']['birthdate']
                            )));
            $webkey = $this->Webkey->find('first', array('recursive' => false,
                            'conditions' => array(
                                'Webkey.user_id' =>
                                    $this->request->data['User']['id'],
                                'Webkey.text' =>
                                    $this->request->data['Webkey']['text']
                            )));
            if (empty($userToIdentify) 
                    || empty($patientToIdentify) || empty($webkey)){
                $this->response->statusCode(403);
                $result['ok'] = false;
                $result['message'] = '';
            }
            else {
                $this->Webkey->id = $webkey['Webkey']['id'];

                $this->Webkey->saveField('used_on', 
                                $this->DhairDateTime->currentGmt());

                $this->Auth->login(array(
                                "id" => $this->request->data['User']['id']));

                $this->response->statusCode(200);
                $result['ok'] = true;

                if ($webkey['Webkey']['purpose'] == 'login_assist'){

                    $this->User->id = $userToIdentify['User']['id'];
                    $this->User->saveField('change_pw_flag', 1);

                    $result['message'] = 'users/changePasswordOfAuthdUser';
                }
                else $result['message'] = 'users/selfRegister';
            }

            $this->set($result);
            $this->set('_serialize', array('ok', 'message'));
        }

    }// function identify($webkeyText = null){

}
?>    
