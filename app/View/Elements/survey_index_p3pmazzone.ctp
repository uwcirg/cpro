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
        
        <p><?=__("Click or touch the button below to build your profile. You'll be asked a series of questions about your health.")?></p>

        <div align="center" style="margin-top: 30px">
        <?php
        if(isset($session_link) && $session_link) {
            echo $this->Html->link($session_link[0] ." <i class='icon-chevron-right icon-white'></i>", $session_link[1], array('title'=>__("Go to the P3P Survey."), 'class' => 'btn btn-large btn-primary', 'escape' => false));
        } 
        ?>
        </div>        
        
</div>
    


