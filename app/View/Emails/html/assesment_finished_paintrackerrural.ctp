<p>PainTracker patient ID <?php echo $session['SurveySession']['patient_id'] ?> has completed an <?php echo $session['SurveySession']['type'] ?> assessment <?php if (isset($MEDChangeText) and $MEDChangeText) echo ' and entered a change in medication' ?>.

Please enter medication data for this patient before

<?php

$reportable_datetime = new DateTime($session['SurveySession']['reportable_datetime']);
$reportable_datetime->add(new DateInterval('P' . 1 . 'D'));
echo $reportable_datetime->format('g:iA n/j/y \(l\)');
?>, via the link below. </p>

<?php
if (!isset($url))
    $url = Router::url('/', true);
    // $url = $this->Html->url('/');
echo $url . '/patients/edit/' . $session['SurveySession']['patient_id'];

if (isset($MEDChangeText) and $MEDChangeText)
    echo ' Here is the data the patient entered: <br>', $MEDChangeText;
?>