<?php

/**
 *  
 * DHAIR-SPECIFIC CONFIGURATIONS
 * 
 * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
 *
 */

    // used to build var names 
    define('INSTANCE_ID', 'paintrackerrural');
 
    /* Is this the production instance? */
    Configure::write('isProduction', true);

    // Control Minify view helper
    Configure::write('minify_js', Configure::read('isProduction'));
    //Configure::write('minify_js', true);


/** SYSTEM STATE **********************************************************/
    /* Whether the system is currently down */
    define('SYSTEM_NORMAL', 'SYSTEM_NORMAL');
    define('SYSTEM_DOWN', 'SYSTEM_DOWN');
    define('SYSTEM_DOWN_IMMINENT', 'SYSTEM_DOWN_IMMINENT');
    define('SYSTEM_DOWN_IMMINENT_MESSAGE', 'Alert: this website will not be available today (Wednesday 7/8/09) between 5:30 and 7:30 PM Pacific, due to a scheduled maintenance event.)');
    define('SYSTEM_DOWN_MESSAGE', 'As of 5:30 PM Pacific today (7/8/09), the PainTracker™ program is unavailable, due to a scheduled maintenance event.<br/><br/>We expect the program to be available again by 7:30 PM today. Thank you for your patience.');
    Configure::write('systemStatus',  SYSTEM_NORMAL);


/** SYSTEM-LEVEL VARS ********************************************************/
    /** The default format we use for datetimes (Mysql's preferred format) */
    define ('MYSQL_DATETIME_FORMAT', 'Y-m-d H:i:s');

    define ('SECURE_DATA_DIR', 'securedata');
    define ('PATIENT_UNASSISTED_PW_RESET', true);

    Configure::write('tempPwPostfix', '_123');

    /** Name of the database used for the test instance */
    define ('TEST_DB_NAME', 'esrac2_test');

    define('GOOGLE_ANALYTICS_ACCT', '');

    Configure::write('mailServer', 'smtp.washington.edu');
    Configure::write('mailUsername', '');
    Configure::write('mailPassword', '');


/** PATIENT-COMMUNICATION CHANNEL VARIABLES **********************************/
    define('HELP_CLINIC_SPECIFIC', true);
    define('HELP_TELEPHONE_NUMBER', '1-888-211-3768');
    define('HELP_EMAIL_ADDRESS', 'esrachelp@rt.cirg.washington.edu');
    define('STAFF_GUIDE', 'http://goo.gl/rSUTd');
    define('USER_GUIDE', 'https://docs.google.com/document/d/1vNacOkA7VKaL-cimxheR88WSKKGAFPOUQ4ENOy7djJc/pub');
    define('ADMIN_EMAIL_ADDRESS', 'mcjustin@uw.edu');

    Configure::write('email_patient_survey_prompt_interval', 2);


/** FUNCTIONALITY-DEFINING VARIABLES *****************************************/
    /**
    * Which MVC this DHAIR instance uses beyond the modelsDhairCore found in the AppController. If an instance doesn't use a particular MVC set, that set's models php files and DB tables can be deleted from that instance.
    */
    Configure::write(
        'modelsInstallSpecific',
        array(
            //'activity_diaries', 'activity_diary_entries',
            'appointments',
            //'associates', 'patient_associates', 'patient_associates_subscales',
            //'audio_codings', 'audio_codings_categories', 'audio_files',
            //'charts', 'chart_codings', 'chart_codings_categories',
            //'categories', // model, not controller
            //'coded_items', //controller, not model
            'medications',
            'other_medications',
            //'targets',
            'results', // controller, not model 
            //'teaching', // controller, not model
            //'teaching_tips', 'teaching_tips_percentages',
            //'journals',
            //'journal_entries',
            //'consents',
            /**'clinicians', 'clinician_races', 'clinician_notes'*/));

    // Use instance-specific model subclasses for these
    Configure::write(
        'instanceModelOverrides',
        array(
            'patient',
            'survey_session',
    ));

    // Use instance-specific component subclasses for these
    Configure::write(
        'instanceComponentOverrides',
        array(
            'TabFilter'
    ));

    /* $tabControllerActionMap: assoc array of available tabs. Each tab 
       consists of one entry in the associative array. Note that these may 
       be relabelled in TabHelper via tabControllerDisplayNameMap
     */
    Configure::write(
        'tabControllerActionMap', array(
                                    'Welcome' => array('controller'=>'users',
                                                       'action'=>'index'),
                                    'My Home' => array('controller'=>'users',
                                                       'action'=>'index'),
                                    'Report My Experiences' => array('controller'=>'surveys',
                                                       'action'=>'index'),
                                    'View My Reports' => array('controller'=>'results',
                                                        'action' => 'index'),
                                    'Data Access' =>
                                                array('controller' => 'data_access',
                                                      'action'     => 'index'),
                                    'Editor' =>
                                                array('controller' => 'surveys',
                                                    'action' => 'overview'),
                                    'Dashboard' =>
                                                array('controller' => 'patients',
                                                    'action' => 'dashboardForSelf'),
                                    'Patients' => array('controller' =>'patients',
                                                     'action' => 'calendar')));

    # tabOrderToDisplayForMostPages 
    Configure::write(
        'tabs_order_default',
        array("My Home", "Report My Experiences", 
                "Patients",
                "Data Access", "Editor", 'Dashboard'));

    Configure::write('quickLinks', array(
            'Patients' => array(
                    "View Patient Records" =>
                                array("controller" => "patients",
                                      "action" => "viewAll"),
                    "Patient Search" =>
                                array("controller" => "patients",
                                      "action" => "search"),
                    "New Patient Record" =>
                                array("controller" => "patients",
                                      "action" => "add")
            ),
            "To Do" => array(
                    "Appointment Calendar" =>
                                array("controller" => "patients",
                                      "action" => "calendar"),
                    // "Check Agains" =>
                                // array("controller" => "patients",
                                      // "action" => "checkAgainCalendar"),
                    // "No Check Agains" =>
                                // array("controller" => "patients",
                                      // "action" => "noCheckAgain"),
                    // "Consent Verification" =>
                                // array("controller" => "patients",
                                      // "action" => "consents")
            ),
            // "Accrual" => array(
                    // "Accrual Report" =>
                                // array("controller" => "patients",
                                      // "action" => "accrualReport"),
                    // "Participant Status Report" =>
                                // array("controller" => "patients",
                                      // "action" => "offStudy")
            // ),
            "Staff" => array(
                    "View Staff Users" =>
                                array("controller" => "admin",
                                      "action" => "viewNonAdminUsers")
            ),
            // "Kiosk Mode" => array(
                    // "Configure browser for kiosk mode" =>
                                // array("controller" => "admin",
                                      // "action" => "kiosk")
            // )
        ));// Configure::write('quickLinks', array(

    // Whether to show the patient dashboard; default: false
    define('DASHBOARD', true);

    // Whether to show PHI in patient dashboard; default: true
    define('DASHBOARD_PHI', false);

    // if defined and true, the survey items will be smaller. Use when more questions are 
    // needed per page.
    define('SURVEY_UI_SMALL', true);

    // if defined and false, the survey progress bar will be excluded
    // TODO IMPL
    define('PROGRESS_BAR', false);

    // whether clinic staff and above can log in as patients they can see
    define('LOGIN_AS_PATIENT_ALLOWED', true);

    // whether clinic staff and above can take surveys as patients they can see
    define('TAKE_SURVEY_AS_PATIENT_ALLOWED', true);

    // Enable patient notes (admins can add notes that patients view on login)
    define('PATIENT_NOTES', true);
    
    // Enable UW NetID option on login page
    define('UWNETID_LOGIN', true);

