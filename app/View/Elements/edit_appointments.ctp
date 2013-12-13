<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

        
        <h3>Appointments 
            <button class="btn btn-mini minimize-section" data-hide=".appt-edit"><i class="icon-chevron-up"></i> Hide</button>
        </h3>

    <?php   
    $appointmentLimit = Configure::read('appointmentLimit');
    $numAppointmentsToDisplay = $appointmentLimit;
    if ($appointmentLimit == null){
        $numAppointmentsToDisplay = sizeof($this->request->data["Appointment"]);
    }
    $allowCreateNew = true;  
    $tester = $this->request->data['Patient']['test_flag'];
    $prevApptId = null;
    $apptCount = 0;   
 
    for ($i=0; $i<$numAppointmentsToDisplay; $i++){
        if (array_key_exists($i, $this->request->data['Appointment'])){
            $apptCount = ($i+1);
            $appointment = $this->request->data['Appointment'][$i];
            $appointmentId = $appointment['id'];
            $session_started = $appointment['session_started']; 
            ?>
            <div class="well admin-edit-section appt-edit" id="apptEdit<?= $i?>">
                <legend><strong>Appointment <?= $apptCount?></strong>&nbsp;
                <button class="btn btn-small edit-btn" name="apptEdit<?= $i?>" data-edit-mode="view">Edit</button>
                </legend>
                <?php
                echo $this->Form->create('Appt', array(
                    'class' => 'form-horizontal form-condensed admin-edit-form',
                    'id' => 'apptEdit'.$i,
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
                echo $this->Form->hidden('Appointment.' . $i . '.id',  
                                    array('value' => $appointment['id']));
                ?>
                <div class="control-group">
                    <label for="Appointment<?= $i ?>Location" class="control-label">Date</label>
                    <div class="controls">
                    <?php
                    // If session is editable, then create and hide edit form
                    //if ($session_started == 0 || $tester){           
                    $timestamp = strtotime($appointment['datetime']);
                    // if a previous appt has an unset datetime, don't allow creation of a new one
                    if (empty($timestamp)) $allowCreateNew = false;
                    // if we don't check for empty, empty datetimes turn into 1969-12-31
                    $dateDefault = empty($timestamp) ? '' :
                                               date('Y-m-d', $timestamp);
                    $hourDefault = empty($timestamp) ? '' :
                                    date('H', $timestamp);
                    $minDefault = empty($timestamp) ? '' :
                                   date('i', $timestamp);
                    echo $this->Form->text("Appointment." . $i . ".date",
                           array('default' => $dateDefault, 
                                  'id' => "AppointmentDate" . $i . "Date",
                                  'class' => 'datep',
                                  'style' => 'width: 80px',
                                  'label' => 'Appointment ' . $i
                    ));
                    // T1 hour/minutes cannot be false, but others can
                    if ($i == 0) {
                      echo $this->Form->hour("AppointmentDate." . $i . ".hour", true, 
                                       array('value' => $hourDefault,
                                              'style' => 'margin-left: 10px; width: auto',
                                              'empty' => false));
                      echo $this->Form->minute("AppointmentDate." . $i . ".minute",
                                             array('value' => $minDefault, 'style' => 'margin-left: 5px; width: auto',
                                                  'interval' => '5', 'empty' => false));
                    } else {
                      echo $this->Form->hour("AppointmentDate." . $i . ".hour", true, 
                                    array('value' => $hourDefault, 'style' => 'margin-left: 5px; width: auto'), 'hh');
                      echo $this->Form->minute("AppointmentDate." . $i . ".minute", 
                                           array('value' => $minDefault, 'style' => 'margin-left: 5px; width: auto',
                                                  'interval' => '5'), 'mm');
                    }
                    ?>
                    </div>
                </div>
                
                    <?php
                    if ($session_started == 1 && $tester == 1) {
                      echo "<span class='help-block' style='margin-bottom: 8px'><small>Note: Appointment ".$apptCount." survey session exists, but since this is a test patient you can change the appointment date/time (this will change the date/time of the appointment's survey session)";
                      if (array_key_exists('medday_exists', $appointment) &&
                                            $appointment['medday_exists'] == 1){ 
                            echo " and medications"; 
                      }
                      echo ".</small></span>";
                    }

                echo $this->Form->input("Appointment." . $i . ".location");
                echo '<div class="control-group">';
                echo '<label for="Appointment' . $i . 'StaffId" class="control-label">Staff</label>';
                echo '<div class="controls">';
                echo $this->Form->select("Appointment." . $i . ".staff_id", $staffs,
                               //$this->request->data["Appointment." . $i . ".staff_id"]);
                               array('value' => $this->request->data["Appointment"][$i]["staff_id"], 'class' => 'span2'));
                echo '</div></div>';
                ?>
                <div class="clearfix control-group">
                    <label class="control-label">Assessment Status:</label>
                    <div class="controls">
                        <span class="uneditable-input">
                        <? if ($session_started == 0) {
                            echo 'Not started';
                        } else {
                            if ($appointment["session_finished"]) {
                                echo '<span class="text-success">Completed</span>';
                            } else {
                                echo 'In progress';
                            }
                        } ?>
                        </span>
                    </div>
                </div>
                <?php 
                if (array_key_exists('medday_exists', $appointment)) {
                ?>
                <div class="clearfix control-group">
                    <label class="control-label">Medications Entered?</label>
                    <div class="controls">
                        <span class="uneditable-input">
                        <?php echo ($appointment["medday_exists"] ? "Yes" : "No");?>
                        </span>
                    </div>
                </div>
                <?php 
                }
                // Removing session-based reports for now
                /*
                if ($appointment['session_finished']){
                ?>
                <div class="clearfix">
                    <label class="control-label">Print patient report:</label>
                    <div class="controls">
                        <span class="uneditable-input">
                        <?php
                        echo $this->Html->link('PDF', 
                            '/medical_records/clinic_report_pdf/' . 
                            $this->request->data['User']['id'] 
                            . "/" . $appointment['project_id'] 
                            . "/" . $appointment['id'] 
                            . "/$prevApptId"); 
                        echo ' | ';
                        echo $this->Html->link('HTML', 
                            "/medical_records/clinic_report/$patientId/" . $appointment['id'] . 
                            "/$prevApptId");
                        ?>
                        </span>
                    </div>
                </div>
                <?php
                } // ($appt['session_finished']){
                */
                if (!empty($this->request->data['Patient']['test_flag'])) {
                ?>
                <hr style="margin: 10px 0"/>
                <div>Dashboard:
                    <?php
                    if ($appointment['session_started'] == 1) {
                        echo $this->Html->link('Test script (max)',
                                    "/surveys/generate_se_test/$patientId/" . 
                                $appointment['id'] . "/max") .
                            ' | ' .
                            $this->Html->link('Test script (min)',
                                    "/surveys/generate_se_test/$patientId/" . 
                                $appointment['id'] . "/min");
                    }
                    else echo "In order to generate a test script for this appointment, first launch an assessment/session for it.";
                    echo ' <br/> ';
                    if (isset($appt['session_id'])){
                        $patient_survey_data_element = 'patient_survey_data_' . INSTANCE_ID;
                        if (file_exists(
                                APP . 'View' . DS . 'Elements' . DS . DS . $patient_survey_data_element . '.ctp')){
                            echo $this->element($patient_survey_data_element, 
                                            array('patientId' => $patientId, 
                                                'sessionId' => $appointment['session_id']));
                        }
                        echo ' <br/> ';
                        if ($appointment["session_finished"]){
                            echo $this->Html->link(
                                "Re-open appointment $apptCount's survey session",
                                "/surveys/reopen_test_session/" . $appointment['session_id']); 
                        }
                        else {
                            echo $this->Html->link(
                                "Close/finish appointment $apptCount's survey session",
                                "/surveys/finish_test_session/" . $appointment['session_id']); 
                        }
                    }
                    ?>    
                </div>
                <?php
                }// if (!empty($this->request->data['Patient']['test_flag'])) {             
        $prevApptId = $appointment['id'];
        ?>
            </div>
        <?php
        echo $this->Form->end();
      } //if (array_key_exists($i, $this->request->data['Appointment'])){
    } // for ($i=0; $i<$numAppointmentsToDisplay; $i++){
 ?>

        <?php
// Show create appointment if criteria are met
if ((Configure::read('appointmentLimit') == null
    || Configure::read('appointmentLimit') > $apptCount) 
    && ($this->request->data['Patient']['consent_status'] != Patient::OFF_PROJECT)){
?>

<button class="btn add-element-btn" id="addApptBtn">Add New Appointment</button>
<?php
echo $this->Form->create('addAppt', array(
    'class' => 'form-horizontal form-condensed hide',
    'action' => 'edit',
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
?>
    <table class="table table-bordered table-condensed" id="apptNewTable">
        <tbody>
            <tr>
                <td>
                    <div class="control-group">
                        <label for="AppointmentNewDate" class="control-label"><strong>Date/Time</strong></label>
                        <div class="controls">
                            <?php
                            echo $this->Form->text("Appointment.new.date",
                                array('id' => "AppointmentNewDate",
                                    'data-field' => 'date',
                                    'class' => 'appt-item datep hasDatePicker',
                                    'style' => 'width: 74px',
                                    'label' => 'New Appointment'
                                ));
                            echo $this->Form->hour("Appointment.new", true, 
                                array('class' => 'appt-item', 'data-field' => 'hour', 'style' => 'margin-left: 5px; width: auto'), 'hh');
                            echo $this->Form->minute("Appointment.new", 
                                 array('class' => 'appt-item', 'data-field' => 'minute',  'style' => 'margin-left: 5px; width: auto',
                                        'interval' => '5'), 'mm');
                            ?>
                        </div>
                    </div>
                    <?php 
                    echo $this->Form->input("Appointment.new.location", array("class" => "span2"));
                    echo '<div class="control-group">';
                    echo '<label for="AppointmentNewStaffId" class="control-label">Staff</label>';
                    echo '<div class="controls">';
                    echo $this->Form->select("Appointment.new.staff_id", $staffs, array('class' => 'appt-item span2', 'data-field' => 'staffid'));
                    echo '</div></div>';
                    echo '<div class="controls">';
                    echo '<div class="control-group">';
                    echo $this->Form->submit("Add Appointment", array(
                        "class"=>"btn btn-primary",
                        "div"=>false,
                        "id"=>"submitNewAppt",
                        "staff_id"=>"",
                        "location"=>""
                    ));
                    echo $this->Form->button("Cancel", array(
                        "class"=>"btn btn-small cancel-element-btn",
                        "style"=>array('margin-left:10px'),
                        "div"=>false,
                        "id"=>"cancelNewAppt"
                    ));
                    echo '</div></div>';
                    ?>

                </td>
            </tr>                
        </tbody>
    </table>
<?php
echo $this->Form->end();
} //if (Configure::read('appointmentLimit') == null){
?>

<script>
 
    function verifyApptDate() {
        if ($('#AppointmentNewDate').val() != '' && $('#AppointmentNewHour').val() != '' && $('#AppointmentNewMin').val() != '') {
            return true;
        } else {
            return false
        }
    }
    // Functions for adding new appointment inline
    // These add attributes to the submit button
    $("#apptNewTable .appt-item").change(function(){
       var newValue = $(this).val();
       var field = $(this).attr('data-field');
       $("#submitNewAppt").attr(field, newValue);
    });
    // Add appointment inline via ajax
    function setAppt(apptDate, locationAppt, staffId) {
        $.ajax ({
            type: "POST", // since we're creating a new record
            url: appRoot + 'appointments/add.json',
            dataType: 'json',
            async: false,
            data: {
                "data[AppController][AppController_id]" : acidValue,
                "data[Appointment][patient_id]" : "<?php echo $patientId ?>",
                "data[Appointment][datetime]" : apptDate,
                "data[Appointment][location]" : locationAppt,
                "data[Appointment][staff_id]" : staffId
            },
            success: function () {
                location.reload(true);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var responseTxt = jQuery.parseJSON(jqXHR.responseText);
                formErrorAlert('Sorry, this appointment can\'t be created: ' + responseTxt.message);
                //alert(textStatus);
                //alert(errorThrown);
            }
        });
    }
    
    // Use datepicker and limit to today or future dates
    $('#addApptEditForm input.datep').datepicker({dateFormat: 'mm/dd/yy', minDate: 0 });
    // Submit the new appointment  
    $('#submitNewAppt').on('click', function(){
        if (verifyApptDate()) {
            var apptDate = $(this).attr('date')+" "+$(this).attr('hour')+":"+$(this).attr('minute')+":00";
            var locationAppt = $('#AppointmentNewLocation').val();
            var staffId = $(this).attr('staffid');
            setAppt(apptDate, locationAppt, staffId);  
        } else {
            formErrorAlert('New appointments must have a date and time.');
        }        
        return false; 
    });

</script> 
