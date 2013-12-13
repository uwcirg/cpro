<?php
/**
 * 
 * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
 *
 */
?>

<div class="span2">
<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links)); ?>
</div>

<div class="span10">

<h2>Add a new patient</h2>

<?php
// show form to add new patient
echo $this->Form->create('Patient', array(
    'action' => 'add',
    'class' => 'form-horizontal',
    'inputDefaults' => array(
        'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
        'div' => array('class' => 'control-group'),
        'label' => array('class' => 'control-label'),
        'between' => '<div class="controls">',
        'after' => '</div>',
        'class' => '',
        'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline'))
    )
));

if ($patientNotExist) {
    // only shown after we've checked the patient doesn't exist
    echo '<p><span style="font-weight: bold; font-style: italics">Almost done - a username has been created.</span> You may edit it below if you wish. Press the Submit 
          button to finalize the new patient registration.</p>';

    echo $this->Form->input('User.username', array(
        'id' => 'data[User][username]',
        'div' => array('class' => 'control-group info'),
        'label' => array(
            'text' => 'Username',
            'class' => 'control-label'
        ),
        'after'=>'<span class="help-inline">Username has been created. You can edit it.</span></div>',
        'required' => 'true'
    ));
}

$this->PatientData->echoFields($newFieldsInOrder, true);

echo $this->Form->hidden(AppController::CAKEID_KEY, array(
    'value' => $this->Session->read(AppController::ID_KEY)
));

echo '<div class="controls">';
/* show a different form button if we haven't yet checked if the patient
  is unique */
if (!$patientNotExist) {
    echo $this->Form->submit("Submit", array(
        "class"=>"btn btn-large btn-primary", "div" => array(
            "class" => 'control-group'
        )
    ));
} else {
    echo $this->Form->submit("Submit", array(
        "class"=>"btn btn-large btn-primary", "div" => array(
            "class" => 'control-group'
        )
    ));
}
echo '</div>';
echo $this->Form->end();
?>

</div>

<?php 
// To handle some weird validation problems in IE8 (if you refocus on a date 
// field it will call an error even though it's valid), turing off dates validation
// for IE7/8.
//$oldIE = null;
//$oldIE = (ereg('MSIE 7',$_SERVER['HTTP_USER_AGENT']) || ereg('MSIE 8',$_SERVER['HTTP_USER_AGENT'])) ? true : false;
?>
<script>
$(document).ready(function() {
    // After initial validation is called, configurable validation added.
    // Currently specifying whether required (can vary by instance). Other 
    // validation details stored in cpro.jquery.validate
    validatePatientForm("#PatientAddForm", function() {
        <?php
        foreach($newFieldsInOrder as $modelField => $reqd){
            //eg '$("#PatientGender").rules("add", { required: true });
            $modelField = explode(".", $modelField);
            $model = $modelField[0];
            $field = Inflector::classify($modelField[1]);
            //$dateField = ($field == '0' || $field == 'Birthdate' || $field == 'CheckAgainDate') ? true : false;
            if ($reqd) {
                echo '$("#' . $model . $field . '").rules("add", {';
                if ($reqd) echo 'required: true';
                echo "});" . "\n" ;
            }
        }
        ?>
    });
    // Customize help/error message for the appointment date/time group
    $("#AppointmentDate, #Appointment0HourHour, #Appointment0MinuteMin").on('change', function(){
        var newApptDate = $("#AppointmentDate").val();
        var newApptHour = $("#Appointment0HourHour").val();
        var newApptMin = $("#Appointment0MinuteMin").val();
        var apptHelper = $(this).parent().find('span.help-inline');
        if (newApptDate != '' && newApptHour != '' && newApptMin != '') {
            $(apptHelper).text('');
        } else if (newApptDate != '' && (newApptHour == '' || newApptMin == '')) {
            $(apptHelper).text('Select time for appointment');
        } else {
            $(apptHelper).text('Optional');
        }
    });   
    $('#PatientAddForm input.datep').each(function(){
        var attr = $(this).attr('data-date-past');
        if (typeof attr !== 'undefined' && attr !== false) {
            // If data-date-past is true, then allow change of year and date
            // plus set yearRange. For use with birthdays.
            $(this).datepicker({
                dateFormat: 'mm/dd/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:-20",
                // Calendar will open up to 40 years before today's date
                defaultDate: "-40y"
            });
        } else {
            // Default format - limited to current dates
            $(this).datepicker({dateFormat: 'mm/dd/yy', minDate: 0 });
        }
    });
    // Adds optional note to any row where the text or select inputs do not have
    // the required="required" attribute.
    $('#PatientAddForm input:not([required], :hidden, :submit), #PatientAddForm select:not([required])').each(function() {
        $(this).parent().addClass('optional-field');
    });
    $('#PatientAddForm .optional-field').append('<span class="help-inline" style="color: #999">Optional</span>');

    function changeDateToSend(currentDate) {
        var dateFormatArray = currentDate.split("/");
        var convertDate = dateFormatArray[2]+"-"+dateFormatArray[0]+"-"+dateFormatArray[1];
        return convertDate;
    }
    // Change dates to YYYY-MM-DD before sending to server
    $("#PatientAddForm").submit(function( event ) {
        if($(this).valid()) {
            $('input.datep').each(function(){
                var dateFormat = $(this).val();
                if (dateFormat && dateFormat != '') {
                    $(this).val(changeDateToSend(dateFormat));
                }
            });
        };
        
    });
});
</script>
