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

    <h2>Participant Status Report</h2>

    <div><strong>Summary:</strong></div>

    <ul>
    <?php
    foreach($offStudyEnumsCount as $offStudyEnum => $count){
        echo $offStudyEnum . ': ' . $count . '<br/>';
    }
    ?>
    </ul>

    <?php
    if (empty($patients)) {
    ?>
    <div class="well">
        <div>There are no patients in the system.</div>
    </div>
    <?php
    } else {
    ?>
    <table class="table table-striped table-bordered table-hover table-condensed table-small-text patient-datatable" id="off-study-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Clinic</th>
                <th>Off-study Status</th>
                <th>Off-study Timestamp</th>
                <th style="width: 200px">Off-study Reason</th>
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
            <td><?php echo $patient['Clinic']['name']; ?></td>
            <td><?php echo $patient['Patient']['off_study_status']; ?></td>
            <td><?php echo $patient['Patient']['off_study_timestamp']; ?></td>
            <td><?php echo $patient['Patient']['off_study_reason']; ?></td>
        </tr>
    <?php
        }
    ?>
    </tbody>
    </table>
    <?php
    }
    ?>
</div>
