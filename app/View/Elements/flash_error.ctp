<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
// Customized flashMessage. Set in controller. Example:
// $this->Session->setFlash('You must change your password.','flash_error');
?>

<div id="flashMessage" class="alert alert-error flash-message"><?php echo $message; ?></div>
