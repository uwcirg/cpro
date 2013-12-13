<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
* Run this in a cronjob nightly, like:
/srv/www/esrac.cirg.washington.edu/htdocs/cake/console/cake -app /home/mcjustin/dhair2trunk/app -working /home/mcjustin/dhair2trunk/app -root /home/mcjustin/dhair2trunk -core /home/mcjustin/dhair2trunk/ create_intervention_report
can also pass a param in cronjob like:
... create_intervention_report true
*/
App::uses('Controller', 'Controller');
App::uses('DataAccessController', 'Controller');
App::uses('DataExportComponent', 'Controller/Component');

class CreateInterventionReportShell extends Shell {
    var $DataAccess;
    var $DataExport;

    function startup() {

        Configure::write('debug', 2);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

    }

    function main() {
      
        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $options = array();
        $options['label'] = 'intervention';
        $options['patient_filter'] = 'participant';
        $options['demographics'] = true;

        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's options after interpreting args: " . print_r($options, true), LOG_DEBUG);
 
        $this->DataAccess =& new DataAccessController();
        $this->DataAccess->authd_user_id = 0;
        $this->DataAccess->constructClasses();

        $this->DataAccess->initOptions($options);

        $this->DataExport = $this->DataAccess->Components->load('DataExport');
        //$this->DataExport =& new DataExportComponent();
        $this->DataExport->startup($this->DataAccess);

        $this->DataExport->intervention_dose_file();
        //$this->DataExport->intervention_dose_file($patient_filter);
                        //$patient_filter);

        $this->log(__CLASS__ . "." . __FUNCTION__ . "() done", LOG_DEBUG);
    }
}

?>
