<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

echo "<h2>Survey Menu</h2>";
echo "<p>You are completing a self-initiated report; since this is not for research study, you can select the questionnaires you want to report. The results for this survey will not be viewable on the \"View My Reports\" page until you press 'Complete' below.</p>";
echo "<ul>";
foreach($qrs as $qr) {
    echo "<li><h3>";

    if ($qr["FriendlyTitle"] != null){
        echo $this->Html->link($qr["FriendlyTitle"], "/surveys/questionnaire/$qr[id]");
    }
    else {
        echo $this->Html->link($qr["Title"], "/surveys/questionnaire/$qr[id]");
    }
    echo "</h3></li>";
}
echo "</ul>";
?>

<div style="margin-top: 50px; text-align: center"><?php echo $this->Html->link("Complete", "complete", array('class'=>'survey-link ui-state-default ui-corner-all calcInit','style'=>'font-size: 1.2em')); ?></div>


  
