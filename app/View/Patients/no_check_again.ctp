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

<h2>Participants with Check-Again Dates in the Past</h2>

<?php
if (empty($patients)) {
?>
<div class="well">
    <div>All participants have check-again dates after <?php echo $startdate ?></div>
</div>
<?php
} else {
?>
<table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="no-check-again-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Check Again Date</th>
        <th>Date Sort</th>
        <th>Clinic</th>
        <th>Most Recent Note</th>
    </tr>
  </thead>
  <tbody>
<?php
    foreach($patients as $patient) { 
        $date = $patient['Patient']['check_again_date'];
	$patientId = $patient['Patient']['id'];
?>
    <tr>
        <td class="patient-id"><?php echo $patient['Patient']['id']; ?></td>
        <td><?php echo $patient['User']['first_name']; ?></td>
        <td><?php echo $patient['User']['last_name']; ?></td>
        <td><?php if ($date) { echo date("D n/d",strtotime($date)); } ?></td>
        <td><?php if ($date) { echo date("Y-m-d",strtotime($date)); } ?></td>
        <td class="short"><?php echo $patient['Clinic']['name']; ?></td>
        <td>
				<?php 
            if (!empty($notes[$patientId])) {
                echo $notes[$patientId]['text']; 
            } else {
                echo '&nbsp;';
                  }
        ?>
        </td>
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