/** PATIENT RELATED **********************************************************/

    // Whether this system uses study mgmt functionality
    define('STUDY_SYSTEM', true);

    define('INITIAL_PATIENT_ROLE', 'Patient');

    define('INITIAL_CONSENT_STATUS', 'pre-consent');

    define('MANUAL_RANDOMIZATION', true);

    /**
     * Which fields should staff be able to view & edit for each patient,
     *  and the order in which to present them
     */
    Configure::write('PATIENT_FIELDS_ORDERED', array(
        'User.first_name',
        'User.last_name',
        'User.username',
        'User.clinic_id',
        'User.email',

        'Patient.MRN',
        'Patient.birthdate',
        'Patient.gender',
        'Patient.test_flag',
        'Patient.phone1',
        'Patient.phone2',
        'Patient.mailing_address',
        'Patient.eligible_flag',
    ));

    /**
     * Which fields should staff specify for a new patient,
     * and the order in which to present them
     *
     * Note:  1. User.first_name, User.last_name and Patient.MRN must be
     *           included
     *        2. PatientsController::checkBasicData does validation on
     *           required fields; check that any new fields added to this
     *           list are accounted for in this function
     */
    Configure::write('NEW_PATIENT_FIELDS_ORDERED', array(
        'User.first_name' => true,
        'User.last_name' => true,
        'Patient.birthdate' => false,
        'Patient.MRN' => true,
        'Patient.gender' => true,
        'Patient.test_flag' => false,
        // 'Appointment.0' => true
    ));

    /** Maximum number of points in the dashboard graphs */
    define('DASHBOARD_POINTS', 12);
/** SESSION LIFE RELATED *****************************************************/
    /*
     *  How many appointments can be created per patient
     *  null = no limit 
     */
    Configure::write("appointmentLimit", 1);

    // How much time before the appt can a survey session be init'd or resumed
    define ('MIN_SECONDS_BETWEEN_APPTS', 259200); // 3 days
    // Survey sessions init'able until this time on appt day
    define('INITIALIZABLE_UNTIL', '23:00:00');
    // Survey sessions resumable until this time on appt day
    define('RESUMABLE_UNTIL', '23:00:00');

    define('SINGLE_STAGE_FINALIZATION', true);

    define('SKIPPED_QUESTIONS_PAGE', 362);

    define('SESSION_PATTERN', 'INTERVAL_BASED_SESSIONS');
    
    // define('EMAIL_STAFF_SESSION_FINISH', true);

/** INSTANCE-SPECIFIC STRINGS ************************************************/
    // only echo'd for display
    define('SHORT_TITLE', "PainTracker™");

    // default page <title>
    define('PAGE_TITLE', "PainTracker™");
    
    define('LOGIN_WELCOME', 'PainTracker™ is an easy-to-use, web-based service that helps clinicians track and improve the core outcomes of chronic pain management.<br/><br/>
In order to access and use PainTracker™, you will need login credentials. If you are a provider, please contact: Cara Towle, RN MSN, Director, TeleHealth Services, Health Services, University of Washington at ctowle@uw.edu. ');
    //define('LOGIN_WELCOME', 'Welcome to PainTracker™.');
    
    //DEPRECATED define('SURVEY_HEADER', 'Report My Experiences');
    
/*****************************************************************************/
?>
