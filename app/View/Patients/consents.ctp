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
    
    <h2>Verify patient consents</h2>
    
    <p class="text-warning">Note: please use Firefox or Chrome to view this page, as it has had problems with Internet Explorer.</p>

    <?php
    echo $this->Form->create('Patient', array(
        'action' => 'consents',
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
    echo $this->Form->hidden(AppController::CAKEID_KEY, array(
        'value' => $this->Session->read(AppController::ID_KEY)
    ));
    
    if (empty($patients)) {
    ?>
        <tr>
            <td colspan=6>No unverified patients</td>
        </tr>
    <?php
    } else {
    ?>
    <table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="consents-table">
        <thead>
            <tr>
                <th class="short_8em">Consent verified?</th>
                <?php
                if ($showHipaaColumn) {
                    echo '<th>HIPAA Consent verified?</th>';
                }
                ?>
                <th>Patient ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>ID</th>
                <th>Clinic</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($patients as $patient) { 
            $patientId = $patient['Patient']['id'];
        ?>
            <tr>
                <td align="center">
                <?php 
                    if ($patient['Patient']['consent_checked']) {
                        echo $this->Html->image('check.jpg', array('alt' => 'Consented',
                                                             'height' => '15'));
                    } else {
                        echo $this->Form->checkbox('Patient.consent_checked.' . $patientId);
                    }
                ?> 
                </td>
                <?php
                if ($showHipaaColumn) {
                ?>
                <td align="center">
                <?php
                    if (!defined('HIPAA_CONSENT_SITE_ID') 
                        || $patient['Clinic']['site_id'] != HIPAA_CONSENT_SITE_ID)
                    {
                        echo 'N/A';
                    } else if ($patient['Patient']['hipaa_consent_checked']) {
                        echo $this->Html->image('check.jpg', array('alt' => 'Consented',
                                                             'height' => '15'));
                    } else {
                        echo $this->Form->checkbox('Patient.hipaa_consent_checked.' . 
                                             $patientId);
                    }
                ?> 
                </td>
                <?php
                }
                ?>
                <td><?php echo $patient['Patient']['id']; ?></td>
                <td><?php echo $patient['User']['first_name']; ?></td>
                <td><?php echo $patient['User']['last_name']; ?></td>
                <td><?php echo $patientId; ?></td>
                <td><?php echo $patient['Clinic']['name']; ?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <br />
    <?php
    }
    echo $this->Form->submit("Submit", array(
        "class"=>"btn btn-large btn-primary", "div" => array(
            "class" => 'control-group'
        )
    )); 
    echo $this->Form->end(); 
    ?>

</div>

