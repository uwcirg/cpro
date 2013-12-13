<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class AppointmentsController extends AppController
{

    var $uses = array('Appointment', 'Patient');

    function beforeFilter() {
        parent::beforeFilter();
    }
 
 
    /**
     * Add a patient appointment
     * POST to this service since it creates a new record
     * @param "data[json]", a POST param. Sample:
     *  {"patient_id": "6",  // required unless user is a patient (in which case ignored)
     *  "datetime": "2012-08-15 21:00:00",  // optional default now; pass local i.e. not GMT
     *  "location": "clinic B, front desk",  // optional
     *  "staff_id": "99"}  // optional
     * @param "data[AppController][AppController_id]", a POST param. Pass the "acidValue" js var.
     * @return appropriate http code, and json w/ fields "ok" (bool) and "message". Samples:
     *  {"ok": true, "message": "999 [the id of the new appt]"}
     *  {"ok": false, "message": "Appointments must be at least 48 hours apart."}
     */
    function add() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() w/ request data: " . print_r($this->request->data, true), LOG_DEBUG);

        $this->response->disableCache();

        $result = array();

        if (empty($this->patient) 
                && !is_numeric($this->request->data['Appointment']['patient_id'])) {
            $this->response->statusCode(403);
            $result['ok'] = false;
            $result['message'] = 'Missing patient id';
        }
        elseif (!empty($this->patient) 
                && $this->request->data['Appointment']['patient_id'] 
                    != $this->patient['Patient']['id']) {
            $this->response->statusCode(403);
            $result['ok'] = false;
            $result['message'] = 'Incorrect patient id';
        }
        else {

            $newApptData = array();
            $newApptData = $this->request->data['Appointment']; //location & staff_id

            if (!isset($newApptData['datetime']))
                $newApptData['datetime'] = $this->DhairDateTime->usersCurrentTimeStr();
 
            /** these don't need checking
            if (isset($this->request->data['Appointment']['location'])){
                $newApptData['datetime'] = $this->request->data['Appointment']['location'];
            }
            if (isset($newApptData['staff_id'])){
                $newApptData['datetime'] = $this->request->data['Appointment']['staff_id'];
            }
            */

            if (!empty($this->patient))
                $newApptData['patient_id'] = $this->patient['Patient']['id'];
            else { 
                $newApptData['patient_id'] 
                    = $this->request->data['Appointment']['patient_id'];
                $newApptData['created_staff_id'] = $this->authd_user_id; 
            }

//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's newApptData:" . print_r($newApptData, true), LOG_DEBUG);

            $allApptsData = 
                $this->Patient->find(
                        'first', 
                        array('recursive' => 1,
                              'conditions' => array( 
                                    'Patient.id' => $newApptData['patient_id'])));
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "() allApptsData before adding the new appt: " . print_r($allApptsData, true), LOG_DEBUG);
            $allApptsData['Appointment'][] = $newApptData;
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "() allApptsData after adding the new appt: " . print_r($allApptsData, true), LOG_DEBUG);

            if (!$this->Appointment->suitableAppointments($allApptsData)){
                
                $this->response->statusCode(403);
                $result['ok'] = false;
                $hours = MIN_SECONDS_BETWEEN_APPTS / 60 / 60;


                $timeToNextAppt = MIN_SECONDS_BETWEEN_APPTS / 60 / 60;
                $timeToNextApptUnits = 'hour';
                if ($timeToNextAppt > 72){
                    $timeToNextAppt = $timeToNextAppt / 24;
                    $timeToNextApptUnits = 'day';
                    if ($timeToNextAppt > 6){
                        $timeToNextAppt = $timeToNextAppt / 7;
                        $timeToNextApptUnits = 'week';
                    }
                }
                if ($timeToNextAppt == 1) $timeToNextAppt = '';
                elseif ($timeToNextAppt > 1) $timeToNextApptUnits .= 's';

                $result['message'] = "Appointments must be at least $timeToNextAppt $timeToNextApptUnits apart, and their dates in sequence.";
            }
            else {

                $this->Appointment->create();
                $this->Appointment->save($newApptData);

                $newId = $this->Appointment->id;
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "; newId of save: " . $newId, LOG_DEBUG);

                $result['ok'] = true;
                $result['message'] = $newId;
            }
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning result: " . print_r($result, true), LOG_DEBUG);

        $this->set($result);
        $this->set('_serialize', array_keys($result));

    } // function add()


    /**
     * Edit a patient's appointment
     * PUT to this service
     * @param "data[json]", a PUT param. Sample:
     *  {"data[Patient][id]": "6",  // required unless user is a patient (in which case ignored)
     *  "data[Appointment][0][id]": "12345",  // required
     *  "data[Appointment][0][datetime]": "2012-08-15 21:00:00",  // optional; pass local i.e. not GMT
     *  "data[Appointment][0][location]": "clinic B, front desk",  // optional
     *  "data[Appointment][0][staff_id]": "99"}  // optional
     * @param "data[AppController][AppController_id]", a PUT param. Pass the "acidValue" js var.
     * @return appropriate http code, and json w/ fields "ok" (bool) and "message". Samples:
     *  {"ok": true, "message": "999 [the id of the appt]"}
     *  {"ok": false, "message": "Appointments must be at least 48 hours apart."}
     */
    function edit() {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() w/ request data: " . print_r($this->request->data, true), LOG_DEBUG);

        $this->response->disableCache();

        $result = array();

        if (empty($this->patient) 
                && !is_numeric($this->request->data['Patient']['id'])) {
            $this->response->statusCode(403);
            $result['ok'] = false;
            $result['message'] = 'Missing patient id';
        }
        elseif (!empty($this->patient) 
                && $this->request->data['Patient']['id'] 
                    != $this->patient['Patient']['id']) {
            $this->response->statusCode(403);
            $result['ok'] = false;
            $result['message'] = 'Incorrect patient id';
        }
        else {

            $editedApptsData = $this->request->data['Appointment']; 

//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's editedApptsData:" . print_r($editedApptsData, true), LOG_DEBUG);

            $allApptsData = 
                $this->Patient->find(
                        'first', 
                        array('recursive' => 1,
                              'conditions' => array( 
                                    'Patient.id' => $this->request->data['Patient']['id'])));
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "() allApptsData before adding the new appt: " . print_r($allApptsData, true), LOG_DEBUG);

            foreach ($allApptsData['Appointment'] as $key => $appt){

                foreach($editedApptsData as $key => $editedAppt){
                    if ($appt['id'] == $editedAppt['id']){
                        $allApptsData['Appointment'][$key] = 
                            array_merge($allApptsData['Appointment'][$key], $editedAppt);
                    }
                }
            }
            
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "() allApptsData after adding the new appt: " . print_r($allApptsData, true), LOG_DEBUG);

            if (!$this->Appointment->suitableAppointments($allApptsData)){
                
                $this->response->statusCode(403);
                $result['ok'] = false;
                $hours = MIN_SECONDS_BETWEEN_APPTS / 60 / 60;


                $timeToNextAppt = MIN_SECONDS_BETWEEN_APPTS / 60 / 60;
                $timeToNextApptUnits = 'hour';
                if ($timeToNextAppt > 72){
                    $timeToNextAppt = $timeToNextAppt / 24;
                    $timeToNextApptUnits = 'day';
                    if ($timeToNextAppt > 6){
                        $timeToNextAppt = $timeToNextAppt / 7;
                        $timeToNextApptUnits = 'week';
                    }
                }
                if ($timeToNextAppt == 1) $timeToNextAppt = '';
                elseif ($timeToNextAppt > 1) $timeToNextApptUnits .= 's';

                $result['message'] = "Appointments must be at least $timeToNextAppt $timeToNextApptUnits apart, and their dates in sequence.";
            }
            else {
                foreach($editedApptsData as $key => $editedAppt){

                    $this->Appointment->id = $editedAppt['id'];
                    $editedAppt['patient_id'] = $this->request->data['Patient']['id'];
                    $editedAppt['created_staff_id'] = $this->authd_user_id; 

//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "() editedAppt right before save: " . print_r($editedAppt, true), LOG_DEBUG);
                    $this->Appointment->save($editedAppt);

//                    $this->log(__CLASS__ . "." . __FUNCTION__ . "; done w/ save", LOG_DEBUG);
                }

                $result['ok'] = true;
                $result['message'] = $editedAppt['id'];
            }
        }

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "; returning result: " . print_r($result, true), LOG_DEBUG);

        $this->set($result);
        $this->set('_serialize', array_keys($result));

    } // function edit()

}

