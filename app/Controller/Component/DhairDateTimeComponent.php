<?php
/** 
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class DhairDateTimeComponent extends Component
{
    var $controller = null;

    /** The default format we use for datetimes (Mysql's preferred format) */
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    function initialize(Controller $controller){
        $this->controller = $controller;
        
        $userModelInstance = ClassRegistry::init('User');
        $this->controller->User = $userModelInstance;
    }

    /** Return the default format */
    function getDefaultFormat() {
        return MYSQL_DATETIME_FORMAT;
    }

    /** returns current time for user */
    function usersCurrentTime($userId=null) {
        if (!$userId)
            $userId = $this->controller->authd_user_id;

        $timezone = $this->controller->User->getTimeZone($userId);
        $date = new DateTime("now", new DateTimeZone($timezone));
        return $date;
    }

    /** returns current gmt time (needed if we're writing a timestamp
        directly to the database */
    function currentGmt() {
        return gmdate(MYSQL_DATETIME_FORMAT);
    }

    /** get the current time in a particular timezone, as a string 
     * @param timezone timezone, as a string
     * @return the current time
     */
    function currentTimeStr($timezone) {
        $date = new DateTime("now", new DateTimeZone($timezone));
        return $date->format(MYSQL_DATETIME_FORMAT);
    }

    /** get user's current time, as a string */
    function usersCurrentTimeStr() {
        return $this->currentTimeStr($this->controller->User->getTimeZone(
            $this->controller->authd_user_id));
    }

    /** Get the abbreviation for the user's timezone at a particular datetime 
     * @param datetime The datetime in question
     * @param timezone Timezone for the user, as a string
     */
    function tzAbbr($datetime, $timezone) {
        $oldTimeZone = date_default_timezone_get();
	date_default_timezone_set($timezone);

	$timestamp = strtotime($datetime);
	$result = date('T', $timestamp);

        // restore the current default time zone
	date_default_timezone_set($oldTimeZone);
	return $result;
    }

    /**
    * 
    * $strDateTime date time to convert; tested w/ mysql format
    * $user_id user id (same as patient id) 
    * $unixTimeStamp whether return val should be a unix time stamp 
    */
    function convertDateTimeToUsersTimeZone(
                $strDateTime, 
                $user_id,
                $unixTimeStamp = false){

        $origPhpTimeZone = date_default_timezone_get();
        date_default_timezone_set('GMT');
        $dateTime = new DateTime($strDateTime);
        $strTimeZone = 
            $this->controller->User->getTimeZone($user_id);
        //$this->log("New time zone: " . $strTimeZone . "\n" , LOG_DEBUG);
        $dateTimeZone = new DateTimeZone($strTimeZone);
        $dateTime->setTimezone($dateTimeZone);
        //$unixTimeStamp = $dateTime->format('U');
        // DB stores times as GMT, format like "2008-05-06 03:35:01"
        $dbFormatTimeStamp = $dateTime->format(MYSQL_DATETIME_FORMAT);
        /**$this->log("DT from DB (GMT): " . $strDateTime .
                    ". Applying time zone: " . $dateTimeZone->getName() .
                    ". Time w/ time zone applied, in DB format: " . 
                                            $dbFormatTimeStamp .
                    ". Time w/ time zone applied, in U format: " . 
                                            strtotime($dbFormatTimeStamp) .
                    "\n" , LOG_DEBUG);*/
        if ($unixTimeStamp === true){
            $dbFormatTimeStamp = strtotime($dbFormatTimeStamp);
        }

        // set back to ini, just to be safe
        date_default_timezone_set($origPhpTimeZone);
        
        return $dbFormatTimeStamp;
    }

    /**
     * Get a datetime one week earlier than a particular datetime
     * @param datetime Datetime in question
     */
    /* ignoring timezones here; most likely when we say '1 week before',
       we aren't interested in adjusting for daylight savings changes and
       the like */
    function oneWeekEarlier($datetime) {
        return date(MYSQL_DATETIME_FORMAT, strtotime("$datetime -1 week"));
    }

    /**
     * Get a date one week earlier than a particular date
     * @param date Date in question
     */
    function oneWeekEarlierDate($date) {
        return date('Y-m-d', strtotime("$date -1 week"));
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
        if (empty($datetime)) {
            return null;
        } else {
            if ($isTimestamp) {
                $ts = $datetime;
            } else {   // convert from local time
                // save old timezone
                $oldtimezone = date_default_timezone_get();

                date_default_timezone_set($timezone);
                $ts = strtotime($datetime);

                // reset to old timezone
                date_default_timezone_set($oldtimezone);
            }

            return gmdate(MYSQL_DATETIME_FORMAT, $ts);
        }
    }
}
