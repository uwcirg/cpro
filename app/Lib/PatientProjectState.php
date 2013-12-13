<?php
/**
 * A simple container for a few variables
 *
 */
class PatientProjectState extends Object 
{

    var $patient_id;

    var $project;

    //var $project_id;
    // The next four objects are mutually exclusive, ie only one can be non-null
    var $apptForNewSession = null; // appt that can have a new session created for it now
    var $apptForResumableSession = null; // appt for session that's resumable, and is from within the past MIN_SECONDS_BETWEEN_APPTS
    var $apptForFinishedSession = null; // appt for session that's finished, and is from within the past MIN_SECONDS_BETWEEN_APPTS
    var $initableNonApptSessionType = null; // ELECTIVE, etc.

    var $resumableNonApptSession = null; // for non-appt sessions

    var $finishedNonApptSession = null;

    var $sessionLink; // an array directly consumable by the HtmlHelper->link, for creating or resuming a survey session

    var $availableDateRangeStart = null;
    var $availableDateRangeEnd = null;

    /**
     *
     */
    function __construct($patient_id, $project){
        $this->patient_id = $patient_id;
        $this->project = $project;
        //$this->project_id = $project['Project']['id'];
    }

}
