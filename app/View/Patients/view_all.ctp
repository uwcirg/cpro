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
    <h2>View Patient Records</h2>

    <?php
    if (empty($patients)) {
    ?>
    <div class="well">
        <div>There are no patients in the system.</div>
    </div>
    <?php
    } else {
    ?>
    
    <p><?php echo $this->Html->link('<i class="icon-download-alt"></i> Export as CSV', 'viewAll/Patient.id/asc/true', array('class' => 'btn btn-small', 'escape' =>  false)); ?></p>

    <table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="view-all-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>First</th>
                <th>Last</th>
                <th>MRN</th>
                <th>Consent Status</th>
                <th>Next Appt</th>
                <th>Clinic</th>
                <th><small>Last Session Type</small></th>
                <th><small>Last Session Date</small></th>
                <th><small>Last Session Status</small></th>
            </tr>
        </thead>
        <tbody>
        <?php
            foreach($patients as $patient) { 
        ?>
            <tr>
                <td class="patient-id"><?php echo $patient['Patient']['id']; ?></td>                
                <td><?php echo $patient['User']['first_name']; ?></td>
                <td><?php echo $patient['User']['last_name']; ?></td>
                <td><?php echo $patient['Patient']['MRN']; ?></td>
                <td><?php echo $patient['Patient']['consent_status']; ?></td>
                <td><?php 
                    if ($patient['Patient']['next_appt_dt']) echo date("m/d/Y H:i",strtotime($patient['Patient']['next_appt_dt']));
                ?></td>
                <td><?php echo $patient['Clinic']['name']; ?></td>
                <td><?php echo $patient['SurveySession']['last_session_proj']; ?></td>
                <td><?php
                // If last_session_date starts with a number then convert date format
                if (is_numeric(substr($patient['SurveySession']['last_session_date'], 0, 4))) {
                    echo date("m/d/y H:i",strtotime($patient['SurveySession']['last_session_date']));
                } else {
                    echo $patient['SurveySession']['last_session_date'];
                }
                ?></td>
                <td><?php echo $patient['SurveySession']['last_session_status']; ?></td>
            </tr>
            <?php
            }
            ?>
        
        </tbody>
        </table>    
    
        <?php
        }
        ?>
        
    <br  clear="all" />
    <p><?php echo $this->Html->link('<i class="icon-download-alt"></i> Export as CSV', 'viewAll/Patient.id/asc/true', array('class' => 'btn btn-small', 'escape' =>  false)); ?></p>
    
</div>




