<?php
/**
*
* Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
*
*/

if (isset($patient)){

$patientProjectsState = array_shift(array_values($patientProjectsStates));
?>

<div class="row">
<div class="span10">
    <h2>Report My Experiences</h2>
    <?php
    if(isset($patientProjectsState->currentSession) and $patientProjectsState->currentSession) {
        echo "<p><em><strong>It's time for you to report your experiences</strong></em>. Click on the button below to begin.</p>";
        // echo $this->Html->link('Report my experiences', $session_link, array('class' => 'btn btn-primary btn-large', 'escape' => false, 'style' => 'margin: 20px 40px'));

        $now = new DateTime("now", $patientProjectsState->currentSession['stop']->getTimezone());
        $diff = $patientProjectsState->currentSession['stop']->diff($now);

        if ($diff->format('%a') > 0)
            $timeToComplete = $diff->format('%a') . ' day';
        else
            $timeToComplete = $diff->format('%h') . ' hour';

        if ($diff->format('%a') > 1 or ($diff->format('%h') > 1 and $diff->format('%a') < 1))
            $timeToComplete .= 's';

        if ($diff->format('%a') > 0)
            $timeToComplete .= ' (until ' . $patientProjectsState->currentSession['stop']->format('m/d/y\)');

        echo "You will have $timeToComplete to complete it";

    } else {
        echo "<p><em>You don't need to report your experiences at this time.</em></p>";
        if ($treatment)
            echo "<p>You will be asked to report your experiences one week after the last time. Please check back here at that time to take the questionnaire.</p>";
        else
            echo "<p>You will be asked to report your experiences every other week. Please check back here at that time to take the questionnaire.</p>";
    }
    if ($patientProjectsState->nextSession){
        echo '<p>Your next survey session will become available on '. $patientProjectsState->nextSession['start']->format(' m/d/y') . ' and close on ' . $patientProjectsState->nextSession['stop']->format(' m/d/y') . '</p>';
    }
    else
        echo 'Thank you for participating in this study. We appreciate the time you have spent in completing the weekly questionnaires.';

    ?>

    <h4>What can I do after reporting my experiences?</h4>
    <ul>You can choose which symptoms and quality of life issues to view and track over time by going to the

    <?php
    if (in_array('Dashboard', $tabs_for_layout))
        echo $this->Html->link("View My Dashboard", "/patients/dashboardForSelf/"). ' page';
    else
        echo '"View My Dashboard" page after reporting your experiences'
    ?>. We look forward to hearing from you soon.</ul>
</div>
</div>

<?php
} else
     echo '<p>' . __("You are logged in as an administrator. Use the tabs above to view patient records, set appointments and access the data.") . '</p>';
?>
