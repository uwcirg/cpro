<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div class="subsection right">
   <div class="sidebox rounded">
   <h2>Quick links</h2>
   <ul>
   <?php foreach($quick_links as $text => $link) {
   $link = $this->Html->link($text, $link);
   echo "<li>$link</li>";
 } ?>
   </ul>
   </div>
</div>

<div  class="subsection left">

    <h1>Settings</h1>
    <?php if ($this->Session->check('Message.flash')): echo $this->Session->flash(); endif; ?>  
    <div id="settings-info">
        <p><?php echo __('Use the "Quick Links" menu on the right to change your personal settings.')?></p>
    </div>

</div>	

<script>  
$(document).ready(function(){
    validatePatientForm("#UserChangePasswordOfAuthdUserForm");
});
</script>
