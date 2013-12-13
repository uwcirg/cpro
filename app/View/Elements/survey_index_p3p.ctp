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
            <br />
            <p><?= __(LOGIN_WELCOME); ?> research study.</p>
            <?php echo $this->element('session_appt_links'); ?>
        </div>
        <div class="span4">
            
            <div id="video" data-spy="affix"  data-offset-top="235">
                <div class="video-container">
                    <?php
                    $autoPlay = "0";
                    if (!$is_staff) $autoPlay = "1";
                    if ( $this->Session->read('Config.language') == 'en_US' || $this->Session->read('Config.language') == '' ) {
                        echo '<iframe width="360" height="270" src="https://www.youtube-nocookie.com/embed/KBzFf5Tl_BU?controls=1&autoplay='.$autoPlay.'&rel=0&showinfo=0&fs=0" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        echo '<iframe width="360" height="270" src="https://www.youtube-nocookie.com/embed/BBfu7GBxOBk?controls=1&autoplay='.$autoPlay.'&rel=0&showinfo=0&fs=0" frameborder="0" allowfullscreen></iframe>';
                    }
                    ?>              
                </div>            
            </div>
            
        </div>
    </div>
    
    
</div>
    


