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
    define('INSTANCE_ID', 'sarcoma');
 
    /* Is this the production instance? */
    Configure::write('isProduction', false);

    // Control Minify view helper
    Configure::write('minify_js', Configure::read('isProduction'));
    //Configure::write('minify_js', true);

    // primary survey project
    //Configure::write('PROJECT_ID', 3);


/** SYSTEM STATE **********************************************************/
    /* Whether the system is currently down */
    define('SYSTEM_NORMAL', 'SYSTEM_NORMAL');
    define('SYSTEM_DOWN', 'SYSTEM_DOWN');
    define('SYSTEM_DOWN_IMMINENT', 'SYSTEM_DOWN_IMMINENT');
    define('SYSTEM_DOWN_IMMINENT_MESSAGE', 'Alert: this website will not be available today (Wednesday 7/8/09) between 5:30 and 7:30 PM Pacific, due to a scheduled maintenance event.)');
    define('SYSTEM_DOWN_MESSAGE', 'As of 5:30 PM Pacific today (7/8/09), the ESRAC Self Report Assessment for Cancer program is unavailable, due to a scheduled maintenance event.<br/><br/>We expect the program to be available again by 7:30 PM today. Thank you for your patience.');
    Configure::write('systemStatus',  SYSTEM_NORMAL);


/** SYSTEM-LEVEL VARS ********************************************************/
    /** The default format we use for datetimes (Mysql's preferred format) */
    define ('MYSQL_DATETIME_FORMAT', 'Y-m-d H:i:s');

    define ('SECURE_DATA_DIR', 'securedata');

    Configure::write('tempPwPostfix', '');

    /** Name of the database used for the test instance */
    define ('TEST_DB_NAME', 'esrac2_test');

    //define('GOOGLE_ANALYTICS_ACCT', '');

    Configure::write('mailServer', 'smtp.washington.edu');
    Configure::write('mailUsername', '');
    Configure::write('mailPassword', '');


/** PATIENT-COMMUNICATION CHANNEL VARIABLES **********************************/
    define('HELP_CLINIC_SPECIFIC', true);
    define('HELP_TELEPHONE_NUMBER', '1-888-211-3768');
    define('HELP_EMAIL_ADDRESS', 'esrachelp@rt.cirg.washington.edu');
    define('ADMIN_EMAIL_ADDRESS', 'esrachelp@rt.cirg.washington.edu');

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
            'targets',
            'results', // controller, not model 
            'teaching', // controller, not model
            'teaching_tips', 'teaching_tips_percentages',
            'journals',
            'journal_entries',
            'consents',
            /**'clinicians', 'clinician_races', 'clinician_notes'*/));

    // Use instance-specific model subclasses for these
    Configure::write(
        'instanceModelOverrides',
        array(
            //'patient'
    ));

    // Use instance-specific component subclasses for these
    Configure::write(
        'instanceComponentOverrides',
        array(
    ));

    /* $tabControllerActionMap: assoc array of available tabs. Each tab 
       consists of one entry in the associative array. Note that these may 
       be relabelled in TabHelper via tabControllerDisplayNameMap
     */
    Configure::write(
        'tabControllerActionMap', array(
                                    'My Home' => array('controller'=>'users',
                                                       'action'=>'index'),
                                    'Report My Experiences' => array('controller'=>'surveys',
                                                       'action'=>'index'),
                                    'View My Reports' => array('controller'=>'results',
                                                        'action' => 'index'),
                                    "View Reports" =>
                                                array('controller'=>'results',
                                                        'action' => 'others'),
                                    'Manage My Fatigue' =>
                                                array('controller'=>'teaching',
                                                        'action'=>'manage_fatigue'),
                                    'Manage My Symptoms' =>
                                                array('controller'=>'teaching',
                                                        'action'=>'index'),
                                    'Data Access' =>
                                                array('controller' => 'data_access',
                                                      'action'     => 'index'),
                                    'Editor' =>
                                                array('controller' => 'surveys',
                                                    'action' => 'overview'),
                                    'Patients' => array('controller' =>'patients',
                                                     'action' => 'calendar')));

    # tabOrderToDisplayForMostPages 
    Configure::write(
        'tabs_order_default',
        array("My Home", "Report My Experiences", 
                "View My Reports", 
                "View Reports", "Manage My Symptoms", 
                //"Share My Reports", 
                "Patients",
                "Data Access", "Editor"));

    // if defined and true, the survey items will be smaller. Use when more questions are 
    // needed per page.
    define('SURVEY_UI_SMALL', false);
    
    // whether clinic staff and above can log in as patients they can see
    define('LOGIN_AS_PATIENT_ALLOWED', true);

    // whether clinic staff and above can take surveys as patients they can see
    define('TAKE_SURVEY_AS_PATIENT_ALLOWED', false);

    // Enable patient notes (admins can add notes that patients view on login)
    define('PATIENT_NOTES', false);
    
    // Enable UW NetID option on login page
    define('UWNETID_LOGIN', false);
    
/** PATIENT RELATED **********************************************************/

    // whether all patients are post-treatment or not
    Configure::write('postTreatment', true);

    /** Id of site that requires HIPAA consent as well as study consent */
    define ('HIPAA_CONSENT_SITE_ID', 1);

    // If this subscale is at or above critical and the patients.consent_status is "usual care", change to "pre-consent"
    define('PRE_CONSENT_QUALIFIER_SUBSCALE', 34);

    define('INITIAL_PATIENT_ROLE', 'ParticipantTreatment');

    define('PATIENT_SELF_REGISTRATION', true);

    Configure::write('NEW_PATIENT_FIELDS_ORDERED', array(
        'User.first_name' => true, 
        'User.last_name' => true, 
        'User.username' => false, 
        'User.email' => true, 
        'Patient.MRN' => true,
        'Patient.test_flag' => false,
    ));    
    

/** SESSION LIFE REALTED *****************************************************/
    /*
     *  How many appointments can be created per patient
     *  null = no limit 
     */
    Configure::write("appointmentLimit", null);

    /**
     *  Whether survey sessions can be created which are not associated with 
     *  a particular appointment aka "nonT" sessions
     */
    define('ELECTIVE_SESSIONS', true);

    define('SESSION_PATTERN', 'PATIENT_DESIGNATED_APPTS');

    // How much time before the appt can a survey session be init'd or resumed
    define ('MIN_SECONDS_BETWEEN_APPTS', 172800); // 2 days
    // Survey sessions init'able until this time on appt day
    define('INITIALIZABLE_UNTIL', '23:00:00');
    // Survey sessions resumable until this time on appt day
    define('RESUMABLE_UNTIL', '23:00:00');

    define('SKIPPED_QUESTIONS_PAGE', 120);
    define('COMPLETE_BTN_PAGE', 119);

    define('EMAIL_STAFF_SESSION_FINISH', true); 

/** INSTANCE-SPECIFIC STRINGS ************************************************/
    // only echo'd for display
    define('SHORT_TITLE', "ESRA-C for Sarcoma");

    // default page <title>
    define('PAGE_TITLE', "ESRA-C for Sarcoma");
    
    // used for email 
    define('EMAIL_TITLE', "Electronic Self Report Assessment for Sarcoma Cancer (ESRA-C)");

    // Send registration emails on patient add
    define('AUTO_EMAIL_REGISTRATION', true);

    define('LOGIN_WELCOME', 'Welcome to the Electronic Self Report Assessment for Cancer (ESRA-C). This website gives patients with cancer a way to report health information to their care team.');
    
    define('SURVEY_HEADER', 'Report My Experiences');

/*****************************************************************************/
?>
