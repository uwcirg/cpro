<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

            <a name="emailsSent"><h3>E-mails
                <button class="btn btn-mini minimize-section" data-hide="#emailInfoEdit" id="emailVizBtn"><i class="icon-chevron-up"></i> Hide</button>
            </h3></a>
            <div id="emailInfoEdit" class="well admin-edit-section">
            <?php
            if (isset($emailTemplates) && (count($emailTemplates) > 0)
            ){
                $sendableEmailTemplates = $emailTemplates;
                foreach ($sendableEmailTemplates as $templateName => $template){
                    if ($template['sendable'])
                        $sendableEmailTemplates[$templateName] 
                            = $template['text'];
                    else unset($sendableEmailTemplates[$templateName]);
                }

                if (!empty($sendableEmailTemplates)){
                // todo cleanup ws 
                    ?>
                    <h4>Send E-mail to Patient</h4>
                    <?php

                echo $this->Form->create('Patient', array(
                    'action' => 'email',
                    'inputDefaults' => array(
                        'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
                        'div' => array('class' => 'control-group'),
                        'label' => array('class' => 'control-label'),
                        'between' => '<div class="controls">',
                        'after' => '</div>',
                        'class' => 'span2',
                        'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline'))
                    )
                ));
                echo '<p>Send a standard e-mail to this patient. Choose from the list below.</p>';
                echo '<div style="margin-left: 20px">';
                echo $this->Form->hidden(
                    AppController::CAKEID_KEY,
                    array('value' => $this->Session->read(AppController::ID_KEY)
                ));

                echo $this->Form->select('emailTemplate', 
                                        $sendableEmailTemplates, array(
                                                'empty' => 'Select e-mail...')
                );
                ?>
                <div class="alert alert-error hide" id="noEmailAlert" style="margin-bottom: 8px">
                    <button type="button" class="close">&times;</button>
                    An e-mail address is required. Edit the patient to add one.
                </div>
                <div class="alert hide" id="sendEmailAlert" style="margin-bottom: 8px">
                    <button type="button" class="close">&times;</button>
                    <span id="displayEmail"></span>
                </div>
                <?php
                echo $this->Form->submit("Send E-mail", array(
                        "class"=>"btn btn-small disabled",
                        "disabled"=>"disabled",
                        "id"=>"sendEmail"
                    )
                );
                echo "</div>";
                echo $this->Form->end();
            } //if (!empty($sendableEmailTemplates)){
            else {
                echo '<p style="margin-left: 20px">No e-mails are available to send at this time.</p>';
                // Modal for details about why not emails can be sent. Removed 
                // for now b/c it was too specific to P3P
                /*
                 * Launch with:
                 * <a href="#emailInfo" class="link-with-icon" data-toggle="modal"><i class="icon-question-sign"></i></a>
                 * 
                 * Launches:
                 * <!-- Modal for details about e-mails -->
                <div id="emailInfo"  class="modal hide fade">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>E-mails</h3>
                  </div>
                  <div class="modal-body">
                    <p>For registration e-mails, a patient must:</p>
                    <ul>
                        <li>have a birth date and e-mail listed</li>
                        <li>be confirmed as eligible via the assessment tool</li>
                        <li>have an upcoming appointment</li>
                    </ul>
                  </div>
                </div>
                 */
                
            } 
            } // if $emailTemplates

            echo '<div id="emailSent"><div>';
            if (isset($emailTimestamps) && (count($emailTimestamps) > 0)){   
                if (isset($emailTimestamps) and $emailTimestamps){
                    echo '<h4>Emails sent to this patient:</h4>';
                    if (count($emailTemplates) > 0) {
                        echo '<dl style="margin-left: 20px">';
                        foreach ($emailTemplates as $templateName => $emailTemplate){
                            if (isset($emailTimestamps[$templateName])){
                                $friendlyName = $emailTemplate['text']; 
                                echo "<dt>$friendlyName:</dt>";
                                foreach ($emailTimestamps[$templateName]
                                                            as $timestamp){
                                    echo "<dd>$timestamp</dd>";
                                }
                            }
                        }
                        echo '</dl>';
                    } else {
                        echo '<p style="margin-left: 20px">No e-mails have been sent.</p>';
                    }
                }
            } // End of if emailTimestamps 
            echo '</div></div>';
            ?>
            </div>            


<script>

$(document).ready(function(){

    function sendEmail(emailTemplate, emailName) {
        $.ajax ({
            type: "POST",
            url: appRoot + 'patients/sendEmail/<?php echo $patientId ?>.json',
            dataType: 'json',
            async: false,
            data: {
                "data[AppController][AppController_id]" : acidValue,              
                "data[User][email]" : "<?php echo $this->request->data['User']['email'] ?>",
                "data[User][last_name]" : "<?php echo $this->request->data['User']['last_name'] ?>",
                "data[Clinic]":<?php echo json_encode($this->request->data['Clinic']) ?>,
                "data[Appointment][0][datetime]" : "<?php if (isset($this->request->data['Appointment']['0']['datetime'])) echo $this->request->data['Appointment']['0']['datetime']; ?>",
                "data[Patient][emailTemplate]" : emailTemplate,
                "data[Patient][emailName]" : emailName
            },
            success: function () {
                // If successful show msg and clear template select.
                $("#sendEmailAlert").fadeOut('fast', function(){
                    $("#displayEmail").text("E-mail has been sent.");
                    $("#sendEmailAlert").fadeIn("slow");
                })
                $('#PatientEmailTemplate').val('');
                $('#sendEmail').attr('disabled', true).addClass('disabled');
                //console.log('sent');
                $('#emailSent').load("edit #emailSent div");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var responseTxt = jQuery.parseJSON(jqXHR.responseText);
                formErrorAlert('Sorry, there was a problem sending this e-mail: ' + responseTxt.message);
            }
        });
    }
    // Click to trigger send email, with template defined
    $("#sendEmail").on('click', function(){
        var emailTemplate = $(this).attr("data-template");
        var emailName = $(this).attr("data-template-name");
        sendEmail(emailTemplate, emailName);
        return false;
    })
    // When choosing email, confirmation message appears and submit is enabled
    $('#PatientEmailTemplate').change(function(){
        var templateVal = $(this).val();
        var templateName = $('#PatientEmailTemplate option:selected').html();
        var userEmail = $('#UserEmail').val();
        if (templateVal != '') {
            if (userEmail != '') {
                $("#displayEmail").text("A \"" + templateName + "\" e-mail will be sent to " +userEmail+". Click the button below to send.");
                $("#sendEmailAlert").fadeIn();
                $('#sendEmail').removeAttr('disabled').removeClass('disabled').attr("data-template", templateVal).attr("data-template-name", templateName).val('Send Email Now');
            } else {
                $("#noEmailAlert").fadeIn();
            }
        } else {
            $("#noEmailAlert").fadeOut();
            $("#sendEmailAlert").fadeOut();
            $('#sendEmail').attr('disabled', true).addClass('disabled');
        }
    });

});
</script>
