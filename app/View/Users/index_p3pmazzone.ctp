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

<?php
    echo $this->element('check_browser_compat');
?>

    <h1><?php echo $welcome_text; ?></h1>





<div class="row">
    <div class="span6">
        
    <?php
    if (isset($patient)){
    ?>
        <p><?php echo __("You are logged in to P3P - your personal patient profile for prostate cancer. This site allows you to build a profile by answering questions, and then view personalized information about prostate cancer care options.")?></p>

<?php	
        echo $this->element('session_appt_links');
?>

    </div>
    <div class="span4">
        <div id="video" data-spy="affix"  data-offset-top="235">
            <div class="video-container">
                <?php
                if ( $this->Session->read('Config.language') == 'en_US' || $this->Session->read('Config.language') == '' ) {
                    echo '<iframe width="360" height="270" src="https://www.youtube-nocookie.com/embed/nqdID8IR4sg?controls=1&autoplay=1&rel=0&showinfo=0&fs=0&start=5&end=55" frameborder="0" allowfullscreen></iframe>';
                } else {
                    echo '<iframe width="360" height="270" src="https://www.youtube-nocookie.com/embed/PHEb5b_iFsc?controls=1&autoplay=1&rel=0&showinfo=0&fs=0&start=5&end=55" frameborder="0" allowfullscreen></iframe>';
                }
                ?>              
            </div>            
        </div>
    </div>
    <?php
    // End if isset($patient) - otherwise admin
    } else {
    ?>
        <p><?php echo __("You are logged in as an administrator. Use the tabs above to view patient records, set appointments and access the data.")?></p>
    <?php
    }
    ?>

</div>

