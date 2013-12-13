<p>PainTracker patient ID <?php echo $session['SurveySession']['patient_id'] ?> has completed an <?php echo $session['SurveySession']['type'] ?> assessment and responded to the HUI Emotion question with:</p>

<p>"<?= $responseText ?>"</p>

<p>You may review their record via the link below. </p>

<?php
if (!isset($url))
    $url = Router::url('/', true);
    // $url = $this->Html->url('/');
echo "$url/patients/edit/{$session['SurveySession']['patient_id']}";

?>