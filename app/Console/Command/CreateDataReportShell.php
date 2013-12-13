<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
* Run this in a cronjob nightly, like:
/srv/www/esrac.cirg.washington.edu/htdocs/cake/console/cake -app /srv/www/esrac.cirg.washington.edu/htdocs/app -working /srv/www/esrac.cirg.washington.edu/htdocs/app -root /srv/www/esrac.cirg.washington.edu/htdocs -core /srv/www/esrac.cirg.washington.edu/htdocs/ create_data_report T1.T2.T3.T4,Ts,,non_test
Another args variant example: T1.T2.T3.T4.nonT,TsAndNonTs.tx,true,tx
*/
App::uses('Controller', 'Controller');
App::uses('DataAccessController', 'Controller');
App::uses('DataExportComponent', 'Controller/Component');

class CreateDataReportShell extends Shell {
    var $DataAccess;
    var $DataExport;

    function startup() {

        Configure::write('debug', 2);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

    }

    /**
     *
     */
    function main() {
      
        $this->log(__CLASS__ . "." . __FUNCTION__ . " with args " . print_r($this->args, true), LOG_DEBUG);

        if (count ($this->args) != 1){
            $this->log('Error: incorrect parameters. This script must be called with one parameter in the following format: type_array,label,row_per_session,patient_filter, eg "T1.T2.T3.T4.nonT,TsAndNonTs.tx,true,tx"', LOG_ERROR);
            return;
        }

        $options = array();
        list($options['type_array'], 
                $options['label'], 
                $options['row_per_session'], 
                $options['patient_filter']) 
            = explode(',', $this->args[0]);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's options after interpreting args: " . print_r($options, true), LOG_DEBUG);

        $this->DataAccess =& new DataAccessController();
        $this->DataAccess->authd_user_id = 0;
        $this->DataAccess->constructClasses();

        $this->DataAccess->initOptions($options);

        $this->DataExport = $this->Controller->Components->load('DataExport')
        //$this->DataExport =& new DataExportComponent();
        $this->DataExport->startup($this->DataAccess);

        $this->DataExport->data_file();

        $this->log(__CLASS__ . "." . __FUNCTION__ . "() done", LOG_DEBUG);

    }
}

?>
