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
        print "<p><em>You don't need to report your experiences at this time.</em></p>
            <p>You will be asked to report your experiences in the 48 hours before your next appointment. Please check back here at that time to take the questionnaire.</p>";
            if($participant) {
                    echo "<p>In the meantime, please continue to fill out your ";
                    echo $this->Html->link( "Activity Diary", "/activity_diaries");
                    echo "</p>";
            }
    }
?>        

    <?php if($treatment) { ?>
        <h4>Why report my symptom & quality of  life experiences?</h4>
        <ul>Reporting your experiences with symptoms and quality of life on this secure web site can help you keep track of them over time.</ul>
    <?php } ?>
    <?php if($participant) { ?>
        <h4>When should I report my experiences?</h4>
        <ul>We would like you to report your experiences every two weeks. We will tell you under the "Announcements" section (on the right) when it is time to report your experiences. You can report your experiences in this survey up to 48 hours before your scheduled appointment listed on this page. We will give a printed copy of your completed reports to your care team.</ul>

      <!--<p class="expandable">For the research study, we would like you to report your experiences four times. We call these "Time Points" (T1, T2, etc.) <a href="#" class="expand">More >></a> <span class="hidden"><strong>We will tell you on this page when it is a study Time Point to report your experiences.</strong> The first time will be near the start of your treatment, and the second time will be about 3-4 weeks later. The third and fourth times will be within 2-4 months after your treatment begins. We will give a printed copy of your reports at each  Time Point to your care team in the clinic.</span></p>-->
    <?php } ?>
    <?php if($treatment) { ?>
        <h4>What can I do after reporting my experiences?</h4>
        <ul>You can choose which symptoms and quality of life issues to view and track over time by going to the <?php echo $this->Html->link("View My Reports", "/results"); ?> page. We look forward to hearing from you soon.</ul>

    <!--<p class="expandable">Yes! You can report your experiences  (almost) any time you want, and you can choose which symptoms and quality of life issues to track. <a href="#" class="expand">More >></a> <span class="hidden">The only times you will not be able to pick which symptoms you want to report is during the four Time Points we ask you to report your experiences for the research study. If you are sharing reports with family, friends, or caregivers, they will be able to view every report you complete and invite them to view.</span></p>-->
    <?php }?>
        
</div>


