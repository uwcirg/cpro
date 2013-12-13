<?php

/**
 *  
 * DHAIR-SPECIFIC CONSTANTS
 *  
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
 *
 */
    // survey_sessions.type
    define('APPT', 'APPT');
    define('NON_APPT', 'NON_APPT');
    define('ELECTIVE', 'ELECTIVE');
    define('ODD_WEEK', 'ODD_WEEK');
    define('EVEN_WEEK', 'EVEN_WEEK');
    define('EVEN_WEEK_8', 'EVEN_WEEK_8');
    define('EVEN_WEEK_12', 'EVEN_WEEK_12');

    define('SURVEY_SESSION_JUST_FINISHED', 'SURVEY_SESSION_JUST_FINISHED');
    define('INTERVAL_BASED_SESSIONS', 'interval_based');
    define('APPT_BASED_SESSIONS', 'appt_based_sessions');
    define('PATIENT_DESIGNATED_APPTS', 'patient_designated_appts');

    // eg Activity Diary 
    define('NON_FXNL_SCALE_ID', -99);

    define('PHQ9_SCALE', 4);
    define('PHQ9_SUBSCALE', 24);
    define('PHQ9_ALERT_ITEM', 40);
    define('PHQ9_LITTLE_INTEREST_ITEM', 32);
    define('PHQ9_FEELING_DOWN_ITEM', 33);

    define('PROMIS_FATIGUE_SCALE', 8);
    define('PROMIS_FATIGUE_SUBSCALE', 35);
    define('PROMIS_FEEL_FATIGUED_ITEM', 72);
    define('PROMIS_HOW_FATIGUED_ITEM', 73);
    define('PROMIS_FATIGUE_RUN_DOWN_ITEM', 74);
    define('PROMIS_FATIGUE_TROUBLE_STARTING_ITEM', 75);

    define('PROMIS_PAIN_SCALE', 7);
    define('PROMIS_PAIN_SUBSCALE', 31);

    define('PINS_SCALE', 5);
    define('PINS_SUBSCALE', 27);

    define('FINS_SCALE', 10);
    define('FINS_SUBSCALE', 34);

    define('QOL_SCALE', 2);
    define('QOL_PHYSICAL_SUBSCALE', 16);
    define('QOL_EMOTIONAL_SUBSCALE', 17);
    define('QOL_SOCIAL_FAM_SUBSCALE', 18);
    define('QOL_WORK_LEISURE_SUBSCALE', 19);
    define('QOL_COGNITIVE_SUBSCALE', 20);
    define('QOL_FINANCIAL_SUBSCALE', 32);
    define('QOL_OVERALL_SUBSCALE', 33);

    define('NEURO_SCALE', 3);
    define('SENSORY_SUBSCALE', 21);
    define('MOTOR_SUBSCALE', 22);
    define('AUTO_SUBSCALE', 23);

    define('SKIN_SCALE', 6);
    define('SKIN_SUBSCALE', 28);

    define('RELIGION_SCALE', 'religion');
    define('RELIGION_IMPORTANT_Q', 1036);
    define('CHAPLAIN_VISIT_Q', 1039);

    define('SDS_SCALE', 1);
    define( 'NAUSEA_FREQUENCY_SUBSCALE', 29);
    define( 'NAUSEA_INTENSITY_SUBSCALE', 2);
    define( 'APPETITE_SDS_SUBSCALE', 4);
    define( 'INSOMNIA_SUBSCALE', 7);
    define( 'PAIN_FREQUENCY_SUBSCALE', 1);
    define( 'PAIN_INTENSITY_SUBSCALE', 30);
    define( 'FATIGUE_SDS_SUBSCALE', 3);
    define( 'BOWEL_PATTERN_SUBSCALE', 5);
    define( 'CONCENTRATION_SUBSCALE', 6);
    define( 'APPEARANCE_SUBSCALE', 9);
    define( 'IMPACT_ON_SEX_SUBSCALE', 11);
    define( 'BREATHING_SDS_SUBSCALE', 14);
    define( 'OUTLOOK_SUBSCALE', 13);
    define( 'COUGH_SUBSCALE', 12);
    define( 'FEVER_CHILLS_SUBSCALE', 15);

    define('SYMPTOMS_SCALE', 9);
    define('NAUSEA_VOMITING_SUBSCALE', 37);
    define('PAIN_SUBSCALE', 38);
    define('BREATHING_SYMPTOM_SUBSCALE', 39);
    define('SLEEPING_SUBSCALE', 40);
    define('APPETITE_SYMPTOM_SUBSCALE', 41);
    define('CONSTIPATION_SUBSCALE', 42);
    define('DIARRHEA_SUBSCALE', 43);
    define('SEXUALITY_SUBSCALE', 44);
    define('FATIGUE_SYMPTOM_SUBSCALE', 45);

    define( 'OPEN_TEXT_Q', 114);

    define('RANKING_Q', 999);

    define('PARTICIPATION_QUESTION', 1000);
    define('PARTICIPATION_QUESTION_NEW', 1011);
    define('PARTICIPATION_YES_OPTION', 4168);
    define('PARTICIPATION_YES_OPTION_NEW', 4247);

    define('DEMOGRAPHICS_QNR', 7);
    define('GENDER_QUESTION', 78);
    define('HISPANIC_QUESTION', 112);
    define('RACE_QUESTION', 98);

    define ('TEACHING_TIPS_PAGE', 315);

    /** P3P Specific */
    define('P3P_BASELINE_PROJECT', 3);
    define('P3P_ELIGIBILITY_PROJECT', 6);
    define('P3P_BASELINE_CLINICAL_PROJECT', 7);
    define('P3P_1_MO_FU_PROJECT', 4);
    define('P3P_6_MO_FU_PROJECT', 5);
    define('CONTROL_PREFERENCE', 1739);
    define('BIRTHYEAR_QUESTION', 1952);
    define('AGE_GROUP_QUESTION', 2052);
    define('AGE_GROUP_ELDER_MIN_SEQUENCE', 3);
    define('LATINO_QUESTION', 2053);
    define('LATINO_NO', 7102);
    define('RACE_QUESTION_P3P', 1519);
    define('RACE_OPTION_CAUCASIAN', 5066);
    define('RACE_OPTION_ASIAN', 5067);
    define('RACE_OPTION_ISLANDER', 5068);
    define('RACE_OPTION_AFRICAN_AMERICAN', 5069);
    define('RACE_OPTION_NATIVE_AMERICAN', 5070);
    define('RACE_OPTION_MESTIZO', 7101);

    define('INFLUENTIAL_FACTORS_PERSONAL_PROFILE_SCALE', 11);
    define('INFLUENTIAL_FACTORS_SYMPTOMS_SCALE', 12);
    define('OUTCOMES_SUBSCALE', 46);
    define('DEFAULT_STAT', 92);
    define('EPIC_SCALE', 13);
    define('URINARY_INCONTINENCE_EPIC_SUBSCALE', 50);
    define('URINARY_IRRITATION_EPIC_SUBSCALE', 51);
    define('BOWEL_EPIC_SUBSCALE', 52);
    define('SEXUALITY_EPIC_SUBSCALE', 53);
    define('VITALITY_EPIC_SUBSCALE', 54);

?>
