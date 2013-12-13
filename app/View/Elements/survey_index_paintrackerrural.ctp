<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
?>

<div class="span2 visible-desktop">
           
</div>

<div class="span10 esrac-main">
    
        <h2>Report My Experiences</h2>

        <?php
        if(isset($session_link) && $session_link) {
            echo "<p><em><strong>It's time for you to report your experiences</strong></em>. Click on the button below to begin.</p>";
            echo $this->Html->link($session_link[0], $session_link[1], array('class' => 'btn btn-primary btn-large', 'escape' => false, 'style' => 'margin: 20px 40px'));
        } else {
            echo "<p><em>You don't need to report your experiences at this time.</em></p>";
            if ($treatment)
                echo "<p>You will be asked to report your experiences one week after the last time. Please check back here at that time to take the questionnaire.</p>";
            if ($participant)
                echo "<p>You will be asked to report your experiences every other week. Please check back here at that time to take the questionnaire.</p>";

        }
        ?>


        <h4>What can I do after reporting my experiences?</h4>
        <ul>You can choose which symptoms and quality of life issues to view and track over time by going to the <?php echo $this->Html->link("View My Dashboard", "/patients/dashboardForSelf/"); ?> page. We look forward to hearing from you soon.</ul>

</div>


