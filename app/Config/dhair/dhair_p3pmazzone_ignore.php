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
    define('INSTANCE_ID', 'p3pmazzone');

    /* Is this the production instance? */
    Configure::write('isProduction', false);

    // Control Minify view helper
    Configure::write('minify_js', Configure::read('isProduction'));
    //Configure::write('minify_js', true);


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
            'associates', 'patient_associates', 'patient_associates_subscales',
            //'audio_codings', 'audio_codings_categories', 'audio_files',
            //'charts', 'chart_codings', 'chart_codings_categories',
            //'categories', // model, not controller
            //'coded_items', //controller, not model
            'targets',
            'results', // controller, not model 
            'p3p_teaching',
            //'teaching', // controller, not model
            //'teaching_tips', 'teaching_tips_percentages',
            //'journals',
            //'journal_entries',
            'consents',
            /**'clinicians', 'clinician_races', 'clinician_notes'*/
            'locale_selections'
        ));

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
            'TabFilter'
    ));

    /* $tabControllerActionMap: assoc array of available tabs. Each tab 
       consists of one entry in the associative array. Note that these may 
       be relabelled in TabHelper via tabControllerDisplayNameMap
     */
    //translation note: these strings should be manually added to the default.po file
    Configure::write(
        'tabControllerActionMap', array(
                                    'Welcome' => array('controller'=>'users',
                                                       'action'=>'index'),
                                    'My Home' => array('controller'=>'users',
                                                       'action'=>'index'),
                                    'Build My P3P' => array('controller'=>'surveys',
                                                       'action'=>'index'),
                                    'View My P3P' => 
                                        array('controller'=>'p3p',
                                                'action'=>'index'),
                                    'Statistics' => 
                                        array('controller'=>'p3p',
                                                'action'=>'statistics'),
                                    'Important Factors' => 
                                        array('controller'=>'p3p',
                                                       'action'=>'factors'),
                                    'My Decision Role' => 
                                        array('controller'=>'p3p',
                                                       'action'=>'control'),
                                    'Prostate Cancer Information' => 
                                        array('controller'=>'p3p',
                                                       'action'=>'next_steps'),
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
        array("My Home", "Build My P3P",
                "View My P3P", "Important Factors", "Statistics", 
                    "My Decision Role", "Prostate Cancer Information", 
                "Patients",
                "Data Access", "Editor"));
    
    // if defined and true, the survey items will be smaller. Use when more questions are 
    // needed per page.
    define('SURVEY_UI_SMALL', false);
    
    // whether clinic staff and above can log in as patients they can see
    define('LOGIN_AS_PATIENT_ALLOWED', false);

    // whether clinic staff and above can take surveys as patients they can see
    define('TAKE_SURVEY_AS_PATIENT_ALLOWED', false);

    // Enable patient notes (admins can add notes that patients view on login)
    define('PATIENT_NOTES', false);
    
    // Enable UW NetID option on login page
    define('UWNETID_LOGIN', false);
    
/** PATIENT RELATED **********************************************************/

    // If this subscale is at or above critical and the patients.consent_status is "usual care", change to "pre-consent"
    define('PRE_CONSENT_QUALIFIER_SUBSCALE', 34);

    define('INITIAL_PATIENT_ROLE', 'ParticipantTreatment');

    define('PATIENT_UNASSISTED_PW_RESET', true);

    /**
     * Which fields should staff be able to view & edit for each patient,
     *  and the order in which to present them
     */
    Configure::write('PATIENT_FIELDS_ORDERED', array(
        'User.first_name', 'User.last_name', 'User.username',
        'User.clinic_id',
        'Patient.MRN', 'Patient.birthdate',
        'Patient.test_flag', 'Patient.phone1', 'Patient.phone2',
        'User.email',
        'Patient.mailing_address',
        'Patient.user_type'
    ));




/** SESSION LIFE RELATED *****************************************************/

    /*
     *  How many appointments can be created per patient
     *  null = no limit 
     */
    Configure::write("appointmentLimit", 1);

    // How much time before the appt can a survey session be init'd or resumed
    define ('MIN_SECONDS_BETWEEN_APPTS', 86400000); // 1000 days, would prefer infinite
    // Survey sessions init'able until this time on appt day
    define('INITIALIZABLE_UNTIL', '23:00:00');
    // Survey sessions resumable until this time on appt day
    define('RESUMABLE_UNTIL', '23:00:00');
    // If true, SurveySession .partial_finalization and .finished are treated identically
    define('SINGLE_STAGE_FINALIZATION', true);
    
    define('SKIPPED_QUESTIONS_PAGE', 120);
    define('COMPLETE_BTN_PAGE', 1473);

/** P3P-SPECIFIC STRINGS *****************************************************/

    // key: friendly string
    // element: locale code, used by gettext
    Configure::write('i18nLanguages', array('English' => 'en_US', 
                                            'Spanish' => 'es_MX'));

    define('VIDEO_WIDTH', 400);
    define('VIDEO_HEIGHT', 350);


/** INSTANCE-SPECIFIC STRINGS ************************************************/
    // only echo'd for display
    define('SHORT_TITLE', "P3P");
    
    // default page <title>
    define('PAGE_TITLE', "Personal Patient Profile");

    define('LOGIN_WELCOME', 'Welcome to the Personal Profile for Prostate (P3P). This website gives patients with prostate cancer a way to report health information to their care team.');
    
    define('ASK_STAFF_ABOUT_SURVEY', 'Please talk with clinic staff about building and viewing your P3P');

/*****************************************************************************/
?>

