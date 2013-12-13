<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<script>
$(document).ready(function(){
    validatePatientForm("#AssociateRegisterFinishForm");
});
</script>

<h1>Welcome to the Electronic Self Report Assessment for Cancer (ESRA-C) project.</h1>

<p>You have been invited to view charts and graphs of symptoms that 
<?php
echo $patient['User']['first_name'];
?> is entering online. If you are already a member of this site, please go to our login page.</p>

<p>Below, you can choose a username and password to log in to this site and see the charts and graphs that <?php echo $patient['User']['first_name']; ?> is sharing with you. 
You can register now with the form below, but to actually view the charts and graphs you will need to find out what the 'secret word' is from 
<?php
echo $patient['User']['first_name'];
?>. We will not share your contact information with any other parties. For more information, please see our privacy policy.
<br/><br/>
<fieldset>
Email: 
<?php 
echo $user['User']['email'];
?>
<br/>
<?php
    // this writes: <form id="AssociateRegisterFinishForm" method="post" action="/dhair2mcjustindev/associates/registerFinish">
    echo $this->Form->create('Associate', // defaults to controller' name
                      array('action' => 'registerFinish'));
    echo $this->Form->input('username', 
                        array(
                            'id' => 'data[User][username]',
                            'autocomplete' => 'off',
                            'name' => 'data[User][username]',
                            'value' => $user['User']['username'])
                        );
    echo "To help keep {$patient['User']['first_name']}'s private information secure, your password should be at least 8 characters long and contain a mix of letters, numbers, and other characters.<br/>";
    //this writes: <input type="password" name="data[User][password]" id="data[User][password]" value="" />
    echo $this->Form->input('password', 
                        array('id' => 'data[User][password]',
                              'autocomplete' => 'off',
                                'name' => 'data[User][password]'            
                        ));
    echo $this->Form->input('password_confirm',
                        array('type' => 'password',
                                'id' => 'data[User][password_confirm]',
                                'name' => 'data[User][password_confirm]'
                        ));
    // a secret phrase for any of their associated patients is ok?
    echo $this->Form->input('secret_phrase');
    echo $this->Form->input('webkey',
                      array('type' => 'hidden',
                            'id' => 'data[Associate][webkey]',
                            'value' => $patientAssociate
                                        ['PatientAssociate']['webkey']));
    echo $this->Form->submit();
    echo $this->Form->end();
?>

</fieldset>
</p>
