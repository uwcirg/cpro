<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<script>
    $( function(){
        $("input:password:first").focus();
    });
</script>

<div class="span2 visible-desktop">
           
</div>

<div class="span10">

    <h2><?php echo __('Change Your Password') ?></h2>

    <?php
    // Flash message. Displays change password message on 1st login by user.
    if ($this->Session->check('Message.flash')): echo $this->Session->flash(); endif;
    // Password change. On first login, this is required.   
    ?>  
               
    <p>Your username is <?php echo $userName ?>. Use the form below to enter a new password (you'll have to enter it twice).</p>
    <p>Your password must:</p>
    <ul>
        <?php if (!$is_staff) { ?>
        <li>Be at least <?php echo $minLength ?> characters long</li>
        <li>Contain both letters and numbers</li>
        <?php } else { ?>
        <li>Be at least <?php echo $minLength ?> characters long</li>
        <li>Contain the following type of characters:
            <ul>
                <li>Lowercase letters (a-z)</li>
                <li>Uppercase letters (A-Z)</li>
                <li>Digits (0-9)</li> 
                <li>Any other character ($&?^-*, etc.)</li>
            </ul>
        </li>
        <?php } ?>
    </ul>

    <div class="well span5">
        <?php
        echo $this->Form->create('User', array(
            'action' => 'changePasswordOfAuthdUser'
            ));?>
        <fieldset>
        <?php
            echo $this->Form->input(
                'password', 
                array(
                    'id' => 'data[User][password]',
                    'label' => array('class' => 'control-label'),
                    'label' => __('Password').':', 
                    'placeholder' => __('Enter password here'),
                )
            );
            echo $this->Form->input(
                'password_confirm', 
                array(
                    'type' => 'password',
                    'id' => 'data[User][password_confirm]',
                    'label' => array('class' => 'control-label'),
                    'label' => __('Type your password again').':'
                )
            );
            echo $this->Form->hidden(
                AppController::CAKEID_KEY,
                array('value' => $this->Session->read(AppController::ID_KEY))
            );
            echo $this->Form->submit(__("Change Password"), array("class"=>"btn btn-primary"));
        ?>
        </fieldset>
        <?php echo $this->Form->end();?>

    </div>
    
<script>  
$(function() {
    validatePatientForm("#UserChangePasswordOfAuthdUserForm");
})
</script>

</div>  
