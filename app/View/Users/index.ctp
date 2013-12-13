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

<?php
        $this->InstanceSpecifics->echo_instance_specific_elem('intro');

        echo $this->element('session_appt_links');
?>
</div>
