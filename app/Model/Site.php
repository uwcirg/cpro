<?php
/** 
    * Site class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Site extends AppModel
{
    var $name = "Site";
    var $useTable = 'sites';
    var $hasMany = array('Clinic');

    /**
     * @return array of sites where it is currently after 23:00.
     */
    function after_eleven() {
        $sites = $this->find('all');
        $sitesAfterEleven = array();
        foreach($sites as $site) {
            $timezone = $site["Site"]["timezone"];
            $time = $this->gmtToLocal(gmdate(MYSQL_DATETIME_FORMAT), $timezone);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), timezone $timezone, local time is $time", LOG_DEBUG);
            if(!$this->currentlyBeforeSomeTime($time, $timezone, "23:00:00")) {
                array_push($sitesAfterEleven, $site);
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning sitesAfterEleven: " . print_r($sitesAfterEleven, true), LOG_DEBUG);

        return $sitesAfterEleven;
    }
}
