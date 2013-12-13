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

<h2>Check Agains</h2>

<?php
// $today = current date
$today = date('Y-m-d');
// all dates below relative to startdate, not today; 
$prevStart = date('Y-m-d', strtotime("$startdate -" . ($length + 1) . ' days'));
$nextStart = date('Y-m-d', strtotime("$enddate +1 day"));
echo '<div id="patients-nav" style="text-align: center">';
// create link for previous week
echo $this->Html->link('<i class="icon-chevron-left"></i> Previous Week', "checkAgainCalendar/$prevStart/$length",array('class' =>'weeknav btn', 'escape' => false));
echo '<h4 style="display: inline-block; margin: 10px; vertical-align: middle">Week of ' . date("M. j",strtotime($startdate)) . ' to ' . date("M. j, Y",strtotime($enddate)) . '</h4>';
echo $this->Html->link('Next Week <i class="icon-chevron-right"></i>', "checkAgainCalendar/$nextStart/$length",array('class' =>'weeknav btn', 'escape' => false, 'style' => 'margin-right: 10px'));
echo '<span>&nbsp;&nbsp;</span>';
if ( $today > $prevStart && $today < $nextStart ) {
    echo $this->Html->link('Current Week', "checkAgainCalendar/$today/$length",array('class' =>'weeknav btn disabled', 'escape' => false));
} else { 
    echo $this->Html->link('Current Week', "checkAgainCalendar/$today/$length",array('class' =>'weeknav btn', 'escape' => false));
}
echo '</div>';
?>

<br />

<?php
if (empty($checkAgains)) {
?>
<div class="well">
    <div>No check agains during this week.</div>
</div>
<?php
} else {
?>
<table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="check-again-cal-table">
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
    foreach($checkAgains as $checkAgain) { 
        $date = $checkAgain['check_again_date'];

      //  if (!empty($prevDate) && $date != $prevDate) {
	    // add visual cue between dates
      //      echo '<tr><td colspan="5"><hr noshade></td></tr>';
      //  }

	$id = $checkAgain['id'];
?>
    <tr>
        <td class="patient-id"><?php echo $checkAgain['id']; ?></td> 
        <td><?php echo $checkAgain['first_name']; ?></td>
        <td><?php echo $checkAgain['last_name']; ?></td>
        <td><?php echo date("D n/d",strtotime($date)); ?></td>
        <td><?php echo date("Y-m-d",strtotime($date)); ?></td>
        <td><?php echo $checkAgain['Clinic']['name']; ?></td>
        <td>
	<?php 
	    if (!empty($checkAgain['Note'])) {
	        echo $checkAgain['Note']['text']; 
	    } else {
	        echo '&nbsp;';
            }
	?>
	</td>
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

</div>
