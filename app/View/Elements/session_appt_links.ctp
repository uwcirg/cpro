<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<?php
if (!$is_staff){

    $linkDisplayed = false;
    $assessmentCompleteFor;
    foreach($patientProjectsStates as $projectId => $state){

        $project = $state->project['Project'];
        //TODO would be nice to use DhairAuth for this
        if (!strstr($project['roles_that_can_assess'], 'aroPatient') 
            && !strstr($project['roles_that_can_assess'], 'aroParticipant')){
            continue;
        } 
  
        if (isset($project['contextLimiter']) // eg 'p3p/whatdoyouthink'
            && !strstr($project['contextLimiter'], 
                    $this->request->params['controller'] . '/' 
                    . $this->request->params['action'])){ 
            continue;
        } 
  
/**
        echo '<b>state->apptForNewSession</b>:' . print_r($state->apptForNewSession, true) . '<br/><br/>';
        echo '<b>state->apptForResumableSession</b>:' . print_r($state->apptForResumableSession, true) . '<br/><br/>';
        echo '<b>state->apptForFinishedSession</b>:' . print_r($state->apptForFinishedSession, true) . '<br/><br/>';
        echo '<b>resumableNonApptSession</b>:' . print_r($resumableNonApptSession, true) . '<br/><br/>';
        echo '<b>state->initableNonApptSessionType</b>:' . print_r($state->initableNonApptSessionType, true) . '<br/><br/>';
*/

        // $state->initableNonApptSessionType, $resumableNonApptSession for INTERVAL_BASED_SESSIONS
        // $state->apptForNewSession, $state->apptForResumableSession, state->apptForFinishedSession otherwise
        // Depending on status, the corresponding element is called and logic is
        // included there to determine what to display

        // FIXME  some of these (assessment_complete, at least) should be project-specific, not instance-specific.

        if ($state->apptForResumableSession || $state->resumableNonApptSession){
            $this->InstanceSpecifics->echo_instance_specific_elem('assessment_resume',
                array("projectId" => $projectId));
            $linkDisplayed = true;
            break;
        }
        elseif($state->apptForNewSession || $state->initableNonApptSessionType){
            $this->InstanceSpecifics->echo_instance_specific_elem('assessment_start',
                array("projectId" => $projectId));
            $linkDisplayed = true;
            break;
        }
        elseif ($state->apptForFinishedSession || $state->finishedNonApptSession){

            $assessmentCompleteFor = $projectId;
        }
        elseif ($project['session_rules_fxn'] == PATIENT_DESIGNATED_APPTS){
            // TODO - this should probably be moved to its own element

                $timeToNextAppt = MIN_SECONDS_BETWEEN_APPTS / 60 / 60;
                $timeToNextApptUnits = 'hour';
                if ($timeToNextAppt > 72){
                    $timeToNextAppt = $timeToNextAppt / 24; 
                    $timeToNextApptUnits = 'day';
                    if ($timeToNextAppt > 13){
                        $timeToNextAppt = $timeToNextAppt / 7; 
                        $timeToNextApptUnits = 'week';
                    }
                }
                if ($timeToNextAppt == 1) $timeToNextAppt = ''; 
                elseif ($timeToNextAppt > 1) $timeToNextApptUnits .= 's'; 
                echo "<br/>";
                echo "<h4>" . __('Do you have an appointment in the next') 
                    . ' ' . $timeToNextAppt . ' ' . $timeToNextApptUnits . '?</h4>';
                echo "<br/>";
                echo '<div id="createAppt" class="btn btn-large btn-primary">Yes <i class="icon-chevron-right icon-white"></i></div>';
            //echo $this->Html->link(__('Yes <i class="icon-chevron-right icon-white"></i>'), '/patients/createAppt', array('class' =>'btn btn-large btn-primary', 'escape' => false)); 
                if ($project['elective_sessions']){
                    echo '<button class="btn btn-large" id="buttonNo">No, but I\'d like to report my experiences anyways</button>';
                }
                $linkDisplayed = true;
            break;
        }

    }// foreach($patientProjectsStates as $projectId => $state){

    // This is what gets displayed if the survey and interventin aren't available
    // Typically this will show up if it's past appt date and they didn't complete it.
    if (!$linkDisplayed/** && !$state->apptForFinishedSession*/){

        if (isset($assessmentCompleteFor)){
            $this->InstanceSpecifics->echo_instance_specific_elem('assessment_complete',
                array("projectId" => $assessmentCompleteFor));
        }
        else {

            echo "<hr />";
            echo "<h4>".SHORT_TITLE.__(" is not available for you at this time.")."</h4>";
            $text = __('Please talk with the clinic staff about reporting your experiences');
            if (defined('ASK_STAFF_ABOUT_SURVEY')){
                $text = __(ASK_STAFF_ABOUT_SURVEY);
            }
            echo "<p>".__($text).". See the ";
            echo $this->Html->link(__("help page"), "/users/help", array('title'=>__("Go to the Help Page")));
            echo " for contact information.</p>"; 
        }
    }

    echo $this->element('single_session_launch');
?>

<script language="javascript" type="text/javascript">
$(document).ready(function() {
    $('#createAppt').click( function() {
        var request = $.ajax ({
            type: "POST", // since we're creating a new record
            url: appRoot + 'appointments/add.json',
            dataType: 'json',
            async: false,
            data: {"data[Appointment][patient_id]" : "<?= $patient['Patient']['id'];?>", 
                "data[AppController][AppController_id]" : "<?= $this->Session->read(AppController::ID_KEY);?>"
            }
        });
        request.done(function(data, textStatus, jqXHR) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            //alert ('Thank you for creating the appt (remove this dialog).');
            window.location = '<?=Router::url('/users/index', true);?>';
        });
        request.fail(function(jqXHR, textStatus, errorThrown) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            alert ('Problem: ' + responseTxt.message);
        }); 
    });
    $('#deleteAppt').click( function() {
        alert ('<?= __('Please contact the help staff so they can update your records');?>');
        window.location = '<?=Router::url('/users/help', true);?>';
    });
});
</script>
<?
}//if (!$is_staff){
?>
