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

<div class="span10">
        
<h1><?php echo __('Registration') ?></h1>

<h4 style="margin-top: 1em"><?php echo __('Step 2 - Select username and password') ?></h4>
<p style="margin-bottom: 1em"><?php echo __('OK, we found you in our system. Finish registration by picking a username and password below.') ?></p>

<div id="regProblem" class="alert alert-error hide"></div>

<div class="well span5">
<?php
echo $this->Form->create('User', array(
        'default' =>false, // disable submit
        'inputDefaults' => array(
                        'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
                        'div' => array('class' => 'control-group'),
                        'label' => array('class' => 'control-label'),
                        'between' => '<div class="controls">',
                        'after' => '</div>',
                        'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline'))
            )
));

$usernameLabel = 'You can either use your email address, or choose a different name at least 3 letters or numbers long.';
if ($user['User']['username_tmp'] != $user['User']['email']){
    $usernameLabel = 'Your username must be at least 3 letters or numbers long.';
}


echo $this->Form->input(
                    'username', 
                    array('default' => $user['User']['username_tmp'],
                        'between' => '<span class="help-block">'.$usernameLabel.'</span><div class="controls">'
                        ));

echo $this->Form->input(
        'password',
        array(
            'id' => 'data[User][password]',
            'label' => array(
                'class' => 'control-label',
                'text' => 'Password:'
            ),
            'between' => '<span class="help-block">'. __("Your password must be at least %d letters or numbers long. It must contain at least 1 letter and at least 1 number. For example, speedy79 or My2dogs.", $minLength).'</span><div class="controls">',
            'placeholder' => 'Enter password here'
        )
);
echo $this->Form->input(
        'password_confirm',
        array(
            'type' => 'password',
            'id' => 'data[User][password_confirm]',
            'label' => array(
                'class' => 'control-label',
                'text' => 'Type your password again:'
            ),
            'placeholder' => 'Re-enter password here'
        )
);
echo $this->Form->button('Finish', array(
                    'type' => 'button',
                    'class' => 'btn btn-large btn-primary',
                    'id' => 'ajaxSubmit'));

echo $this->Form->end();


?>
    </div>
</div>

<div id="calculatingBox"  class="calculating-box modal hide fade">
  <div class="modal-header">
    <h3><?=__('Checking your information')?></h3>
  </div>
  <div class="modal-body">
    <p><?=__("One moment please while the system sets up your account.")?></p>
    <p><?php echo $this->Html->image('loading.gif', array('alt'=>'Saving', 'style'=>'vertical-align: middle')); ?></p>
  </div>
</div>

<script>
$(document).ready(function() {

    validatePatientForm("#UserSelfRegisterForm");

    $("#UserUsername").focus();

    // Submit form
    $('#ajaxSubmit').click( function() {

        // Hide any existing error message
        $('#regProblem').hide();
        
        var request = $.ajax ({
            type: "PUT",
            url: appRoot + 'users/<?= $user['User']['id'];?>.json',
            url: appRoot + 'users/registerEdit.json',
            dataType: 'json',
            async: false,
            data: {"data[User][username]" : $('#UserUsername').val(), 
                // Switched to use name attribute for password b/c id was causing
                // collisions with jquery.validate
                "data[User][password]" : $('input[name="data[User][password]"]').val(),
                "data[User][password_confirm]" : $('input[name="data[User][password_confirm]"]').val(),
                "data[User][id]" : "<?= $user['User']['id'];?>",
                "data[AppController][AppController_id]" : "<?= $this->Session->read(AppController::ID_KEY);?>"
            }
        });
        var ajaxCheck, ajaxSucceed, ajaxFail;
        request.done(function(data, textStatus, jqXHR) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            //alert ('Thank you for registering. You will now be forwarded to your home page.');
            ajaxCheck = true;
            ajaxSucceed = '<?=Router::url('/users/login', true);?>';
        });
        request.fail(function(jqXHR, textStatus, errorThrown) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            var alertTxt = '<?php 
                    $sorry =  __("Please try again. If you continue to have trouble, please visit the ");
                    $sorry .= $this->Html->link(__("Help page"), "/users/help");
                    $sorry = str_replace("'", "\'", $sorry);
                    echo $sorry;
                    ?>';
            ajaxFail = '<strong>Problem</strong>: ' + responseTxt.message + ' ' + alertTxt + '.';
        }); 

        // Show spinner modal
        $("#calculatingBox").modal({
            backdrop: 'static',
            keyboard: false
        });
        // Timeout, then if success then load new page. Otherwise hide modal
        // and display error message.
        setTimeout(function() {
            if (ajaxCheck) {
                window.location = ajaxSucceed;
            } else {
                $('#calculatingBox').modal('hide');
            }
        }, 2000);
        $('#calculatingBox').on('hidden', function () {
            $('#regProblem').html(ajaxFail).fadeIn();
        });
        return false;

    });

});

</script>


