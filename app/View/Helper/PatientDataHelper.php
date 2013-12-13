<?
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class PatientDataHelper extends Helper {

var $helpers = array('Form');

var $user_types;
var $clinical_services;


/**
 *
 */
function __construct($options = null){ 

    parent::__construct($options);
    
//    $this->log(__CLASS__ . "." . __FUNCTION__ . "(); arg:" . print_r(func_get_args(), true), LOG_DEBUG);

    foreach($options as $name => $var){

        // eg $this->user_types = $options['user_types'];
        $this->{$name} = $var;
    }

    //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); user_types:" . print_r($this->user_types, true), LOG_DEBUG);
}


/**
 *
 */
function echoFields($fieldsInOrder, $forEdit = false){
    // Flip the fields if we're not producing forms to edit
    // $fieldsInOrder doesn't contain isRequired information if echoFields isn't forEdit
    if (!$forEdit)
        $fieldsInOrder = array_flip($fieldsInOrder);
    foreach($fieldsInOrder as $classAndField => $isRequired){
        // $classAndField eg User.first_name
        $pieces = explode('.', $classAndField);
        $model = $pieces[0];
        $field = $pieces[1];

        $editFxnNameMod = '';
        if ($forEdit) $editFxnNameMod = '_edit';

        $echoFxnName = "echo_" . $model . "_" . $field . $editFxnNameMod;

        if (method_exists($this, $echoFxnName)){
            // eg echo_Patient_test_flag
            $this->{$echoFxnName}($isRequired);
        }
        else {
            if ($forEdit)
                $this->echoMagicField_edit($model, $field, $isRequired);
            else
                $this->echoMagicField($model, $field);
        }

    }
}

function echoFieldsEdit($fieldsInOrder, $forEdit = false){
    foreach($fieldsInOrder as $classAndField){
        // $classAndField eg User.first_name
        $pieces = explode('.', $classAndField);
        $model = $pieces[0];
        $field = $pieces[1];

        $editFxnNameMod = '';
        if ($forEdit) $editFxnNameMod = '_edit';

        $echoFxnName = "echo_" . $model . "_" . $field . $editFxnNameMod;

        if (method_exists($this, $echoFxnName)){
            // eg echo_Patient_test_flag
            $this->{$echoFxnName}();
        }
        else {
            $this->{'echoMagicFieldEdit' . $editFxnNameMod}($model, $field);
        }

    }
}

/**
 * First set of functions are for patients/edit
 */

function echoMagicField($model, $field, $name = null){

    if (!isset($name)) $name = Inflector::humanize($field);

    echo "$name";
    //echo "view->data:" . print_r($view->data, true);
    //$view->log("echoMagicField, here's data: " . print_r($view->data, true));
    echo $this->request->data[$model][$field];
}

function echo_Patient_test_flag(){

    echo "Tester?";
    echo $this->Form->input('Patient.test_flag', array('label' => '',
                                                'disabled' => 'true'));
}

function echo_Patient_user_type(){
    echo "User Type";
    echo $this->request->data['Patient']['user_type']; 
}

function echo_User_clinic_id(){
    $this->echoMagicField('Clinic', 'name', 'Clinic');    
}



/**
 * Next set of functions are for patients/edit and patients/add
 */
function echoMagicField_edit($model, $field, $isRequired=false){
    $name = Inflector::humanize($field);
    echo $this->Form->input($model . "." . $field, array('required'=>$isRequired));
}

function echoMagicFieldEdit_edit($model, $field){
    $name = Inflector::humanize($field);
    echo $this->Form->input($model . "." . $field);
}

function echo_Patient_user_type_edit(){
//if ($typeUpdatable){
    echo "<div class='control-group'>";
    echo "<label for='PatientUserType' class='control-label'>User Type</label>";
    echo "<div class='controls'>";
    echo $this->Form->select('Patient.user_type', $this->user_types,
                           array('value' => $this->request->data['Patient']['user_type']));
    echo "</div></div>";
//}
}

// Uses jQueryUI datepicker used to set range when calling 'data-date-past'
function echo_Patient_birthdate_edit($required = true){
    echo '<div class="control-group">';
    echo '<label for="PatientBirthdate" class="control-label">Birthdate</label>';
    echo '<div class="controls">';
    echo $this->Form->text('Patient.birthdate', array(
        'class' => 'datep span2',
        // Add attribute to allow for choosing dates in past
        'data-date-past' => 'birthday',
        'size' => 10,
        'required' => $required,
        'placeholder' => 'MM/DD/YYYY',
        'maxlength' => 10));
    echo '</div>';
    echo '</div>';
}


function echo_Patient_gender_edit($required=true){
    $attributes = array();
    if (array_key_exists('Patient', $this->request->data))
        $attributes['value'] = $this->request->data['Patient']['gender'];
    echo $this->Form->input('Patient.gender', array(
        'options' => array(
            'female' => 'female',
            'male' => 'male'
        ),
        'empty' => 'Choose...',
        'required' => $required
    ), $attributes);
}

function echo_Patient_wtp_status_edit($required=false){
    $attributes = array();

    if (array_key_exists('Patient', $this->request->data))
        $attributes['value'] = $this->request->data['PatientExtension']['wtp_status'];

    echo $this->Form->input('Patient.wtp_status', array(
        'options' => array(
            'Completed' => 'Completed',
            'Unable to reach' => 'Unable to reach',
            'Patient refused' => 'Patient refused',
        ),
        'empty' => 'Choose...',
        'required' => $required
    ), $attributes);
}

function echo_Patient_test_flag_edit($required=false){
    echo '<div class="control-group">';
    echo '<label for="PatientTestFlag" class="control-label">Test Flag</label>';
    echo '<div class="controls">';
    echo $this->Form->checkbox('Patient.test_flag', array(
        'class' => '',
        'required' => $required));
    echo '</div>';
    echo '</div>';
}

function echo_Patient_clinical_service_edit(){
    echo '<div class="control-group">';
    echo '<label for="PatientClinicalService" class="control-label">
          Treatment Start Date</label>';
    echo '<div class="controls">';
        echo "<label for = \"PatientClinicalService\">Clinical Service</label>";
        echo $this->Form->select('Patient.clinical_service', 
                            $this->clinical_services,
                            array('empty' => false, 
                                'value' => 
                                    $this->request->data['Patient']['clinical_service']));
    echo "</div></div>";
}

function echo_Patient_treatment_start_date_edit(){
    echo '<div class="control-group">';
    echo '<label for="PatientTreatmentStartDate" class="control-label">
          Treatment Start Date</label>';
    echo '<div class="controls">';
    echo $this->Form->text('Patient.treatment_start_date', array(
                         'class' => 'datep date',
                         'size' => 10,
                     'maxlength' => 10));
    echo "</div></div>";
}

    function echo_Appointment_0_edit($required=true) {
        echo '<div class="control-group">';
        echo '<label for="AppointmentDate" class="control-label">First Appointment</label>';
        echo '<div class="controls">';
        echo $this->Form->text('Appointment.0.date', array(
            //'default' => date('Y-m-d'),//fixme
            'id' => 'AppointmentDate',
            'class' => 'datep date',
            'style' => 'width: 100px',
            'required' => $required,
            'size' => 10,
            'maxlength' => 10,
            'placeholder' => 'MM/DD/YYYY'
        ));
        echo $this->Form->hour('Appointment.0.hour', true, array(
            'required' =>$required,
            'style' => 'width: auto; margin-left: 10px'), 'hh'
        );
        echo $this->Form->minute('Appointment.0.minute', array(
            'interval' => '5', 
            'style' => 'width: auto; margin-left: 10px',
            'required' => $required,
            ), 'mm'
        );
        echo "</div></div>";
    }

    function echo_Patient_check_again_date_edit($required=false) {
        echo '<div class="control-group">';
        echo '<label for="PatientCheckAgainDate" class="control-label">Check Again Date</label>';
        echo '<div class="controls">';
        echo $this->Form->text('Patient.check_again_date', array(
            //'default' => date('Y-m-d'),
            'id' => 'PatientCheckAgainDate',
            'class' => 'datep date',
            'style' => 'width: 100px',
            'required' => $required,
            'size' => 10,
            'maxlength' => 10,
            'placeholder' => 'MM/DD/YYYY'
        ));
        echo "</div></div>";
    }

}
?>
