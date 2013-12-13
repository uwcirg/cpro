<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
?>

<legend>1 Week Follow-up</legend>
    
<?php
$attributes = array();
if (array_key_exists('Patient', $this->request->data)){
    $attributes['value'] = $this->request->data['PatientExtension']['wtp_status'];


    // Show if 1-week assessment is available
    if (isset($this->request->data['Appointment'][0])){

        $windowOpen = new DateTime(
            $this->request->data['Appointment'][0]['datetime'],
            new DateTimeZone('GMT')
        );
        $windowOpen->setTimeZone(new DateTimeZone($timezone));
        $windowClose = clone $windowOpen;

        $windowOpen->add(new DateInterval('P'. (7-1) .'D'));
        $windowClose->add(new DateInterval('P'. (7+7) .'D'));
        echo '<p>Date range available to patient: ';
        echo $windowOpen->format('m/j/Y');
        echo '-';
        echo $windowClose->format('m/j/Y').'</p>';
    }

}

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



echo $this->Form->input('Patient.wtp_status', array(
    'options' => array(
        'Completed' => 'Completed',
        'Unable to reach' => 'Unable to reach',
        'Patient refused' => 'Patient refused',
    ),
    'empty' => 'Choose...',
    // 'required' => $required
), $attributes);

echo $this->Form->hidden(
    AppController::CAKEID_KEY,
    array('value' => $this->Session->read(AppController::ID_KEY))
);


echo $this->Form->end();
?>

