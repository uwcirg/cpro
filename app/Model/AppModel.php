<?php
/**
    * App Model, father of all models
    * 
    * This model defines application-wide features used in multiple models.
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/

App::uses('Model', 'Model');
App::uses('Controller/Component', 'CakeSession');

class AppModel extends Model
{
    // Values for the status field for coded items (audio files or charts)
    /** Value for status field that indicates file has only been uploaded */
    const RAW = 'Raw';

    /** Value for status field that indicates file has been downloaded for 
        scrubbing */
    const DOWNLOADED_SCRUB = 'Downloaded for Scrubbing';

    /** Value for status field that indicates file has been scrubbed */
    const SCRUBBED = 'Scrubbed';

    /** Value for status field that indicates file has been assigned for 
        coding */
    const ASSIGNED_CODING = 'Assigned for Coding';

    /** Value for status field that indicates coder 1 is done, 
        but coder 2 is not */
    const CODER1_DONE = 'Coder 1 Done';

    /** Value for status field that indicates coder 2 is done, 
        but coder 1 is not */
    const CODER2_DONE = 'Coder 2 Done';

    /** Value for status field that indicates two coders are not in agreement
        and file must be recoded */
    const TO_BE_RECODED = 'To Be Recoded';

    /** Indicates that no recordings have been uploaded for the patient */
    const NO_RECORDING_MADE = 'No Recording Made';

    /** Value for status field that indicates all coding complete */
    const CODING_DONE = 'All Coding Done';
    
    const DISTANT_FUTURE_DT = '2100-01-01 00:00:00';
    
    var $Session;   

    /**
     */
    function __construct ($id = false, $table = null, $ds = null ){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), this is " . get_class($this), LOG_DEBUG);
        parent::__construct($id, $table, $ds);

        // apply instanceModelOverrides to inter-model relationships
        $instanceModelOverrides = Configure::read('instanceModelOverrides');
        foreach($instanceModelOverrides as $modelName){
            // $modelName eg 'survey_session'
            $baseModelName = Inflector::camelize($modelName);
            // eg SurveySession
            foreach ($this->_associations as $association){
            // eg hasMany
                if (array_key_exists($baseModelName, $this->{$association})){
                    $currentAssn = $this->{$association}[$baseModelName];
//                  $this->log("baseModelName: " . $baseModelName, LOG_DEBUG);
                    $subclassModelName =
                        $baseModelName .
                        Inflector::humanize(INSTANCE_ID);
                    //eg "SurveySessionP3p"
//                    $this->log(__CLASS__ . "->" . __FUNCTION__ . ", changing " .get_class($this) . "'s $association for $baseModelName to class $subclassModelName: ", LOG_DEBUG);
                    $this->{$association}[$baseModelName]['className'] 
                        = $subclassModelName;
                }
            }
        }
    }




    /**
     * When called, this function will mutate the $results paramter
     * so that it contains the correct translations. Here is how it works
     * 1) read the locale (i18nPrefix) from the Session Config.language parameter
     * 2) if the locale is valid, iterate through every cell of the database and replace
            replaces the contents of the $default_eng cell with the contents of the es_MX cell.        

    Called by the aferFind methods of various models. Takes a result set and replace the english columns with the 
    columns used by the current language. 
    **/     
    function loadTranslations($results,$name){
    $this->Session  = new CakeSession();  
    $i18nPostfix = $this->Session->read("Config.language");
//    $this->log("loadTranslations Called by " . $name,LOG_DEBUG);  
      if ($i18nPostfix === "") return $results;
        foreach($results as $key => $val){
            if(!empty ($val[$name])){
                foreach ($val[$name] as $innerKey => $innerVal){
                    if(substr($innerKey,-1*strlen($i18nPostfix))==$i18nPostfix){
                        $default_eng = substr($innerKey,0,-1*strlen($i18nPostfix) - 1);
                            if(!isset($results[$key][$name][$innerKey])&& isset($results[$key][$name][$default_eng]))
                                $results[$key][$name][$default_eng] = "<b>(Translation not found)</b> " . $results[$key][$name][$default_eng];
                            else 
                                $results[$key][$name][$default_eng] = $results[$key][$name][$innerKey];
                    }
                }
            }
        }
    return $results;
    }

    function logArrayContents($array, $description = "array to log"){
        ob_start();
        var_dump($array);
        $debugStr = ob_get_contents();
        ob_end_clean();
        $this->log($description . " = " . $debugStr . "; "  
                    . Debugger::trace(), LOG_DEBUG);
    }

    /** @function get: returns the object you want
     * from this model, from a variety of arguments.
     * Calling this on a first parameter lets you save
     * queries if you already have the object, while
     * also letting you call the function if you only
     * know the id.
     * @arg $input: (mixed) can be:
     *  - int -> $this->findbyId(input)
     *  - model array -> return the input
     */
    function getRecord($arg) {
        if ((int)($arg) == $arg) {
            return $this->findById((int)$arg);
        } elseif(is_array($arg) and $arg[$this->name]) {
            return $arg;
        } else {
            throw new Exception($this->name . 
                                " could not get ".
                                $arg);
        }
    }

    /**
    * Adam's DB cache clearing utility fxn
    * can be called from controller like:
    *       $this->(any model name)->_clearDBCache();
    */
    function _clearDBCache() {
        $db =& ConnectionManager::getDataSource($this->useDbConfig);
        $db->_queryCache = array();
    }

    // functions to convert between local time and GMT

    /**
    *
    */
    function getTimeZone($userId){

//        $this->log(__CLASS__ . '.' . __FUNCTION__ . "($userId), trace:" . Debugger::trace() . "\n\n", LOG_DEBUG);

        $userId = intval($userId);

        if (empty($userId)) {
            // $this->log("getTimeZone: userId empty: $userId", LOG_DEBUG);
            return date_default_timezone_get();
        }

        $queryStr = "SELECT timezone from sites 
            JOIN clinics, users
            WHERE users.id = $userId
            AND clinics.site_id = sites.id
            AND users.clinic_id = clinics.id
            LIMIT 1";
        $result = $this->query($queryStr);

        if (empty($result)) {
            $this->log("getTimeZone: userId $userId has no timezone!");
            return date_default_timezone_get();
        } else {
            return $result[0]['sites']['timezone'];
        }
    }


    /**
     * Convert a datetime in local time into a datetime in GMT
     * @param $datetime as a string or timestamp
     * @param $timezone local timezone, as a String
     * @param $isTimestamp If true, the datetime is a timestamp, if false,
     *        a string
     * @return The datetime, converted to GMT, as a Mysql DateTime string
     */
    function localToGmt($datetime, $timezone, $isTimestamp=false) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(args) where args= " . print_r(func_get_args(), true), LOG_DEBUG);

        $returnVal = null;

        if (!empty($datetime)) {
            if ($isTimestamp) {
                $ts = $datetime;
            } 
            else {   // convert from local time
                // save old timezone
                $oldtimezone = date_default_timezone_get();

                date_default_timezone_set($timezone);
                $ts = strtotime($datetime);

                // reset to old timezone
                date_default_timezone_set($oldtimezone);
            }

            $returnVal = gmdate(MYSQL_DATETIME_FORMAT, $ts);
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() returning " . $returnVal, LOG_DEBUG);
        return $returnVal;
    }

    /**
     * Convert a datetime in GMT into local time 
     * @param $gmtdatetime as a string, in GMT, encoded as Mysql DateTime
     * @param $timezone local timezone, as a String
     * @param $returnTimestamp If true, return a Unix timestamp, if false,
     *        return a string in local time
     * @return The datetime as a timestamp or a string in local time
     */
    function gmtToLocal($gmtdatetime, $timezone, 
                        $returnTimestamp=false, 
                        $dateFormat = MYSQL_DATETIME_FORMAT) 
    {
        if (empty($gmtdatetime)) {
            return null;
        } else {
            // save old timezone
            $oldtimezone = date_default_timezone_get();

            date_default_timezone_set('GMT');
            $timestamp = strtotime($gmtdatetime);

            if ($returnTimestamp) {
                $result = $timestamp;
            } else {   // convert to local time
                date_default_timezone_set($timezone);
                $result = date($dateFormat, $timestamp);
            }

            // reset to old timezone
            date_default_timezone_set($oldtimezone);

            return $result;
        }
    }

    /**
      * Get the difference between a particular local time and now, in seconds
      * @param time Local time to test, as string
      * @param timezone Timezone of local time, as a string
      * @return time - now, in seconds
      */
    function secondsAfterNow($time, $timezone) {
        if (empty($time)) {
	    return null;
        }

        // save old timezone
	$oldtimezone = date_default_timezone_get();
	date_default_timezone_set($timezone);
	$result = strtotime($time) - time();

	// reset to old timezone
	date_default_timezone_set($oldtimezone);

	return $result;
    }

    /**
     * Check whether it is currently before some time (default 5pm) on a 
     *  particular date (in local time).
     * @param date date (according to timezone) to test, as a string; note that if you pass a datetime the time portion is ignored! e.g. "2012-09-14 15:05:40" and "2012-09-14" are treated the same
     * @param timezone Timezone of the date, as a string
     * @param maxTime Upper clock time limit according to timezone; defaults to "17:00:00" 
     * @return true if we are currently before or at 5pm, false if we are not
     */
    function currentlyBeforeSomeTime($date, $timezone, $maxTime = "17:00:00") {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(args) where args= " . print_r(func_get_args(), true), LOG_DEBUG);

        $returnVal;

        if (empty($date)) {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); date empty, returning null", LOG_DEBUG);
            return null;
        }

        $components = date_parse($date);
        $referenceDateTime = $components['year'] . "-" . 
            $components['month'] . "-" .
            $components['day'] . " " . $maxTime;

        if ($this->secondsAfterNow($referenceDateTime, $timezone) >= 0) {
            $returnVal = true;
        } else {
            $returnVal = false;
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...) returning " . $returnVal, LOG_DEBUG);
        return $returnVal;
    }// function currentlyBeforeSomeTime


    /** returns current gmt time (needed if we're writing a timestamp
        directly to the database) */
    function currentGmt() {
        return gmdate(MYSQL_DATETIME_FORMAT);
    }

    function afterFind($results, $primary=false) {

        // Override Patient array attributes with ones present in PatientExtension to allow model-agnostic saving
        if (array_key_exists(0, $results) and is_array($results[0])){

            foreach ($results as $key => &$val) {

                if (
                    isset($val['PatientExtension']['patient_id']) and
                    array_key_exists('patient_id', $val['PatientExtension']) and

                    isset($val['Patient']) and
                    array_key_exists('Patient', $val)
                )
                    $val['Patient'] = array_replace_recursive(
                        $val['Patient'], $val['PatientExtension']
                    );
            }
        }
        return $results;
    }

    /**
     * Add a time period to a local datetime
     * @param datetime base local datetime, as a string
     * @param period period of time to add, in seconds
     * @param timezone Timezone of local time
     * @return A new local datetime string that is $period seconds after 
     *     $datetime
     */
    function addPeriodToTime($datetime, $period, $timezone) {
        // save the current default time zone
        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set($timezone);

        $timestamp = strtotime($datetime);
        $timestamp += $period;
        $result = date(MYSQL_DATETIME_FORMAT, $timestamp);

        // restore the current default time zone
        date_default_timezone_set($oldTimeZone);
        return $result;
    }


    /**
     * Compare the difference between two datetimes in the same timezone to a 
     *    particular period of time
     * @param datetime1 First datetime, as a string
     * @param datetime2 Second datetime, as a string
     * @param period Period of time to compare to, in seconds
     * @param timezone Timezone of the localtimes
     * @return Let d = datetime2 - datetime1.  Return '=' if d = $period, 
     *         '<' if d < $period,
     *         '>' if d > $period.  
     *         Return 'NaN' if either datetime is not valid
     */
    function compareDifferenceToPeriod($datetime1, $datetime2,
                                       $period, $timezone)
    {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; args: " . print_r(func_get_args(), true), LOG_DEBUG);

        // save the current default time zone
        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set($timezone);

        $timestamp1 = strtotime($datetime1);
        $timestamp2 = strtotime($datetime2);

        if (empty($timestamp1) || empty($timestamp2)) {
            $result = 'NaN';
        } else {
            $d = $timestamp2 - $timestamp1;

            if ($d == $period) {
                $result = '=';
            } else if ($d > $period) {
                $result = '>';
            } else {
                $result = '<';
                }
            }

        // restore the current default time zone
        date_default_timezone_set($oldTimeZone);

//    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning $result", LOG_DEBUG);
        return $result;
    }


    /**
     * Returns enum options for a column as an array
     * http://stackoverflow.com/a/8474116/796654
     * @param string $columnName the name of the column for the current model
     * @return array an array containing the possible enums for this field/column
     */
    function getEnumValues($columnName=null){
        $type = $this->getColumnType($columnName);

        if ($type and strpos($type,'enum') !== false)
            return explode('\',\'', substr($type, 6,-2));
    } //end getEnumValues
}
?>
