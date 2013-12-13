<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" => $quick_links)); ?>


<h1>"Interested" Patients</h1>

<p>(who are not yet consented or declined)</p>

<table class="display admin-table" id="interested-report-table">
	<thead>
    <tr>
        <th>&nbsp;</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>ID</th>
        <th>T1</th>
        <th>T2</th>
        <th>Clinic</th>
    </tr>
  </thead>
  <tbody>
<?php

if (empty($patients)) {
?>
    <tr>
        <td colspan=7>No patients</td>
    </tr>
<?php
} else {
    foreach($patients as $patient) { 
?>
    <tr>
        <td class="short"><?php 
	    echo $this->Html->image('magnify.gif', array(
	        'alt' => 'View',
					'title' => 'View',
	        'height' => '25',
	        'url' => array(
		    'controller' => 'patients', 
		    'action' => 'edit', 
                    $patient['Patient']['id']
		)
	    )); 

	    if ($canEdit) {
	        echo $this->Html->image('btnEditSmall.gif', array(
	            'alt' => 'Edit',
							'title' => 'Edit',
	            'url' => array(
		        'controller' => 'patients', 
		        'action' => 'edit', 
                        $patient['Patient']['id']
		    )
	        )); 
            }
        ?></td>
        <td><?php echo $patient['User']['first_name']; ?></td>
        <td><?php echo $patient['User']['last_name']; ?></td>
        <td><?php echo $patient['Patient']['id']; ?></td>
        <td><?php echo $patient['Patient']['t1']; ?></td>
        <td><?php echo $patient['Patient']['t2']; ?></td>
        <td><?php echo $patient['Clinic']['name']; ?></td>
    </tr>
<?php
    }
}
?>
</tbody>
</table>
