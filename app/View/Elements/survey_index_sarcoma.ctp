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

        <h1><?php echo __(SURVEY_HEADER); ?></h1>

        <p>Through this tab you will be able to report your symptom and quality of life experiences. <strong>Please click or touch the link below to begin reporting</strong>.</p>

        <p><strong>Why report my symptom & quality of life experiences?</strong><br />
            Reporting your experiences with symptoms and quality of life on this secure web site can help you keep track of them over time.</p>

        <p><strong>When should I report my experiences?</strong><br />
            We would recommend reporting your experiences 24-48 hours before your next clinic appointment with your health care provider. Reporting during this time will allow for your clinician to have the most up to date and current information about your experiences.</p>

        <p><strong>Can I report my experiences at other times?</strong><br />
            Yes! You can report your experiences any time you want, and you can choose which symptoms and quality of life issues to track. Only those reports that are completed 24-48 hours before your next clinical appointment will be reported to your clinical team. If you report your experiences at times in between clinic visits, please contact your health care provider if you need immediate assistance or have any questions about your symptom or quality of life concerns. 
</p>

        <div align="center" style="margin-top: 30px">
        <?php
        if(isset($session_link) && $session_link) {
            echo $this->Html->link($session_link[0] ." <i class='icon-chevron-right icon-white'></i>", $session_link[1], array('class' => 'btn btn-large btn-primary', 'escape' => false));
        } 
        ?>
        </div>        
        
</div>
    


