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
    
<h2>Appointment Calendar</h2>

<?php
// $today = current date
$today = date('Y-m-d');
// all dates below relative to startdate, not today; 
$prevStart = date('Y-m-d', strtotime("$startdate -" . ($length + 1) . ' days'));
$nextStart = date('Y-m-d', strtotime("$enddate +1 day"));
echo '<div id="patients-nav" style="text-align: center">';
// create link for previous week
echo $this->Html->link('<i class="icon-chevron-left"></i> Previous Week', "calendar/$prevStart/$length",array('class' =>'weeknav btn', 'escape' => false));
echo '<h4 style="display: inline-block; margin: 10px; vertical-align: middle">Week of ' . date("M. j",strtotime($startdate)) . ' to ' . date("M. j, Y",strtotime($enddate)) . '</h4>';
echo $this->Html->link('Next Week <i class="icon-chevron-right"></i>', "calendar/$nextStart/$length",array('class' =>'weeknav btn', 'escape' => false, 'style' => 'margin-right: 10px'));
if ( $today > $prevStart && $today < $nextStart ) {
    echo $this->Html->link('Current Week', "calendar/$today/$length",array('class' =>'weeknav btn disabled', 'escape' => false));
} else { 
    echo $this->Html->link('Current Week', "calendar/$today/$length",array('class' =>'weeknav btn', 'escape' => false));
}
echo '</div>';
?>

<br />

<?php

if (empty($appts)) {
?>
<div class="well">
    <div>No appointments are scheduled for this week.</div>
</div>
<?php
} else {
?>
<table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="appointment-table">
    <thead>
	<tr>
            <th>ID</th>
            <th>First</th>
            <th>Last</th>
            <th>Date</th>
            <th>Date Sort</th>
            <?php
            $appointmentLimit = Configure::read('appointmentLimit');
            // If appointmentLimit != null, then display appt number
            if ($appointmentLimit != 1 ) echo '<th>Appt #&nbsp;</th>';
            ?>
            <th>Clinic</th>
            <th>Location</th>
            <th>Staff</th>
            <th>Status</th>
            <th><small>Last Session Type</small></th>
            <th><small>Last Session Date</small></th>
            <th><small>Last Session Status</small></th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($appts as $appt) {
        // datetime like '2011-04-12 10:00:00' 
        $date = substr($appt['Appointment']['datetime'], 0, 10);

       // if (!empty($prevDate) && $date != $prevDate) {
	    // add visual cue between dates
            // echo '<tr><td colspan="9"><hr noshade></td></tr>';
       // }
    ?>
    <tr>
        <td class="patient-id"><?php echo $appt['Patient']['id']; ?></td>
        <td><?php echo $appt['Patient']['User']['first_name']; ?></td>
        <td><?php echo $appt['Patient']['User']['last_name']; ?></td>
        <td><?php echo date("D n/d H:i",strtotime($appt['Appointment']['datetime'])); ?></td>
        <td><?php echo date("Y-m-d H:i",strtotime($appt['Appointment']['datetime'])); ?></td>
        <?php if ($appointmentLimit != 1) echo '<td>'.($appt['Appointment']['number'] + 1).'</td>'; ?>
        <td class="short"><?php echo $appt['Patient']['Clinic']['name']; ?></td>
        <td><?php echo $appt['Appointment']['location']; ?></td>
        <td><?php 
            if (isset($appt['Appointment']['staff_username'])){
                echo $appt['Appointment']['staff_username'];
            }
            else echo "(unassigned)" 
        ?></td>
        <td><?php echo $appt['Patient']['consent_status']; ?></td>

        <td><?php echo $appt['Patient']['last_session_proj']; ?></td>
        <td><?php 
        echo ($appt['Patient']['last_session_date'] == '(no session)') ? $appt['Patient']['last_session_date'] : date("n/d H:i",strtotime($appt['Patient']['last_session_date']));
        ?></td>
        <td><?php echo $appt['Patient']['last_session_status']; ?></td>

    </tr>
    <?php
    $prevDate = $date;
    }
    ?>
</tbody>
</table>
<?php
}
?>


<br />

</div>
