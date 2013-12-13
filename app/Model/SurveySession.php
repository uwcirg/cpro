<?php
/** 
    * SurveySession
    *
    * Models a survey session, with answers entered by a patient,
    * logged in through a user, and eventually calculated for scales.
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

App::uses('CakeEmail', 'Network/Email');
class SurveySession extends AppModel
{
    var $name = "SurveySession";
    var $useTable = 'survey_sessions';
        
    var $belongsTo = array('Project', 'User', 'Patient', 'Appointment');
    var $hasMany = array('SessionItem' => array('dependent' => true), 
                         'SessionScale' => array('dependent' => true), 
			 'SessionSubscale' => array('dependent' => true));

    var $order = array('SurveySession.id' => 'asc');

    /** static list */
    var $ARRAY_TYPES = array(APPT, NON_APPT, ELECTIVE, ODD_WEEK, 
                                EVEN_WEEK, EVEN_WEEK_8, EVEN_WEEK_12);

    /** 
     * Value indicating a survey session is a non-T session 
     *  @deprecated see ELECTIVE_SESSIONS 
     */
    const NONT = 'nonT';
    /** 
    *   A session which was a T, but later determined to be errant.
    *   An errantT session is not launchable or resumable.
    *   They will be reportable in "View My Reports" charts tho, 
    *       just as nonT's are.
    *   They will not be auto-finalized - the lazy assumption being
    *       that they will be switched to errantT after having been finalized.
    */
    const ERRANT_T = 'errantT';
    const RELATIVE_TIME_SESSIONS_CLOSE = "-1 day ago";


    /**
     * Is this session finished?  
     *
     * @param $session cakephp array for a session
     * @result BOOL whether this session is considered finished 
     */
    function finished($session) {
        return $session['finished'] == 1;
    }
  
    /**
      * Given an array of sessions, return those sessions that have been
      * finished.  
      * @param $sessions array of sessions
      * @param $projectId only look at sessions w/ this project_id
      * @return The sessions in the array that were 'finished'
      */
    function filterFinished($sessions, $projectId = null) {
        if ($sessions === null) {
            return array();
        }

        if (isset($projectId)){
            foreach ($sessions as $key => $session){
                if ($session['project_id'] != $projectId )
                    unset($sessions[$key]);
            } 
        }
            
        return array_values(array_filter($sessions, array('SurveySession', 'finished')));
    }

    /**
     *
     */
    function allScaleValues($sessions) {
        return array_map($sessions, array('SurveySessions', 'scaleValues'));
    }

    /**
     *
     */
    function scaleValues($session_id) {
        return ($this->findById($session_id));
    }

    /**
     * count sessions to see which number this is; 0-based. 
     */
    function getSessionNumber($session_id, $patient_id){

        $sessions = $this->find('all', array(
                                'conditions' => array(
                                    'SurveySession.patient_id' => $patient_id),
                                'recursive' => -1));
        //$this->log("getSessionNumber, sessions : " . print_r($sessions, true), LOG_DEBUG); 
        foreach($sessions as $key => $session){
            if ($session['SurveySession']['id'] == $session_id){
                return $key;
            }
        }
        return null;
    }

  /**
   * @param $t_n report on $t_n and the T preceeding it. Only sessions which 
     have 'partial_finalization' set will be reported on (assumes that 
     this remains set when 'finished' is set).
   * must be T1, T2, T3, T4, or null; if null, latest two T sessions which have 'partial_finalization' set will be reported on. 
   */
  function getSessionsTnAndTnLess1($patient_id, $project_id, $t_n){

    $conditions = array('SurveySession.patient_id' => $patient_id,
                        'SurveySession.project_id' => $project_id,
                        'SurveySession.partial_finalization' => '1');
    if ($t_n == null){
        $conditions['SurveySession.type <>'] =  'nonT';
        $conditions['SurveySession.type <>'] =  self::ERRANT_T;
    }
    else{
        // FIXME hopefully the simple rule holds - report on Tn and Tn-1
        $conditions['SurveySession.type'] = array($t_n); 
        $TLabels = array(1=>'T1', 2=>'T2', 3=>'T3', 4=>'T4');
        $n = substr($t_n, 1, 1);
        if (array_key_exists($n - 1, $TLabels)){
            $conditions['SurveySession.type'][] = $TLabels[$n - 1]; 
        }
    } 

    $sessions = 
        $this->find("all",
                    array(
                        'conditions' => $conditions, 
                        'order' => 'SurveySession.id ASC',
                        'recursive' => -1, // don't need "belongsTo" data
                        'limit' => 2
                        )
                    );
    return $sessions;
  }

  /**
   * Only sessions which have 'partial_finalization' set will be reported
   * @param $apptId report on $apptId and the appt preceeding it. 
   * if null, latest two T sessions which have 'partial_finalization' set will be reported on. 
   */
  function getReportableSessionsForApptAndApptPrevious(
                                        $patient_id, $project_id, 
                                        $apptId, $apptIdPrev){

    $conditions = array('SurveySession.patient_id' => $patient_id,
                        'SurveySession.project_id' => $project_id,
                        'SurveySession.partial_finalization' => '1');

    $conditionsApptId = array($apptId);
    if ($apptIdPrev){
        $conditionsApptId[] = $apptIdPrev;
    }
    $conditions ['SurveySession.appointment_id'] = $conditionsApptId;

    $sessions = $this->find("all",
                    array('conditions' => $conditions, 
                        'order' => 'SurveySession.id ASC',
                        'recursive' => -1
                        )
                    );
    //$this->log("getReportableSessionsForApptAndApptPrevious(" . print_r(func_get_args(), true) . "); conditions : " . print_r($conditions, true) . "; returning " . print_r($sessions, true), LOG_DEBUG);
    return array($sessions);
  }

  /**
   * Only sessions which have 'partial_finalization' set will be reported
   */
  function getReportableSessions($patient_id, $project_id){

    $conditions = array('SurveySession.patient_id' => $patient_id,
                        'SurveySession.project_id' => $project_id,
                        'SurveySession.partial_finalization' => '1');

    $sessions = $this->find("all",
                    array('conditions' => $conditions, 
                        'order' => 'SurveySession.id ASC',
                        'recursive' => -1
                        )
                    );
    //$this->log("getReportableSessionsForApptAndApptPrevious(" . print_r(func_get_args(), true) . "); conditions : " . print_r($conditions, true) . "; returning " . print_r($sessions, true), LOG_DEBUG);
    return array($sessions);
  }

    /**
     * change times to GMT before save
     */
    function beforeSave($options = Array()) {
        if (!empty($this->data['SurveySession']['patient_id'])) {
            $timezone = $this->User->getTimezone(
                $this->data['SurveySession']['patient_id']);

            if (!empty($this->data['SurveySession']['modified'])) { 
                    $this->data['SurveySession']['modified'] =
                        $this->localToGmt($this->data['SurveySession']['modified'],
                                  $timezone);
            }

            if (!empty($this->data['SurveySession']['started'])) { 
                    $this->data['SurveySession']['started'] =
                        $this->localToGmt($this->data['SurveySession']['started'], 
                                  $timezone);
            }

            if (!empty($this->data['SurveySession']['reportable_datetime'])) { 
                    $this->data['SurveySession']['reportable_datetime'] =
                        $this->localToGmt($this->data['SurveySession']['reportable_datetime'], 
                                  $timezone);
            }
        }

        return true;
    }

    /**
     * change times back to local time after save
     */
    function afterSave($created) {
        if (!empty($this->data['SurveySession']['patient_id'])) {
            $timezone = $this->User->getTimezone(
	            $this->data['SurveySession']['patient_id']);

	    if (!empty($this->data['SurveySession']['modified'])) { 
                $this->data['SurveySession']['modified'] =
                    $this->gmtToLocal($this->data['SurveySession']['modified'], 
		                      $timezone);
            }

	    if (!empty($this->data['SurveySession']['started'])) { 
                $this->data['SurveySession']['started'] =
                    $this->gmtToLocal($this->data['SurveySession']['started'], 
		                      $timezone);
            }
        }

        return true;
    }

    /**
     * change times to local time after retrieved
     */
    function afterFind($results, $primary = false) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()" . print_r(func_get_args(), true), LOG_DEBUG);
        //$this->logArrayContents($results, "resultsafterfix4");
        foreach ($results as $key => $val) {
            //if (sizeof($val['SurveySession']) > 0) {
            //    $this->logArrayContents($val, "valafterfix4; size of val[SS] is: " . sizeof($val['SurveySession']));
            if (isset($val['SurveySession']['patient_id'])){
                $timezone = $this->User->getTimezone(
                                        $val['SurveySession']['patient_id']);
                if (isset($val['SurveySession']['modified'])) {
                    $results[$key]['SurveySession']['modified'] =
                    $this->gmtToLocal($val['SurveySession']['modified'], $timezone);
                }
                if (isset($val['SurveySession']['started'])) {
                    $results[$key]['SurveySession']['started'] =
                    $this->gmtToLocal($val['SurveySession']['started'], $timezone);
                }
                if (isset($val['SurveySession']['reportable_datetime'])) {
                    $results[$key]['SurveySession']['reportable_datetime'] =
                    $this->gmtToLocal($val['SurveySession']['reportable_datetime'], $timezone);
                }
                if (isset($val['SurveySession']['reportable_datetime'])) {
                    $results[$key]['SurveySession']['reportable_datetime'] =
                    $this->gmtToLocal($val['SurveySession']['reportable_datetime'], $timezone);
                }
            }
            //}


        }

        return $results;
    }

    /**
     *
     */
    function partially_finalize($session) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $this->id = $session["SurveySession"]["id"];

        $this->saveField('partial_finalization', 1);

        $timezone = $this->User->getTimezone(
            $session['SurveySession']['patient_id']);
        $this->saveField('modified',
            $this->localToGmt($session['SurveySession']['modified'], 
                $timezone));

    } // function partially_finalize 

    /**
     *
     */
    function finish($session) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $this->id = $session["SurveySession"]["id"];

        $this->saveField('partial_finalization', 1);

        $this->saveField('finished', 1);

        $timezone = $this->User->getTimezone(
            $session['SurveySession']['patient_id']);
        $this->saveField('modified',
            $this->localToGmt($session['SurveySession']['modified'], 
                $timezone));

    } // function finish

    /**
    * Return array of sessions to expire, based on rules
    *
    */
    function expired_open_survey_sessions() {
        // $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        $expired_sessions = $this->_expired_appointment_sessions();

        if (defined('ELECTIVE_SESSIONS') and ELECTIVE_SESSIONS)
            $expired_sessions += $this->_expired_elective_sessions();

        if (defined('SESSION_PATTERN') and SESSION_PATTERN == INTERVAL_BASED_SESSIONS)
            $expired_sessions += $this->_expired_interval_sessions();

        return $expired_sessions;

    }// function expired_open_survey_sessions() {


    /**
    * Return array of appointment-associated sessions to expire, based on rules
    * @return array
    */
    function _expired_appointment_sessions() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        //return array_merge($this->expired_nonT_sessions(), $this->expired_T_sessions());
        # find list of sites where this time is after 11 PM
        $site_clause = $this->_expired_site_clause();
        $Site = new Site();
        /**
        //$sites = $Site->after_five();
        $sites = $Site->after_eleven();
        //$sites = $this->sites_after_five();
        $site_clause = $this->site_clause($sites);
        */
        $now = $this->currentGMT();
        // Make sure we only expire sessions on the day of the clinic app't (e.g. after T-time)
        $site_clause = 
          "($site_clause AND (Appointment.datetime < '$now'))";
        # find all survey sessions that are from one of these sites
        #    (because it's after 11 PM there and the appt was in the past)
        $query = 
           "SELECT
                SurveySession.id, 
                SurveySession.patient_id,
                SurveySession.modified,
                Site.id
            FROM survey_sessions AS SurveySession
            JOIN appointments AS Appointment on 
                SurveySession.appointment_id = Appointment.id
            JOIN patients AS Patient on SurveySession.patient_id = Patient.id
            JOIN users ON SurveySession.patient_id = users.id
            JOIN clinics ON users.clinic_id = clinics.id
            JOIN sites AS Site on clinics.site_id = Site.id
            WHERE SurveySession.finished = 0
            AND SurveySession.type ='" . APPT . "'
            AND $site_clause";

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's the query to run: " . $query, LOG_DEBUG);

        $expired_sessions = $Site->query($query);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning expired_sessions: " . print_r($expired_sessions, true), LOG_DEBUG);
        return $expired_sessions;
        
    }// function _expired_appointment_sessions() {

        
    /**
     * Return array of elective sessions to expire, based on rules
     * "Sessions are finalized (both stages) at 11:59 PM the day after the session was started."
     * Here, we invert the math: all sessions started before local time midnight yesterday should be expired
     * @return array
     */
    function _expired_elective_sessions() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        
        $site_clause = $this->_expired_site_clause();
        $Site = new Site();
        $query = 
           "SELECT 
                SurveySession.id, 
                SurveySession.patient_id, 
                SurveySession.modified, 
                Site.id
            FROM survey_sessions AS SurveySession
            JOIN users ON SurveySession.patient_id = users.id
            JOIN clinics ON users.clinic_id = clinics.id
            JOIN sites AS Site on clinics.site_id = Site.id
            WHERE SurveySession.type ='" . ELECTIVE . "'
            AND SurveySession.finished = 0
            AND $site_clause";
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's the query to run: " . $query, LOG_DEBUG);
        $expired_sessions = $this->query($query);
        
        if(!$expired_sessions || count($expired_sessions) == 0) 
            $expired_sessions = array();
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning expired_sessions: " . print_r($expired_sessions, true), LOG_DEBUG);
        return $expired_sessions;
    }// function _expired_elective_sessions() {

    /**
     * Return array of interval sessions to expire
     * uses Patient properties to determine if there are any current sessions, if not, expire the unfinished
     * @return array
     */
    function _expired_interval_sessions(){
        $this->Behaviors->attach('Containable');
        $sessions = $this->find('all', array(
            'conditions' => array(
                'SurveySession.finished !=' => 1,
                'SurveySession.type' => array(ODD_WEEK, EVEN_WEEK, EVEN_WEEK_8, EVEN_WEEK_12),
            ),
            'contain' => array('Patient'),
        ));
        $expiredSessions = array();
        // HACK, will not be necessary when we move interval-based patient model code to the patient model from PatientPaintrackerrural
        $patientInstance = ClassRegistry::init('PatientPaintrackerrural');
        foreach ($sessions as $session){
            $patientInstance->_initializeIntervalSessions($session);
            if (!$patientInstance->currentWindow)
                array_push($expiredSessions, $session);
        }
        return $expiredSessions;
    }


    /**
     * Construct SQL clause identifying for each site what started time (GMT) 
     *  qualifies the SurveySession as expired
     * @return SQL fragment
     */
    function _expired_site_clause() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);
        $sites = $this->_get_sites_w_expire_times();

        $site_clauses = array();
        foreach($sites as $site) {
            $site_id = $site["Site"]["id"];
            $site_expire_before = $site["Site"]["expire_before"];
            array_push($site_clauses, "(Site.id = $site_id AND SurveySession.started < '$site_expire_before')");
        }
        $site_clause = "(" . join(" OR ", $site_clauses) . ")";
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...), returning" . $site_clause, LOG_DEBUG);
        return $site_clause;
    }// function _expired_site_clause() {

    /**
     * For each site, find the latest time that a session from that site
     *  should expire (e.g. find timestamp for midnight one day ago, in the 
     *  sites' local time converted to GMT)
     * @param @expireAt strtotime input; defaults to yesterday at midnight
     * @return array of Sites, each w/ "expire_before" set.
     */
    function _get_sites_w_expire_times($expireAt = "midnight") {
    //function _get_sites_w_expire_times($expireAt = "-1 day midnight") {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($expireAt)", LOG_DEBUG);
        $Site = new Site();
        $sites = $Site->find('all');
        $oldtimezone = date_default_timezone_get();
        foreach($sites as &$site) {
            $timezone = $site["Site"]['timezone'];
            date_default_timezone_set($timezone);
            $time = strtotime($expireAt);
            //$time = strtotime("-1 day midnight");
            //$time = strtotime("-1 day 23:00:00");
            $time = date(MYSQL_DATETIME_FORMAT, $time);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "($expireAt); here's expireAt according to local timezone ($timezone): $time", LOG_DEBUG);
            date_default_timezone_set($oldtimezone);
            $gmt_time = $this->localToGmt($time, $timezone);
            $site["Site"]['expire_before'] = $gmt_time;
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() returning sites = " . print_r($sites, true), LOG_DEBUG);
        return $sites;
    }// function _get_sites_w_expire_times($expireAt = "-1 day midnight")

    /**
     * Construct an SQL clause
     * @return SQL fragment
     */
    function _site_clause($sites) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() w/ sites = " . print_r($sites, true), LOG_DEBUG);
        if($sites && count($sites) > 0) {
            foreach($sites as &$site) {
                $site_id = $site["Site"]["id"];
                $site = "Site.id = $site_id";
            }
            $returnVal = "(" . join(" OR ", $sites) . ")";
        } else {
            $returnVal = "FALSE";
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...), returning " . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// function _site_clause($sites) 

    /**
     * @deprecated uses the old Patient.Tn struct
     */
    function expired_T_sessions() {
        # find list of sites where this time is after 11 PM
        $Site = new Site();
        //$sites = $Site->after_five();
        $sites = $Site->after_eleven();
        //$sites = $this->sites_after_five();
        $site_clause = $this->_site_clause($sites);
        $now = $this->currentGMT();
        // Make sure we only expire sessions on the day of the clinic app't (e.g. after T-time)
        $site_clause = "($site_clause AND ((SurveySession.type = 'T1' AND Patient.t1 < '$now')
                                        OR (SurveySession.type = 'T2' AND Patient.t2 < '$now')
                                        OR (SurveySession.type = 'T3' AND Patient.t3 < '$now')
                                        OR (SurveySession.type = 'T4' AND Patient.t4 < '$now')))";
        $two_days_ago = gmdate(MYSQL_DATETIME_FORMAT, strtotime("-2 day"));
        # find all T survey sessions that are: 
        #  either from one of these sites
        #    (because it's after 11 PM there and the T was in the past)
        #  or started more than 48 hours ago (absolute time)
        #    (just in case we skipped a day and need to make sure the old ones are all closed)
        $query = 
           "SELECT SurveySession.id, SurveySession.type, Site.id
            FROM survey_sessions AS SurveySession
            JOIN patients AS Patient on SurveySession.patient_id = Patient.id
            JOIN users ON SurveySession.patient_id = users.id
            JOIN clinics ON users.clinic_id = clinics.id
            JOIN sites AS Site on clinics.site_id = Site.id
            WHERE SurveySession.type != 'nonT'
            AND SurveySession.finished = 0
            AND $site_clause";
        $expired_sessions = $Site->query($query);
        return $expired_sessions;
    }

    /* reportableSessions generates a list of ids of SurveySessions that should be 
     * reported in the default view of the data export tool. 
     *   Requirements:
     *     - Survey Session: T-time, finalized
     *     - User: consented participant who is not withdrawn
     *   Arguments:
     *     - adding an array of $filters to the argument will add these to the query
     *       for more specialized cases.
     */
    function reportableSessions($filters = array()) {
        $eligible_patient  = array("Patient.test_flag = '0'", "Patient.consent_status = 'consented'");
        $finished_t_survey = array(
            "SurveySession.partial_finalization = '1'", 
            "SurveySession.type != '" . self::NONT . "'",
            "SurveySession.type != '" . self::ERRANT_T . "'"); 
        $filters = array_merge($eligible_patient, $finished_t_survey, $filters);

        return $this->find('all', array('conditions' => $filters));
    }

    /**
     *
     */
    function reportableSessionAndPatientIds($filters = array()) {
        $sessions = $this->reportableSessions($filters);
        $ids = array();
        foreach($sessions as $session) {
            array_push($ids, array("survey_session_id" => $session["SurveySession"]["id"],
                                   "patient_id" => $session["SurveySession"]["patient_id"]));
        }
        return $ids;
    }

    /**
    *  The local timestamp of the last user input of data to the survey session.
    */
    function lastAnswerDT($id){
        $session = $this->findById($id); 
        $timezone = $this->User->getTimezone(
            $session['SurveySession']['patient_id']);
        
        $answer = ClassRegistry::init('Answer')->find(
                    'first', 
                    array('conditions' => array("Answer.survey_session_id" 
                                                => $id),
                            'order' => array('Answer.modified' => 'DESC')
                    ));

        $dt = $session['SurveySession']['started'];
        if (isset($answer['Answer']['modified'])){
            $dt = $answer['Answer']['modified'];
        }
        return $this->gmtToLocal($dt, $timezone);
    }

    /**
     *
     */
    function findRecent($patient_id, $project_id = null) {
        $ago = gmdate(MYSQL_DATETIME_FORMAT, time() - 10);
        
        $conditions = array("SurveySession.patient_id = $patient_id",
                        "SurveySession.started > '$ago'");
        if ($project_id) {
            $conditions [] = "SurveySession.project_id = $project_id";
        }
        $recent = $this->find('first', 
                array('conditions' => $conditions));
        if($recent) {
            return $recent["SurveySession"]["id"];
        } else {
            return false;
        }
    }

}
