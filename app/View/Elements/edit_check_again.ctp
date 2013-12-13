<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<h3>Check Again Status 
    <button class="btn btn-small edit-btn" name="checkAgainEdit" data-edit-mode="view">Edit</button>
    <button class="btn btn-mini minimize-section" data-hide="#checkAgainEdit"><i class="icon-chevron-up"></i> Hide</button>
</h3>

<div class="well admin-edit-section disable-section" id="checkAgainEdit">
            <?php
            echo $this->Form->create('CheckAgain', array(
                'class' => 'form-horizontal form-condensed admin-edit-form',
                'default' => false, // disable submit
                //'action' => 'edit',
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
            echo '<div class="control-group">';
            echo '<label for="PatientCheckAgainDate" class="control-label">Check Again Date</label>';
            echo '<div class="controls">';
            echo $this->Form->text('Patient.check_again_date', array(
                             'class' => 'datep span2',
                             'size' => 10,
                             'maxlength' => 10));
            echo '</div></div>';
            echo $this->Form->input('Patient.no_more_check_agains');

            echo $this->Form->end();
            ?>
</div>


<script>

$(document).ready(function(){

    // Update checkAgainDate based on whether checked
    var $patientNoMoreCheckAgains = $("#PatientNoMoreCheckAgains");
    var $patientCheckAgainDate = $("#PatientCheckAgainDate");   
    function checkAgainUpdate() {
        if ($patientNoMoreCheckAgains.is(':checked')){
            $patientCheckAgainDate.attr({
              value: "",
              disabled: true
            });
        } else {
            $patientCheckAgainDate.removeAttr("disabled");
        }
    }
    checkAgainUpdate();
    $patientNoMoreCheckAgains.click(function(){
        checkAgainUpdate();
    });

}); // ready
    
</script>

