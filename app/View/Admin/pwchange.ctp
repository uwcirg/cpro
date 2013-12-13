<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<h1>Change any user's password</h1>
<p>Need to restrict access to this, obviously...</p>
<?php

echo $this->Form->create('User', array('action' => 'changeanyuserspw');
echo $this->Form->input('username');
echo $this->Form->input('password');
echo $this->Form->input('password_confirm', array('type' => 'password'));
echo $this->Form->submit();
echo $this->Form->end();
?>

