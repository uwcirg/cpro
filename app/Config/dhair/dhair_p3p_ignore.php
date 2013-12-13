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
    define('INSTANCE_ID', 'p3p');

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

    define ('LAY_UI_DATETIME_FORMAT', 'm/d/Y g:i a');

    define ('SECURE_DATA_DIR', 'securedata');

    Configure::write('tempPwPostfix', '');

    /** Name of the database used for the test instance */
    define ('TEST_DB_NAME', 'esrac2_test');

    //define('GOOGLE_ANALYTICS_ACCT', '');

    Configure::write('mailServer', 'smtp.washington.edu');
    Configure::write('mailUsername', '');
    Configure::write('mailPassword', '');

    define('IP_ADDRESS_OBFUSCATOR', '/usr/local/bin/hipaa-hash-single.plx');

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
            'consents'
            /**'clinicians', 'clinician_races', 'clinician_notes'*/
            //'locale_selections'
        ));

    // Use instance-specific model subclasses for these
    Configure::write(
        'instanceModelOverrides',
        array(
            'patient',
            'survey_session'
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
                                    'My Top Concerns' => 
                                        array('controller'=>'p3p',
                                                       'action'=>'factors'),
                                    'My Decision Role' => 
                                        array('controller'=>'p3p',
                                                       'action'=>'control'),
                                    'More About Prostate Cancer' => 
                                        array('controller'=>'p3p',
                                                       'action'=>'next_steps'),
                                    "What Do You Think?" =>
                                        array('controller' => 'p3p',
                                                'action' => 'whatdoyouthink'),
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
                "My Top Concerns", "Statistics", 
                    "My Decision Role", "More About Prostate Cancer", 
                "What Do You Think?",
                "Patients",
                "Data Access", "Editor"));
   
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
                "Check Agains" =>
                            array("controller" => "patients",
                                  "action" => "checkAgainCalendar"),
                "Past Check Agains" =>
                            array("controller" => "patients",
                                  "action" => "noCheckAgain"),
                "Consent Verification" =>
                            array("controller" => "patients",
                                  "action" => "consents"),
                'One Week Follow-up Report' =>
                            array('controller' => 'patients',
                                  'action' => 'oneWeekFollowup'),
                'One Month Follow-up Report' =>
                            array('controller' => 'patients',
                                  'action' => 'oneMonthFollowup'),
                'Six Month Follow-up Report' =>
                            array('controller' => 'patients',
                                  'action' => 'sixMonthFollowup')
        ),
        "Accrual" => array(
                "Accrual Report" =>
                            array("controller" => "patients",
                                  "action" => "accrualReport"),
                "Participant Status Report" =>
                            array("controller" => "patients",
                                  "action" => "offStudy")
        ),
        "Staff" => array(
                "View Staff Users" =>
                            array("controller" => "admin",
                                  "action" => "viewNonAdminUsers")
        ),
        "Kiosk Mode" => array(
                "Configure browser for kiosk mode" =>
                            array("controller" => "admin",
                                  "action" => "kiosk")
        )
    ));// Configure::write('quickLinks', array( 

 
    // if defined and true, the survey items will be smaller. Use when more questions are 
    // needed per page.
    define('SURVEY_UI_SMALL', false);
    
    // whether clinic staff and above can log in as patients they can see
    define('LOGIN_AS_PATIENT_ALLOWED', true);

    // whether clinic staff and above can take surveys as patients they can see
    define('TAKE_SURVEY_AS_PATIENT_ALLOWED', false);
    
    // Use chevron arrows in nav (to show sense of steps)
    define('USE_CHEVRONS', true);
    
    // Use guided tours as an alternative to separate help pages
    define('SHOW_TOUR', false);
    
    // Enable patient notes (admins can add notes that patients view on login)
    define('PATIENT_NOTES', false);
    
    // Enable UW NetID option on login page
    define('UWNETID_LOGIN', false);

/** PATIENT RELATED **********************************************************/

    // Whether this system uses study mgmt functionality (high-level)
    define('STUDY_SYSTEM', true);
    // Whether this system has a workflow around eligibility
    define('ELIGIBILITY_WORKFLOW', true);

    /* If enabled:
     *   generates a webkey on patient creation
     *   shows webkey+link on patient record
     */
    define('PATIENT_ANONYMOUS_ACCESS', true);

    // If this subscale is at or above critical and the patients.consent_status is "usual care", change to "pre-consent"
    //TODO remove? define('PRE_CONSENT_QUALIFIER_SUBSCALE', 34);

    define('INITIAL_CONSENT_STATUS', 'pre-consent');

    define('PATIENT_SELF_REGISTRATION', true);

    define('PATIENT_UNASSISTED_PW_RESET', true);

    define('CLINICIAN_REPORT_PROJECT_ID', 3);

    /*
     * Which Views/Elements to include in Patients/edit, and their position.
     */
    Configure::write('PATIENT_EDIT_ELEMENTS', array(
        'patient',
        'study',
        'notes',
        'language',
        'emails',
        'appointments',
        'anonymous_access',
        'check_again',
        '1_wk_fu',
        '1_mo_fu',
        '6_mo_fu',
    ));


    /**
     * Which fields should staff be able to view & edit for each patient,
     *  and the order in which to present them
     */
    Configure::write('PATIENT_FIELDS_ORDERED', array(
        'User.first_name',
        'User.last_name',
        'User.username',
        'User.clinic_id',

        'Patient.MRN',
        'Patient.birthdate',
        'Patient.test_flag',
        'Patient.phone1',
        'Patient.phone2',
        'User.email',
        'Patient.mailing_address',
        // 'Patient.wtp_status',

        'Patient.alt_contact_name',
        'Patient.alt_contact_relation',
        'Patient.alt_contact_phone',
        'Patient.alt_contact_email',
    ));

    Configure::write('NEW_PATIENT_FIELDS_ORDERED', array(
        // 'User.first_name' => true,
        // 'User.last_name' => true,
        // 'Patient.birthdate' => true,
        // 'Patient.MRN' => true,
        // 'Patient.external_study_id' => false,
        // 'User.username' => false,
        'Patient.check_again_date' => true,
        // 'User.email' => false,
        // 'Patient.test_flag' => false,
        'Appointment.0' => false,
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

    // List of clinic ids to allow language selection on
    Configure::write('multiLangClinics', array());

    define('VIDEO_WIDTH', 400);
    define('VIDEO_HEIGHT', 350);
    define('REGISTRATION_REMINDER', true);


/** INSTANCE-SPECIFIC STRINGS ************************************************/
    // only echo'd for display
    define('SHORT_TITLE', "P3P");
    
    // default page <title>
    define('PAGE_TITLE', "Personal Patient Profile");

    define('LOGIN_WELCOME', 'Welcome to the Personal Patient Profile for Prostate (P3P)');
    
    define('ASK_STAFF_ABOUT_SURVEY', 'Please talk with clinic staff about building and viewing your P3P');

/*****************************************************************************/
?>

