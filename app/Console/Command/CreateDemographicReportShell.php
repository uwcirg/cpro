<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
* Run this in a cronjob nightly, like:
/srv/www/esrac.cirg.washington.edu/htdocs/cake/console/cake -app /home/mcjustin/dhair2trunk/app -working /home/mcjustin/dhair2trunk/app -root /home/mcjustin/dhair2trunk -core /home/mcjustin/dhair2trunk/ create_demographic_report
can also pass a param in cronjob like:
... create_demographic_report true
*/
App::uses('Controller', 'Controller');
App::uses('DataAccessController', 'Controller');
App::uses('DataExportComponent', 'Controller/Component');

class CreateDemographicReportShell extends Shell {
    var $DataAccess;
    var $DataExport;

    // called before main()
    function startup() {

        Configure::write('debug', 2);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

    }

    /**
     *
     */
    function main() {

        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $options = array();
        $options['type_array'] = 'T1';
        $options['demographics'] = true;
        $options['question_filter'] = 'ethnicity';

        $consent_irrelevant = false;
        if (count ($this->args) != 0){
            $consent_irrelevant = $this->args[0];
        }
        if ($consent_irrelevant){
            $this->log("consent_irrelevant", LOG_DEBUG);
            $options['patient_filter'] = 'non_test';
            $options['label'] = 'demog.consentIrrelevant';
        }
        else {
            $options['patient_filter'] = 'participant';
            $options['label'] = 'demog.participant';
        }
        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's options after interpreting args: " . print_r($options, true), LOG_DEBUG);

        $this->DataAccess =& new DataAccessController();
        $this->DataAccess->authd_user_id = 0;
        $this->DataAccess->constructClasses();

        $this->DataAccess->initOptions($options);

        $this->DataExport = $this->DataAccess->Components->load('DataExport');
        //$this->DataExport =& new DataExportComponent();
        $this->DataExport->startup($this->DataAccess);

        $this->DataExport->data_file();

        $this->log(__CLASS__ . "." . __FUNCTION__ . "() done", LOG_DEBUG);
    
    }
}

?>
