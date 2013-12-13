<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class Appointment extends AppModel {
    var $belongsTo = array('Patient');
    var $hasOne = array('SurveySession');
    var $order = "Appointment.datetime ASC";

    //$nextAppointmentId;


    /**
     *
     */
    function beforeFind($queryData){

        if (in_array("medications",
                   Configure::read('modelsInstallSpecific'))){
            $this->bindModel(
                array('hasOne' =>
                        array("Medday")));
        }
    }


    /*
     * Move records with null datetime to the end of the array, as they are new records for future appointments.
     */
    function findWNullReorder($type, $params){

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()" . print_r(func_get_args(), true), LOG_DEBUG);

        $results = $this->find($type, $params);

        //$this->log("Appointment findWNullReorder(); here are results from find : " . print_r($results, true), LOG_DEBUG);
        //$this->log("Appointment findWNullReorder(); here's sizeof(results) : " . sizeof($results), LOG_DEBUG);

        if (sizeof($results) < 2) return $results;

        while($results[0]['Appointment']['datetime'] == null){
          //$this->log("Appointment afterFind(), a datetime is null", LOG_DEBUG);
            $nullDtRecord = array_shift($results);
            $results[] = $nullDtRecord;
        }
        return $results;

    }

    /*
     * Move records with null datetime to the end of the array, as they are new records for future appointments.
     */
    function nullReorder($dataAppointments){

        if (sizeof($dataAppointments) < 2) return $dataAppointments;

        while($dataAppointments[0]['datetime'] == null){
          //$this->log("Appointment afterFind(), a datetime is null", LOG_DEBUG);
            $nullDtRecord = array_shift($dataAppointments);
            $dataAppointments[] = $nullDtRecord;
        }
        return $dataAppointments;

    }


    // change T-times to GMT before save
    function beforeSave($options = Array()){
        //$this->log("Appointment beforeSave(); here's this->data before mod: " . print_r($this->data, true), LOG_DEBUG);

        if (!empty($this->data['Appointment']['patient_id'])){
 
          $timezone = 
            $this->getTimeZone($this->data['Appointment']['patient_id']);
        
          if (!empty($this->data['Appointment']['datetime'])){
            $this->data['Appointment']['datetime'] = 
                $this->localToGmt($this->data['Appointment']['datetime'], 
                                    $timezone);
          }
        }

        return true;
    }


    // change times back to local time after save
    function afterSave($created) {

        if (!empty($this->data['Appointment']['datetime'])) {
            $timezone = $this->getTimeZone(
                                    $this->data['Appointment']['patient_id']);
            $this->data['Appointment']['datetime'] =
                $this->gmtToLocal(
                        $this->data['Appointment']['datetime'], $timezone);
        }

        return true;
    }


    // change appointment times to local time after retrieved
    function afterFind($results, $primary = false){
        //$this->log("Appointment afterFind(); here's data : " . print_r($this->data, true), LOG_DEBUG);
        //$this->log("Appointment afterFind(); here's results before mod: " . print_r($results, true), LOG_DEBUG);
        //$this->log("Appointment afterFind(); here's sizeof(results) : " . sizeof($results), LOG_DEBUG);

        foreach ($results as $key => $val) {
            if (!empty($val['Appointment']) && 
                    !empty($val['Appointment']['datetime'])) {
                //$timezone = $this->User->getTimeZone($val['Patient']['id']);
                $timezone = $this->getTimeZone($val['Appointment']['patient_id']);

                if (isset($val['Appointment']['datetime'])) {
                    $results[$key]['Appointment']['datetime'] =
                        $this->gmtToLocal(
                                $val['Appointment']['datetime'], $timezone);
                }
            }
        }

        return $results;
    }


    // count appointments to see which number this is; 0-based. 
    function getAppointmentNumber($appointment_id, $patient_id){

        $appointments = $this->find('all', array(
                                'conditions' => array(
                                    'Appointment.patient_id' => $patient_id),
                                'recursive' => -1));
        //$this->log("getSessionNumber, appointments : " . print_r($appointments, true), LOG_DEBUG); 
        foreach($appointments as $key => $appointment){
            if ($appointment['Appointment']['id'] == $appointment_id){
                return $key;
            }
        }
        return null;
    }

    /*
     * count appointments to see which number this is; 0-based. 
     * @param $appointments - cakephp data array
     */
    function getAppointmentNumberForResults($appts){
        
        $patientsApptCt = array();

        foreach($appts as $key => &$appt){
            $patient_id = $appt['Appointment']['patient_id'];
            $appt['Appointment']['number'] = 
                $this->getAppointmentNumber($appt['Appointment']['id'],
                                            $appt['Appointment']['patient_id']);
        }
        return $appts;
    }


    function getNextAppointment($patient_id){
        $appointment = $this->find('first', array(
                                'conditions' => array(
                                    'Appointment.patient_id' => $patient_id,
                                    'Appointment.datetime < ' => $this->currentGmt()),
                                'order' => array('Appointment.datetime DESC'),
                                'recursive' => -1));
        //$this->log("getNextAppointment($patient_id), returning " . print_r($appointment, true), LOG_DEBUG);
        return $appointment;
        /**
        foreach ($this->patient['Appointment'] as $appointment){
            if ($appointment['datetime'] < currentGmt()){
                $nextAppointment = $appointment;
            }
            else break;
        }*/
    }


    /**
     * Are a patient's appointment datetimes reasonable?
     *   successive appointments should be at least MIN_SECONDS_BETWEEN_APPTS apart
     * @param $date Data with appointment datetimes
     * @return Whether the appointment datetimes are reasonable
     */
    function suitableAppointments($data) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(data) w/ data:" . print_r($data, true), LOG_DEBUG);
        $timezone = $this->getTimeZone($data['User']['id']);

        /* It's easiest just to compare successive appointment datetimes, but appointment datetimes can 
        be empty (except t1).  So create dummy appointment datetimes MIN_SECONDS_BETWEEN_APPTS later
        for the empty ones */
        $dummyDateTimes = array();

        foreach ($data['Appointment'] as $i => $appointment) {
            if ($i == 0) continue;

            if (empty($appointment['datetime'])) {
                $dummyDateTimes[$i] =
                    $this->addPeriodToTime(
                        $data['Appointment'][$i-1]['datetime'],
                        MIN_SECONDS_BETWEEN_APPTS,
                        $timezone);
            } else {
                $dummyDateTimes[$i] = $appointment['datetime'];
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); dummyDateTimes:" . print_r($dummyDateTimes, true), LOG_DEBUG);

        foreach ($data['Appointment'] as $i => $appointment) {
            if ($i == 0) continue;

            if (strtotime($data['Appointment'][$i]['datetime'])
                    <= strtotime($data['Appointment'][$i - 1]['datetime'])){
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning false because dates are out of temporal sequence.", LOG_DEBUG);
                return false;
            }

            $result =
              $this->compareDifferenceToPeriod(
                $data['Appointment'][$i-1]['datetime'],
                $dummyDateTimes[$i], MIN_SECONDS_BETWEEN_APPTS, $timezone);
            if ($result == '<') {
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning false because .", LOG_DEBUG);
                return false;
            }
        }

        // FIXME if appts are not in temporal sequence, return false

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning true.", LOG_DEBUG);

        return true;
    } // function suitableAppointments($data) {



}
?>
