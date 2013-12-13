<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    * 
*/
?>

<hr />

<?php
if (empty($patientProjectsStates[$projectId]->project['Project']['concise_session_launch_txt'])){
?>

<i class="icon-pencil icon-3x pull-left icon-gray"></i> 
<h4><?php echo __("Continue your session") ?></h4>
<?php
echo __("Use the link below to continue where you left off.");
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
// generic message for home to resume assessment

$text = 'Continue Report';
if (isset($patientProjectsStates[$projectId]->project["Project"]["continue_survey_txt"])){
    $text =
        $patientProjectsStates[$projectId]->project["Project"]["continue_survey_txt"]; 
}
//}

if (isset($patientProjectsStates[$projectId]->project->session_pattern) 
    && $patientProjectsStates[$projectId]->project->session_pattern == INTERVAL_BASED_SESSIONS){
    echo $this->Html->link(__($text).' <i class="icon-chevron-right icon-white"></i>', '/surveys/restart/' . $patientProjectsStates[$projectId]->resumableNonApptSession['SurveySession']['id'], array('id' =>'surveyButton', 'class' =>'btn btn-large btn-primary', 'escape' => false)); 
} 
elseif (isset($patientProjectsStates[$projectId]->apptForResumableSession)) {
    echo $this->Html->link(__($text).' <i class="icon-chevron-right icon-white"></i>', '/surveys/restart/' . $patientProjectsStates[$projectId]->apptForResumableSession['SurveySession']['id'], array('id' =>'surveyButton', 'class' =>'btn btn-large btn-primary', 'escape' => false)); 
} 
else {
    echo $this->Html->link(__($text).' <i class="icon-chevron-right icon-white"></i>', '/surveys/restart/' . $patientProjectsStates[$projectId]->resumableNonApptSession['SurveySession']['id'], array('id' =>'surveyButton', 'class' =>'btn btn-large btn-primary', 'escape' => false)); 
} 
              
?>
</div>
