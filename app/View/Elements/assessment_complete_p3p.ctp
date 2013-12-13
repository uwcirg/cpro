<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    * 
*/

// generic message for home after survey is completed
?>
<hr />
<i class="icon-check icon-3x pull-left icon-gray"></i>
<?php
$finish_survey_txt = __("Thank you for answering the P3P questions.");
if (isset($patientProjectsStates[$projectId]->project["Project"]["finish_survey_txt"])){
    $finish_survey_txt 
        = __($patientProjectsStates[$projectId]->project["Project"]["finish_survey_txt"]);
}


?> 
<h4><?php echo $finish_survey_txt ?></h4>
<?php

if ($projectId == P3P_BASELINE_PROJECT){

// echo __("The P3P program is now ready to give you information <strong>matched to your answers</strong>. You also will have a chance to see short videos about topics important to you. At the end, you can print out information to keep.");
echo __("Use the link below to go on.");
?>
<br />

<div style="margin: 30px 0px 30px 80px" id="nextButtons">
<?php
// Finds the tab label and action to be used for button link
foreach($tabs_for_layout as $tab) {
    if ($tab == $selected_tab) {
        $next_tab = current($tabs_for_layout);
        $next_tab_link = $tabControllerActionMap[$next_tab];
    }
}
$buttonLink = '/p3p/' . $next_tab_link['action'];
echo $this->Html->link(__("Get Started").": <strong>"  . __($next_tab) . "</strong> <i class='icon-chevron-right icon-white'></i>", $buttonLink, array('title'=>__("Next Section") . ": " . __($next_tab), 'class' => 'btn btn-large btn-primary', 'escape' => false));
?>
</div>

<?php
}// if ($projectId == P3P_BASELINE_PROJECT){
?>
