<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<?php

// default
$patientEditElements = array(
    'patient', 'study', 'notes', 'language', 'appointments', 'check_again');

if (Configure::check('PATIENT_EDIT_ELEMENTS')){
    $patientEditElements = Configure::read('PATIENT_EDIT_ELEMENTS');
}

?>

<div class="span2">
<?php echo $this->element('quick_links_admin_tab',
                    array("quick_links" => $quick_links)); ?>
</div>

<div class="span10">

<h2>
Patient # 
<?php 
//$patientId = $this->request->data['User']['id'];
    echo $patientId;
if ($this->request->data['User']['first_name'])
    echo " - {$this->request->data['User']['first_name']} {$this->request->data['User']['last_name']}";
?>
</h2>

<?php
echo $this->element('patient_tools_links');//, array('patientId' => $this->request->data['User']['id']));
?>

<div class="row" style="margin-top: -10px">
    <div class="span5">
        
<?php
if (in_array('patient', $patientEditElements)){ 
?>
    <h3>Patient Information
        <button class="btn btn-small edit-btn" name="patientInfoEdit" id="patientInfoBtn" data-edit-mode="view">Edit</button>
        <button class="btn btn-mini minimize-section" data-hide="#patientInfoEdit"><i class="icon-chevron-up"></i> Hide</button>
    </h3>
    
    <div id="patientInfoEdit" class="well admin-edit-section disable-section">
    <?php
    echo $this->Form->create('Patient', array(
        'class' => 'form-horizontal form-condensed admin-edit-form',
        'default' => false, // disable submit
        //'action' => 'edit',
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
    
    $this->PatientData->echoFieldsEdit($fieldsInOrder, true);

    if (defined('PATIENT_SELF_REGISTRATION') and PATIENT_SELF_REGISTRATION){
        echo '<div class="control-group">';
        echo "<label class='control-label'>Registration Status:</label>";
        echo "<div class='controls'><span class='uneditable-input'>";
        echo $registrationStatus;
        echo '</span></div></div>'; 
    }       

    echo $this->Form->hidden(AppController::CAKEID_KEY,
                        array(
		            'value' => $this->Session->read(AppController::ID_KEY)
		        ));
    echo $this->Form->end();
    ?>
    </div>
<?php
} // if in_array('patient', $patientEditElements){ 

if (in_array('study', $patientEditElements) 
    && defined('STUDY_SYSTEM') && STUDY_SYSTEM){
?>
        <h3>Study Information 
            <button class="btn btn-small edit-btn" name="studyInfoEdit" data-edit-mode="view">Edit</button>
            <button class="btn btn-mini minimize-section" data-hide="#studyInfoEdit"><i class="icon-chevron-up"></i> Hide</button>
        </h3>
        <div id="studyInfoEdit" class="well admin-edit-section disable-section">
        <?php
        echo $this->Form->create('Study', array(
            'class' => 'form-horizontal form-condensed admin-edit-form',
            'default' => false, // disable submit
            //'action' => 'edit',
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
        echo "<div class='control-group'>";
        echo "<label for='PatientConsentStatus' class='control-label'>Consent Status</label>";
        echo "<div class='controls'>";
        echo $this->Form->select('Patient.consent_status', 
                                $consent_statuses, 
	                   array('value' => $this->request->data['Patient']['consent_status'], 'class' => 'span2', 'empty' => false));
        /**echo "<span class='help-block'>";
        $consentOptionsLabel = "Changing the status to 'consented' will irrevocably put this patient in the participant group";
        if (in_array(Patient::OFF_PROJECT, $consent_statuses)){
            $consentOptionsLabel = "Changing the status to '" . 
                Patient::OFF_PROJECT . 
                "' will remove the patient from the project";
        }
        echo $consentOptionsLabel;
        echo "</span>";*/
        echo "</div></div>";
        
        echo '<div class="control-group">';
        echo '<label for="PatientConsentDate" class="control-label">Consent Date</label>';
        echo '<div class="controls">';
        echo $this->Form->text('Patient.consent_date', array(
            'class' => 'datep span2',
            'data-date-past' => true,
            'size' => 10,
            'maxlength' => 10));
        echo '</div></div>';

        echo '<div class="control-group">';
        echo '<label for="PatientConsenterId" class="control-label">Consented by</label>';
        echo '<div class="controls">';
        echo $this->Form->select("Patient.consenter_id", $staffs, array('class' => 'span2'));
        echo '</div></div>';

        echo $this->Form->input('Patient.consent_checked', array('class' => ''));
        
        $studyGroupEditable = null;
        if (defined('MANUAL_RANDOMIZATION') && MANUAL_RANDOMIZATION
            && !$this->request->data['Patient']['study_group']){
            $studyGroupEditable = true;
            echo '<div class="control-group">';
            echo "<label for='PatientStudyGroup' class='control-label'>Patient Study Group</label>";
            echo "<div class='controls'>";
            echo $this->Form->select(
                'Patient.study_group', 
                array(
                    Patient::TREATMENT=>Patient::TREATMENT, 
                    Patient::CONTROL=>Patient::CONTROL
                ),
                array('value' => $this->request->data['Patient']['study_group'])
            );
            echo '</div></div>';
        }
        echo '<div class="control-group">';
        echo "<label for='PatientOffStudyStatus' class='control-label'>Off-Study Status</label>";
        echo "<div class='controls'>";
        echo $this->Form->select('Patient.off_study_status', $offStudyStatuses, 
                           array('value' => $this->request->data['Patient']['off_study_status'], 'class' => 'span2'));
        echo '</div></div>';
        echo $this->Form->input('Patient.off_study_reason');

        // Uneditable fields that show status
        echo '<hr style="margin-bottom: 10px">';
        // Study Group dispalyed here if not editable
        if (($centralSupport or $researchStaff) and !$studyGroupEditable){
            echo '<div class="control-group">';
            echo "<label for='PatientStudyGroup' class='control-label'>Patient Study Group:</label>";
            echo "<div class='controls'><span class='uneditable-input'";
            echo " title='Patient Study Group: ". $this->data['Patient']['study_group'] ."'>";
            echo $this->request->data['Patient']['study_group'];
            echo '</span></div></div>';
        
        }

        if (defined('ELIGIBILITY_WORKFLOW') && ELIGIBILITY_WORKFLOW){ 
            echo '<div class="control-group">';
            echo "<label class='control-label'>Eligibility:</label>";
            echo "<div class='controls'><span class='uneditable-input'>";
            if ($this->request->data['Patient']['eligible_flag'] == '1') {
                echo "Eligible";
            } elseif ($this->request->data['Patient']['eligible_flag'] == '0') {
                echo "Ineligible";
            } else {
                echo "To be determined";
            }
            echo '</span></div></div>';
        }

        if (in_array('p3p_teaching', Configure::read('modelsInstallSpecific'))){
            // Intervention status - only show if test patient
            if ($tester = $this->request->data['Patient']['test_flag']) {
                echo '<div class="control-group">';
                echo "<label class='control-label'>Intervention Status:</label>";
                echo "<div class='controls'><span class='uneditable-input'>";
                if (array_key_exists('farthestStepInIntervention', 
                                        $this->request->data['Patient']) 
                    && !empty($this->request->data['Patient']['farthestStepInIntervention'])){
                    echo "Started ";
                    // If test patient, intervention reset allowed    
                    echo "(".$this->Html->link(
                            "Reset intervention",
                            "/patients/reset_step_last_visited/$patientId"). ")"; 
                }
                else echo "Not yet started";
                echo '</span></div></div>';
            }
        }// if (in_array('p3p_teaching'...

        echo $this->Form->end(); ?>
        </div>
<?php 
}// if (in_array('study', $patientEditElements) && defined('STUDY_SYSTEM') && STUDY_SYSTEM){
?>        
    </div>
    <div class="span5">

<?php
if (
    in_array('anonymous_access', $patientEditElements) and
    $this->request->data['Patient']['consent_status'] != 'consented' and
    $this->request->data['Patient']['eligible_flag'] == 1 and
    !$this->request->data['User']['last_name']
){
    echo $this->element('edit_anonymous_access');
}

// Display language toggle if used in this instance
if (in_array('locale_selections',
    Configure::read('modelsInstallSpecific'))){
        echo $this->element('edit_locale'/**, array('patientId' => $patientId)*/);
} // End language toggle
           
// Email Reminder Section
if ( (isset($emailTemplates) && count($emailTemplates) > 0) || (isset($emailTimestamps) && count($emailTimestamps) > 0) ){
    echo $this->element('edit_emails'); 
} // End Email Reminder Section


if (in_array('appointments', $patientEditElements)){
    echo $this->InstanceSpecifics->echo_instance_specific_elem('edit_appointments');
}

if (in_array('check_again', $patientEditElements)){
    echo $this->element('edit_check_again'); 
}

if (in_array('1_wk_fu', $patientEditElements) || in_array('mo_fu', $patientEditElements) || in_array('6_mo_fu', $patientEditElements)){
    echo $this->element('edit_fu'); 
}

if (isset($currentWindow) or isset($nextWindow)):?>
<h3>Survey Session Windows</h3>
<div id="survey_windows" class="well admin-edit-section">
    <dl>
        <?php if (isset($currentWindow)): ?>
        <dt>Current</dt>
        <?php
        $format = 'n/j';
        if ($currentWindow['start']->format('y') != $currentWindow['stop']->format('y'))
            $format .= '/y';
        printf(
            '<dd>%s-%s</dd>',
            $currentWindow['start']->format($format),
            $currentWindow['stop']->format($format)
        );
        endif
        ?>

        <?php if (isset($nextWindow)): ?>
        <dt>Next</dt>
        <?php
        $format = 'n/j';
        if ($nextWindow['start']->format('y') != $nextWindow['stop']->format('y'))
            $format .= '/y';
        printf(
            '<dd>%s-%s</dd>',
            $nextWindow['start']->format($format),
            $nextWindow['stop']->format($format)
        );
        endif
        ?>
    </dl>
</div>
<?php endif ?>

    </div>
</div>

<div class="row">
    <div class="span10">

        <?php
        echo $this->element('notes', array(
        'authUser' => $authUser,
        'timezone' => $timezone,
        'baseUrl' => $this->Html->url('/patients/'),
        'staffs' => $staffs,
        'notes' => $notes,
        'patientViewNotes' => $patientViewNotes,
        'editPatientViewNotes' => PATIENT_NOTES,
        'showPatientViewNotes' => PATIENT_NOTES));
        ?>
    </div>    
</div>


</div> <?php // End main span10 ?>

<div id="formErrorInfo"  class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Patient Edit Error</h3>
  </div>
  <div class="modal-body">
      <br />
      <div id="formErrorText"></div>
      <br />
      <p>If you continue to have trouble, please contact a system admin.</p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<script>

function PatientConsentStatus_StatusReaction(){
    // consent_date enabled if consent_status not 'usual care' or 'pre-consent'
    //alert('PatientConsentStatus_StatusReaction() called');
    var val = $('#PatientConsentStatus').val();
    if (val == '<?=Patient::ELEMENTS_OF_CONSENT;?>' ||
            val == '<?=Patient::PRECONSENT;?>'){
        $('#PatientConsentDate').prop('disabled', true); 
        $('#PatientConsenterId').prop('disabled', true); 
        $('#PatientConsentChecked').prop('disabled', true); 
        $('#PatientOffStudyStatus').prop('disabled', true); 
    }
    else {
        $('#PatientConsentDate').prop('disabled', false); 
        $('#PatientConsenterId').prop('disabled', false); 
        $('#PatientConsentChecked').prop('disabled', false); 
        $('#PatientOffStudyStatus').prop('disabled', false); 
    }
}

function PatientOffStudyStatus_StatusReaction(){
    // consent_date enabled if consent_status not 'usual care' or 'pre-consent'
    //alert('PatientOffStudyStatus_StatusReaction() called');
    if ($('#PatientOffStudyStatus').val() != ''){
        $('#PatientOffStudyReason').prop('readonly', false)
            .addClass('active-edit');
    }
    else {
        $('#PatientOffStudyReason').prop('readonly', true)
            .removeClass('active-edit')
            .val('');
    }
}

$(document).ready(function(){

/*** Miscellaneous functions ***/
    
    // Generic function for making form appear from add btn. Used by appt and note
    $('.add-element-btn').click(function(){
        $(this).fadeOut('fast', function(){
            $(this).next("form").fadeIn('slow');
        })
    });
    // Generic function for making form disappear via cancel btn.
    $('.cancel-element-btn').click(function(){
        var parentForm = $(this).closest('form');
        $(parentForm).fadeOut('fast', function(){
            // Clears form inputs
            $(parentForm)[0].reset();
            // Shows add button again
            $(parentForm).prev('button').fadeIn();
        })
        return false; 
    });
    
    // Closing any alerts - used mostly for email section
    $('.close').click(function() {
       $('.alert').fadeOut();
       // Revert select back to default and disable send button
       $('#PatientEmailTemplate').val('');
       $('#sendEmail').attr('disabled', true).addClass('disabled');
    });
    
    // Bootstrap modal loads with error message when there's a json failure 
    // reported. Consider using popover instead?
    function formErrorAlert(errorText) {
        $("#formErrorText").html(errorText);
        $("#formErrorInfo").modal('show');
    }

    // initial dependant fields
    $('.admin-edit-form :input').each(function(){
        var fieldId = $(this).attr('id');//eg PatientConsentStatus
        var fn = window[fieldId + '_StatusReaction']; // eg PatientConsentStatus_StatusReaction
        if(typeof fn === 'function') {
            fn();
        }
    });

    validatePatientForm(".admin-edit-form", function() {
        console.log('its time!');
        <?php
        // FIXME - Taken for add. Doesn't work properly here because fields dont'
        // have the required attribute.
        foreach($newFieldsInOrder as $modelField => $reqd){
            //eg '$("#PatientGender").rules("add", { required: true });
            $modelField = explode(".", $modelField);
            $model = $modelField[0];
            $field = Inflector::classify($modelField[1]);

            if ($field == 'AppointmentDate'){// fixme or appt, check again etc
                echo '$("#' . $model . $field . '").rules("add", { date: true});' . "\n" ;
            }
            elseif ($reqd) {
                echo '$("#' . $model . $field . 
                        '").rules("add", { required: ' . $reqd;
                if ($field == 'CheckAgainDate') echo ', date: true';
                echo '});' . "\n" ; 
            } else {
                
            }
        }
        ?>
    });

    // Datepicker for inputs
    $('.admin-edit-form input.datep').each(function(){
        
        var attr = $(this).attr('data-date-past');
        if (attr == 'birthday') {
            // If data-date-past is birthday, then allow change of year and date
            // plus set yearRange. For use with birthdays.
            $(this).datepicker({
                dateFormat: 'mm/dd/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:-20",
                // Calendar will open up to 40 years before today's date
                defaultDate: "-40y"
            });        
        } else if (typeof attr !== 'undefined' && attr !== false) {
            // Else If data-date-past is true, then allow past dates to be selected.
            $(this).datepicker({
                dateFormat: 'mm/dd/yy'
            });
        } else {
            // Default format - limited to current dates
            $(this).datepicker({
                dateFormat: 'mm/dd/yy',
                minDate: 0
            });
        }
    });

    // Give UI indication that form field has been saved with check.
    function showSave(parentField, passType){
        setTimeout(function() {
            // If fieldType is checkbox, then we don't add check (looks weird to
            // have a check next to the checkbox)
            if (passType == 'checkbox') {
                parentField.find('span.help-inline i').removeClass('icon-spinner icon-spin');                
            } else {
                parentField.find('span.help-inline i').removeClass('icon-spinner icon-spin').addClass('icon-ok icon-green');
            }
        }, 500);
    };
    // Clear out any existing save icons on focus
    $('.admin-edit-form input, .admin-edit-form select').focus(function(){
        $(this).parent('div').find('span.help-inline').remove();
    });
    // This is necessary to clear icons if select dropdown is updates before
    // changing focus.
    $('.admin-edit-form select, .admin-edit-form input[type=checkbox]').change(function(){
        $(this).parent('div').find('span.help-inline').remove();
    });
    
    function changeDateToSend(currentDate) {
        var dateFormatArray = currentDate.split("/");
        var convertDate = dateFormatArray[2]+"-"+dateFormatArray[0]+"-"+dateFormatArray[1];
        return convertDate;
    }
    
    // Main function to trigger update to AJAX to database
    $('.admin-edit-form :input').change(function(){
        // name eg data[User][username] 
        var dataToPass = {};
        var fieldname = new String($(this).attr('name'));//eg data[Patient][MRN]
        fieldname = fieldname.valueOf();
        var fieldId = $(this).attr('id');//eg PatientConsentStatus
        var serviceController = 'patients';
        
        // Convert date format to YYYY-MM-DD to send to server
        var altDate;
        if ( $(this).hasClass("datep") ) {
            var dateFormat = $(this).val();
            if (dateFormat && dateFormat != '') {
                altDate = changeDateToSend(dateFormat);
            }
        }


        // For appt data, always need to form the date b/c it's required for
        // submit. Need to update the date for the server even if we're changing 
        // hours or minutes
        if (fieldId.indexOf("Appointment") == 0){
                    
            serviceController = 'appointments';
            var apptIndex = fieldId.match(/\d+/g)[0];
            var apptId = $('#Appointment' + apptIndex + 'Id').val();
            var modDate = $("#AppointmentDate" + apptIndex + "Date").val();
            var apptAltDate = changeDateToSend(modDate);
            dataToPass['data[Appointment][' + apptIndex + '][id]'] = apptId;
            if (fieldId.match(/Date$/) || fieldId.match(/Hour$/) 
                    || fieldId.match(/Min$/)){
                dataToPass['data[Appointment][' + apptIndex + '][datetime]']
                    // these selectors just match the beginning of the id, in order to accomodate our mysterious id's that end in eg HourHour eg Appointment1HourHour  
                    = apptAltDate + " " 
                    +  $("[id^='AppointmentDate" + apptIndex + "Hour']").val() + ":" 
                    +  $("[id^='AppointmentDate" + apptIndex + "Min']").val() 
                    + ":00";
            }
        }
       
        // If fieldType is checkbox, then we won't display the "check" hint next
        // to it on save
        var fieldType = "";
        // If the change field is not related to appt date, then get its value
        if (!fieldId.indexOf("AppointmentDate") == 0){
            var value;
            if ($("#"+fieldId).is(":checkbox")) {
                fieldType = 'checkbox';
                if ($(this).prop('checked')) {
                    value = 1;
                } else {
                    value = 0;
                }
            } else {
                if ( altDate ) {
                    value = altDate;
                } else {
                    value = $(this).val();
                }
            }
            dataToPass[fieldname] = value;
        }
        //console.log(value);
        //console.log ('new text for id ' + fieldId + ', name ' + $(this).attr('name') + ':' + value);
       
        dataToPass['data[Patient][id]'] ='<?php echo $this->request->data['User']['id'];?>';
        dataToPass['data[AppController][AppController_id]'] = '<?= $this->Session->read(AppController::ID_KEY);?>';

        // Visual feedback that it's sending. Will be removed when done.
        var fieldValue = $("#"+fieldId).val();
        var parentField = $("#"+fieldId).parent();
        $(parentField).append('<span class="help-inline"><i class="icon-spinner icon-spin"></i></span>');
        
        var request = $.ajax ({
            type: "POST",
            url: appRoot + serviceController + '/edit.json', // todo users/edit if apropos
            dataType: 'json',
            async: false,
            data: dataToPass
        });

        request.done(function(data, textStatus, jqXHR) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            // Function for UI feedback on success
            showSave(parentField, fieldType);
            // Changes background-color if there's been an email error previously
            $("#"+fieldId).css('background-color', '');
            var fn = window[fieldId + '_StatusReaction']; // eg PatientConsentStatus_StatusReaction
            if(typeof fn === 'function') {
                fn();
            }
            // Put in 'if' b/c of erros with appointment date changes
            if (responseTxt.data) {
                $.each(responseTxt.data, function(index, changes){
                    // adjust other fields that changed because of this save, 
                    // eg when consent_status set to consented, set off_study_status to 'on study'
                    //alert(index + ' and its newValue is: ' + changes.newValue);
                    if (typeof changes['newValue'] != 'undefined'){
                        $('#' + index).val(changes['newValue']); 
                    } 
                });    
            }
            // Add page reload trigger to Done Editing button. Should change to
            // attribute on field
            if (fieldId == 'UserEmail' || fieldId == 'PatientTestFlag') {
                $("#patientInfoBtn").attr("data-reload", true)
            }
            /**
            alert ('<?php
                    echo __('Success - TODO include any directives for changing enabled state or other attributes. Heres responseTxt.data: ');
                    ?>' + responseTxt.data);*/
        });


 
        request.fail(function(jqXHR, textStatus, errorThrown) {   
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            var alertTxt = '<p><strong>There was a problem with this edit</strong>:</p>' 
                    + '<p style="margin-left: 30px">' + responseTxt.message + '</p>'
            formErrorAlert(alertTxt);
            // Highlights the field in question. Other methods such as adding a
            // class to control-group parent seemed to cause
            // problems with our on-page validation script jquery.validate
            $("#"+fieldId).css('background-color', '#f2dede');
            
        }); 

    // Run instance-specific javascript
    if (typeof patientEditCallback == 'function')
        patientEditCallback(this);

    }); // Main function to trigger update to AJAX to database
 
    // Inline editing of forms fields. Disables all inputs on page load then
    // uses the "Edit" buttons to trigger re-enabling fields in that section
    // :not([disabled]) used so that fields that already are disabled can be 
    // handled differently
    // Note - now using readonly for text inputs to allow copying. Need to 
    // exclude date fields otherwise datepicker will be triggered.
    $(".disable-section, .appt-edit").find("select:not([disabled]), input.datep, input:checkbox").addClass("active-edit").attr("disabled", true);
    $(".disable-section, .appt-edit").find("input:not([readonly]):not(.datep)").addClass("active-edit").attr("readonly", true);
    // When edit button is clicked, enable form fields. When done is clicked,
    // (detected via data-edit-mode) disable them again.
    $('.edit-btn').on('click', function(){
       
        var triggerReload = $(this).attr('data-reload');
        // Trigger page refresh if needed
        if ( typeof triggerReload !== 'undefined' && triggerReload !== false ) {
            location.reload(true);
        } else  { // Otherwise proceed to update section
        
            // If section is hidden, trigger a click on the min/maximize button to
            // show section
            $(this).next('button.minimize-section.section-hidden').click();

            var editMode = $(this).attr('data-edit-mode');
            var findEdit = $(this).attr('name');
            var triggerReload = $(this).attr('data-reload');
            // Handle appointments differently since there can be more than one
            // if there's more than one appointment
            var findEdit2;
            if (findEdit.match("^apptEdit")) {
                findEdit2 = '.appt-edit';
            } else {
                findEdit2 = '#' + findEdit;
            }
            if (editMode == 'view') {
                $(findEdit2).find("select.active-edit, input.active-edit.datep, input.active-edit:checkbox").removeAttr("disabled");
                $(findEdit2).find("input.active-edit:not(.datep)").removeAttr("readonly");
                $(this).text("Done Editing");
                $(this).attr("data-edit-mode","edit");
            } else {
                $(findEdit2).find("select.active-edit, input.active-edit.datep, input.active-edit:checkbox").attr("disabled", true);
                $(findEdit2).find("input.active-edit:not(.datep)").attr("readonly", true);
                // Removes all the checks that mark successful saves
                $(findEdit2).find("span.help-inline").remove();
                $(this).text("Edit");
                $(this).attr("data-edit-mode","view");
            }
        }
        
    }); // Inline editing of forms fields.

}); // ready
</script>
