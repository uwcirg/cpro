<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    * 
*/

// Generic message for users/index to begin assessment.
?>
<hr />

<?php
$patientDesignated = false;
if (isset($patientProjectsStates[$projectId]->project->session_pattern)
    && $patientProjectsStates[$projectId]->project->session_pattern == PATIENT_DESIGNATED_APPTS){
    $patientDesignated = true;
}

if (empty($patientProjectsStates[$projectId]->project['Project']['concise_session_launch_txt'])){
?>

<i class="icon-pencil icon-3x pull-left icon-gray"></i> 
<h4><?php echo __("Get started") ?></h4>
<?php
if ($patientDesignated){
    echo '<p>' . __('You have indicated that you have a clinic appointment scheduled soon; please report your experiences before the appointment.') . '</p>';
} else {
    echo __("Thank you for agreeing to take part in the ".SHORT_TITLE." program. Use the link below to begin.");    
}  
?>
<br />

<?php
}
?>


<div style="margin: 30px 0px 30px 80px" id="nextButtons" <?php
    // Change to larger font - used on 'What do you think?' in P3P
    if ($patientProjectsStates[$projectId]->project["Project"]["id"] == '8') {
        echo 'class="big-text"';
    }
?>>
<?php
    $text = 'Report My Experiences Now';
    if (isset($patientProjectsStates[$projectId]->project["Project"]["start_survey_txt"])){
        $text =
            $patientProjectsStates[$projectId]->project["Project"]["start_survey_txt"];
    }
    echo $this->Html->link(
        __($text) . ' <i class="icon-chevron-right icon-white"></i>', 
        '/surveys/new_session/' 
            . $patientProjectsStates[$projectId]->project["Project"]["id"], 
        array('id' =>'surveyButton', 
                'class' =>'btn btn-large btn-primary', 
                'escape' => false)); 
?>
</div>
<?php
if ($patientDesignated) {
    // call new patients/deleteAppt ajax fxn and then reload page?
    //echo $this->Html->link(__('Whoops, I don\'t have an appointment scheduled with the clinic <i class="icon-chevron-right icon-white"></i>'), array(), array('class' =>'btn btn-large btn-primary', 'escape' => false)); 
    echo '<div id="deleteAppt" class="btn btn-large btn-primary">' . __('Whoops, I don\'t have an appointment scheduled with the clinic') . ' <i class="icon-chevron-right icon-white"></i></div>';
}
?>
