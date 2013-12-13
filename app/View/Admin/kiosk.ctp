<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<div class="span2">
<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links)); ?>
</div>

<div class="span10">

	  <h2>Browser configuration for kiosk use</h2>

<?php
    #FIXME: after testing, turn this into a POST so that using the back button won't change it without a warning message

    if ($isKiosk) {
        print "<p><strong>This browser is currently set for kiosk use.</strong></p>";
        print $this->Html->link('Turn off', '/admin/kiosk/false?' .
	                              AppController::ID_KEY . "=" .
			              $this->Session->read(AppController::ID_KEY));

    } else {
        print "<p><strong>This browser is currently set for non-kiosk use.</strong></p>";
        print $this->Html->link('Configure for kiosk use', 
	    '/admin/kiosk/true?' .  AppController::ID_KEY . "=" .
	    $this->Session->read(AppController::ID_KEY));
    }
?>
<p>"Kiosk mode" is applied to individual computers (not accounts). It makes using this website with this particular computer a bit more secure by shortening the inactivity timeout. This should be set to "kiosk" if this computer is in a clinic environment (as opposed to a computer workstation for one employee's use)</p>

</div>
	
