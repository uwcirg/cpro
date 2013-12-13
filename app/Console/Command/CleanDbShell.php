<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
    * Clears patient data from an instance (intended for demo systems)
    *
    * Run like: app/Console/cake clean_db 
*/

class CleanDbShell extends Shell {
    var $uses = array('Patient', 'User', 'Appointment');

    function main() {
        $logName = Inflector::underscore(str_replace('Shell', '', __CLASS__)) . '.log';

        $this->out('Output written to ' . LOGS . $logName . PHP_EOL);
        $log = fopen(LOGS . $logName, 'a');


        if (!$log)
            die("Failed to open file $logName");

        fwrite($log, date(DATE_RFC822) . ' - Clearing patient data' . PHP_EOL);

        if (!Configure::check('DEMO_RESET_BUT_DONT_DELETE_PATIENTS')
            || !Configure::check('DEMO_DONT_TOUCH_PATIENTS')){
            fwrite($log, date(DATE_RFC822) . ' - either DEMO_RESET_BUT_DONT_DELETE_PATIENTS or DEMO_DONT_TOUCH_PATIENTS not set, so bailing!' . PHP_EOL);
            die("either DEMO_RESET_BUT_DONT_DELETE_PATIENTS or DEMO_DONT_TOUCH_PATIENTS not set, so bailing!"); 
        }

        $patients = $this->Patient->find(
            'all',
            array('recursive' => -1)
        );

        foreach($patients as $patient){
            $id = $patient['Patient']['id'];
            if (in_array($id, Configure::read('DEMO_DONT_TOUCH_PATIENTS'))){
                fwrite($log, date(DATE_RFC822) . ' - not touching patient ' . $id . PHP_EOL);
            }
            elseif (in_array($id, Configure::read('DEMO_RESET_BUT_DONT_DELETE_PATIENTS'))){

                foreach(Configure::read('DEMO_RESET_SQL') as $sql){
                    // sql eg 'DELETE FROM SurveySession WHERE project_id = 3 AND patient_id = '
                    $this->Patient->query($sql . " $id"); // which model doesn't matter here
                }
                fwrite($log, date(DATE_RFC822) . ' - reset patient ' . $id . PHP_EOL);
            }
            else {
                // just trying to keep the patient admin section tidy here,
                //      so no need to clear out all the tables
                $this->Patient->query(
                    'INSERT INTO patients_deleted SELECT * FROM patients ' 
                        . 'WHERE patients.id = ' . $id); 
                $this->Patient->delete($id, true); // delete w/ cascade DOESN'T WORK
                $this->User->query(
                    'INSERT INTO users_deleted SELECT * FROM users ' 
                        . 'WHERE users.id = ' . $id); 
                $this->User->delete($id);
                $this->Appointment->deleteAll(array('Appointment.patient_id' => $id));
                fwrite($log, date(DATE_RFC822) . ' - deleted patient ' . $id . PHP_EOL);
            }
        }

        fwrite($log, date(DATE_RFC822) . ' - done clearing patient data' . PHP_EOL . PHP_EOL);
    }
}
?>
