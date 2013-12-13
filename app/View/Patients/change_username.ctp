<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<script>
$(document).ready(function(){
    validatePatientForm("#UserChangeUsernameForm");
    $('input.datep').datepicker({dateFormat: 'yy-mm-dd'});
    return false;
});
</script>

<div class="span2">
<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links));
?>
</div>

<div class="span10">
    
    <h2>Change basic data for Patient # <?php echo $this->request->data['User']['id']; ?></h2>

    <?php
    echo $this->element('patient_tools_links', array('patientId' => $this->request->data['User']['id']));

    echo $this->Form->create('Patient', array(
        'class' => 'form-horizontal',
        'action' => 'changeUsername',
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

    echo $this->Form->input('User.username');
    echo $this->Form->input('User.first_name');
    echo $this->Form->input('User.last_name');
    echo $this->Form->input('Patient.birthdate', 
                      array(
                          'style' => 'width: auto', 
                          'empty' => TRUE,
                          'minYear' => '1890',
                          'maxYear' => '1992'
                      ));
    echo $this->Form->input('Patient.MRN');
    echo $this->element('editTs', array(
        'ts' => array('T1', 'T2'),
        'startedTs' => $startedTs,
        'tester' => $this->request->data['Patient']['test_flag'],
        'timestamps' => $timestamps,
        'staffs' => array(),
        'onlyEditDatetimes' => true));

    ?>
    <div class="well" style="text-align: center"><?php echo $this->Form->submit("Submit", array("class"=>"btn btn-large btn-primary")); ?> </div>
    <?php 
    echo $this->Form->hidden(AppController::CAKEID_KEY,
                        array(
                            'value' => $this->Session->read(AppController::ID_KEY)
                        ));
    echo $this->Form->end();
    ?>

    
</div>



