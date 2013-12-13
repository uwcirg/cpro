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
    <?php if ($purpose == 'login_assist'): ?>
        <h1><?= __('Login Help') ?></h1>
        <?= __('To retrieve your username and reset your password, please fill in these fields.') ?>
    <?php elseif ($purpose == 'anonymous_access'): ?>
        <h1><?= __('Registration') ?></h1>
        <h4 style="margin-top: 1em"><?= __('Step 1 - Match Your Record') ?></h4>
        <p style="margin-bottom: 1em"><?= __('It looks like you\'re ready to create an account for ' . SHORT_TITLE . '. Please start by filling in this form so we can match your record.') ?></p>   
    <?php else: ?>
        <h1><?= __('Registration') ?></h1>
        <h4 style="margin-top: 1em"><?= __('Step 1 - Match Your Record') ?></h4>
        <p style="margin-bottom: 1em"><?= __('Get started by filling in this form so we can match your record.') ?></p>   
    <?php endif; ?>
    <div id="regProblem" class="alert alert-error hide"></div>
    <br />

    <div class="well span4">
        <?php
        echo $this->Form->create('Patient', array(
            'default' => false, // disable submit
            'inputDefaults' => array(
                'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
                'div' => array('class' => 'control-group'),
                'label' => array('class' => 'control-label'),
                'between' => '<div class="controls">',
                'after' => '</div>',
                'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline'))
                )));
        echo "<fieldset>";
        echo $this->Form->input(
                'User.first_name', array(
            'id' => 'data[User][first_name]',
            'class' => 'required',
            'label' => array(
                'class' => 'control-label',
                'text' => __('First name:')
            ),
            'placeholder' => __('Enter your first name here')
                )
        );
        echo $this->Form->input(
                'User.last_name', array(
            'id' => 'data[User][last_name]',
            'class' => 'required',
            'label' => array(
                'class' => 'control-label',
                'text' => __('Last name:')
            ),
            'placeholder' => __('Enter your last name here')
                )
        );
        echo $this->Form->label('Patient.birthdate', 'Birth date:');

        echo '<span class="help-block">Click on the boxes below to choose the month, day and year of your birthday.</span>';
// TODO use an easy date picker?
        echo $this->Form->dateTime('Patient.birthdate', 'MDY', null, array('style' => 'width: auto',
            'class' => 'required',
            'minYear' => '1900',
            'maxYear' => '2000',
            'empty' => array(
                'day' => 'Day...',
                'month' => 'Month...',
                'year' => 'Year...'
            )
        ));
        echo "<br /><br />";
        echo $this->Form->button(__("Next") . " <i class='icon-chevron-right icon-white'></i>", array(
            'type' => 'button',
            'escape' => false,
            'class' => 'btn btn-large btn-primary',
            'id' => 'ajaxSubmit'));
        echo "</fieldset>";
        echo $this->Form->end();
        ?>
    </div>
</div>

<div id="calculatingBox"  class="calculating-box modal hide fade">
    <div class="modal-header">
        <h3><?= __('Matching your information') ?></h3>
    </div>
    <div class="modal-body">
        <p><?= __("One moment please while the system looks for your name.") ?></p>
        <p><?php echo $this->Html->image('loading.gif', array('alt' => 'Saving', 'style' => 'vertical-align: middle')); ?></p>
    </div>
</div>

<script>
    $(document).ready(function() {

        validatePatientForm("#PatientIdentifyForm");

        $("#data[User][first_name]").focus();

        $('#ajaxSubmit').click( function() {
        
            // Hide any existing error message
            $('#regProblem').hide();

            var request = $.ajax ({
                type: "POST",
                url: appRoot + 'users/identify.json',
                //url: appRoot + 'users/registerEdit.json',
                dataType: 'json',
                async: false,
                data: {"data[User][first_name]" : $('input[name="data[User][first_name]"]').val(), 
                    "data[User][last_name]" : $('input[name="data[User][last_name]"]').val(),
                    //"data[Patient][birthdate]" : $('input[name="data[Patient][birthdate]"]').val(),
                    "data[Patient][birthdate]" : 
                        $('select[name="data[Patient][birthdate][year]"]').val() 
                        + '-' + $('select[name="data[Patient][birthdate][month]"]').val()
                        + '-' + $('select[name="data[Patient][birthdate][day]"]').val(),//FIXME do something datepicker friendly 
                    "data[User][id]" : "<?= $user['User']['id']; ?>",
                    "data[Webkey][text]" : "<?= $webkeyText; ?>",
                    "data[AppController][AppController_id]" : "<?= $this->Session->read(AppController::ID_KEY); ?>"
                }
            });
        
            var ajaxCheck, ajaxSucceed, ajaxFail;
            request.done(function(data, textStatus, jqXHR) {
                var responseTxt = jQuery.parseJSON(jqXHR.responseText);
                ajaxCheck = true;
                ajaxSucceed = '<?= Router::url('/', true); ?>' + responseTxt.message;
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {
                var responseTxt = jQuery.parseJSON(jqXHR.responseText);
                var alertTxt = '<?php
                    $sorry = __("<strong>Sorry, we can't find you in the system</strong>. Please try again. If you continue to have trouble, please visit the ");
                    $sorry .= $this->Html->link(__("Help page"), "/users/help");
                    $sorry = str_replace("'", "\'", $sorry);
                    echo $sorry;
                    ?>'
                ajaxFail = alertTxt + '.';
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


