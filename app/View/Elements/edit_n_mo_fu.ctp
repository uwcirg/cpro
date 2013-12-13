<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<!--

    <h3><?php echo $patientProjectsStates[$project]->project['Project']['Title'];?>
        <button class="btn btn-mini minimize-section" data-hide="#mofuProj<?=$project;?>"><i class="icon-chevron-up"></i> Hide</button>
</h3>
    <div id="mofuProj<?=$project;?>" class="well admin-edit-section">
-->
<legend><?php echo $patientProjectsStates[$project]->project['Project']['Title'];?></legend>

<?php
    $patientProjectsStates[$project]->availableDateRangeStart
?>

    <p>Date range available to patient: <?php 
        if ($patientProjectsStates[$project]->availableDateRangeStart) {
            echo "<br />"
            .$patientProjectsStates[$project]->availableDateRangeStart 
            . " - "
            . $patientProjectsStates[$project]->availableDateRangeEnd;
        } else {
            echo "<em>Not set</em>";
        }
    ?></p>

<?php

    $fieldPrefix = $patientProjectsStates[$project]->project['Project']['session_rules_fxn'];

    echo $this->Form->create('Patient' . Inflector::classify($fieldPrefix), array(
        'class' => 'form-horizontal form-condensed admin-edit-form',
        'default' => false, // disable submit
        'inputDefaults' => array(
            'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
            'div' => array('class' => 'control-group'),
            'between' => '<div class="controls">',
            'after' => '</div>',
            'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline'))
        )
    ));

    $wontComplete = $fieldPrefix . '_wont_complete';
    $label = 'Patient won\'t complete';
    echo '<div class="control-group">';
    echo '<label for="Patient' . $wontComplete . '" class="control-label">' . $label . '</label>';
    echo '<div class="controls">';
    echo $this->Form->checkbox('Patient.' . $wontComplete, array(
            'label' => $label));
    echo '</div>';
    echo '</div>';

    $wontCompleteReason = $fieldPrefix . '_wont_complete_reason';
    $label = 'Reason';
    echo '<div id="' . $wontCompleteReason . '">';
    echo '<div class="control-group">';
    echo '<label for="Patient' . $wontCompleteReason . '" class="control-label">' . $label . '</label>';
    echo '<div class="controls">';
    echo $this->Form->text('Patient.' . $wontCompleteReason, array(
        'label' => $label,
        'class' => 'span2'
    ));
    echo '</div>';
    echo '</div>';
    echo '</div>';

    $modePref = $fieldPrefix . '_mode_pref';
    echo '<div id="' . $modePref . '">';
    echo $this->Form->input('Patient.' . $modePref, array(
            'options' => array(
                'online' => 'online',
                'mail' => 'mail'),
            'class' => 'span2',
            'label' => array(
                'class' => 'control-label',
                'text' => 'Mode Preference'),
            'empty' => 'Choose...',
            'required' => false));
    echo '</div>';
?>

<script>

$(document).ready(function(){

    function showAndHideElements(){

        if ($('#<?="Patient" . Inflector::classify($wontComplete);?>')
                .is(':checked')){
            $('#<?=$fieldPrefix;?>mail').hide();
            $('#<?=$modePref;?>').hide();
            $('#<?=$fieldPrefix;?>online').hide();
            $('#<?=$wontCompleteReason;?>').show();
            return;
        }

        $('#<?=$wontCompleteReason;?>').hide();
        $('#<?=$fieldPrefix;?>mail').show();
        $('#<?=$modePref;?>').show();
        $('#<?=$fieldPrefix;?>online').show();

        var mode = $('#<?="Patient" . Inflector::classify($modePref);?>').val();
        switch (mode){
            case 'online':
                $('#<?=$fieldPrefix;?>mail').hide();
                $('#<?=$fieldPrefix;?>online').show();
                break;
            case 'mail':
                $('#<?=$fieldPrefix;?>online').hide();
                $('#<?=$fieldPrefix;?>mail').show();
                break;
            default:
                $('#<?=$fieldPrefix;?>online').hide();
                $('#<?=$fieldPrefix;?>mail').hide();
                break;
        }
    }// function showAndHideElements(){

    $('#<?="Patient" . Inflector::classify($wontComplete);?>, #<?="Patient" . Inflector::classify($modePref);?>').change(
        function(){
            showAndHideElements();
        }
    );

    showAndHideElements();

});// ready

</script>



    <div id='<?=$fieldPrefix;?>online'>
    <h4>Online</h4>

    <p><?php echo $patientProjectsStates[$project]->project['Project']['header'];?> status: <?php 

    $status = 'outside of date range';
    if ($patientProjectsStates[$project]->initableNonApptSessionType)
        $status = 'not yet started'; 
    elseif ($patientProjectsStates[$project]->resumableNonApptSession)
        $status = 'started'; 
    elseif ($patientProjectsStates[$project]->finishedNonApptSession)
        $status = 'completed'; 
   
    echo $status; 

    ?></p>
    <p>Emails sent regarding this: <a href="#emailsSent">please see above</a></p>
    <p></p>
    </div>

    <div id='<?=$fieldPrefix;?>mail'>
    <h4>Mail</h4>

<?php
    $mailFields = array('First mailed on' => 'first_mailing', 
                        'Second mailed on' => 'second_mailing', 
                        'Received on' => 'received_on',
                        'Postmarked on' => 'postmarked_on', 
                        'Faxed on' => 'faxed_on', 
                        'Fax received on' => 'fax_recd_on');

    foreach($mailFields as $label => $fieldName){
        $fieldName = $fieldPrefix . '_' . $fieldName;
        echo '<div class="control-group">';
        echo '<label for="Patient' . Inflector::classify($fieldName) . '" class="control-label">' . $label . '</label>';
        echo '<div class="controls">';
        echo $this->Form->text('Patient.'. $fieldName, array(
            'class' => 'datep span2',
            'data-date-past' => true,
            'label' => $label,
            'placeholder' => 'MM/DD/YYYY',
            'size' => 10,
            'maxlength' => 10));

        echo '</div>';
        echo '</div>';
    }


    echo $this->Form->hidden(AppController::CAKEID_KEY,
                        array(
                    'value' => $this->Session->read(AppController::ID_KEY)
                ));
    echo $this->Form->end();
?>

    </div> <!-- mail -->

