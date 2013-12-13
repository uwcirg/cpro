<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
?>

<div class="span2">
<?php
echo $this->element(
    'quick_links_admin_tab',
    array('quick_links' => $quick_links)
);
?>
</div>

<div class="span10">

<h2>One-week Follow-up Eligible Patients</h2>

<table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="one-week-table">
    <thead>
<?php
echo $this->Html->tableHeaders(array(
    'Patient ID',
    'First Name',
    'Last Name',
    'Start (appt + 6 days)',
    'Stop (appt + 14 days)'/**,
    'Appointment'*/
));
?>
    </thead>
    <tbody>
<?php        
foreach($reportablePatients as $patient){
    echo '<tr><td class="patient-id">';
    echo $patient['Patient']['id']."</td>";
    echo "<td>".$patient['User']['first_name']."</td>";
    echo "<td>".$patient['User']['last_name']."</td>";
    echo "<td>".$patient['Patient']['window_open']."</td>";
    echo "<td>".$patient['Patient']['window_close']."</td>";
    //echo "<td>".$patient['Patient']['appt_dt']."</td>";
    echo '</tr>';
}
?>
    </tbody>
</table>
