<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/


?>

<script>
$(document).ready(function() {
    $('.ajaxSubmit').click( function(event) {
        var request = $.ajax({
            type: "PUT",
            url: appRoot + 'patients/optOut/<?= $user['User']['webkey'];?>.json',
            dataType: 'json',
            async: false,
            data: {
                "data[response]" : event.target.id,
                "data[AppController][AppController_id]" : <?= $this->Session->read(AppController::ID_KEY);?>
            }
        });
        
        request.done(function(data, textStatus, jqXHR) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            // Open modal with the confirmation note. Was an alert.
            $("#modalConfirm").modal();
        });
        // When modal is closed, redirect to about page.
        $('#modalConfirm').on('hidden', function () {
            window.location = '<?php echo Router::url('/users/about', true);?>';
        })

        request.fail(function(jqXHR, textStatus, errorThrown) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            alert('Problem: ' + responseTxt.message);
        });         
        
    });

});

</script>

<div class="span10 offset2">
    
    <h2>Opt Out Request</h2>
    <p>You clicked on the e-mail link saying you weren't interested in using the
        Electronic Self Report Assessment for Sarcoma Cancer (ESRA-C) program.</p>
    <p>To confirm that you're not interested and want to stop receiving e-mails about 
        the program, click on the blue button below.</p>

<?php

echo $this->Form->create('User', array('default' =>false));
echo $this->Form->button(
    'I\'m not interested in ESRA-C. Remove me from the program.',
    array(
        'type' => 'button',
        'id' => 'yes',
        'style' => 'margin: 12px 0 0 24px',
        'class' => 'ajaxSubmit btn btn-primary'
    )
);

$link = $this->webroot . 'users/login/';
echo $this->Form->button(
    'I am interested. Register now.',
    array(
        'type' => 'button',
        'class' => 'btn btn-small',
        'style' => 'margin: 12px 0 0 24px',
        'onclick' => "location.href='". $link."'"
    )
);

echo $this->Form->end();

?>

</div>

  <!-- Address Section List Modal -->
  <div id="modalConfirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalConfirmTitle" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 id="modalConfirmTitle">Removal - Confirmed</h3>
    </div>
    <div class="modal-body">
        <p>You've been removed from the ESRA-C program.</p>
        <p>Thank you for your consideration. If you'd like to opt back in, please contact the clinic staff.</p>
    </div>
    <div class="modal-footer">
      <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
  </div>

