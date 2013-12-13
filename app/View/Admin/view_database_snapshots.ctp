<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div class="subsection left">
<h1>
Test Database Save/Load
</h1>

<p>
<?php
    echo $this->Html->link('Save the current test database', 
                     '/admin/saveDatabase?' .  AppController::ID_KEY . "=" .
                     $this->Session->read(AppController::ID_KEY));
?>
</p>
<?php
    if (empty($snapshots)) {
        echo '<p>No database snapshots to load</p>';
    } else {
?>
<h2>Load the test database from a snapshot</h2>
<p>Note: file timestamps are local to the server</p>
<ul>
<?php
        $i = 0;

        foreach ($snapshots as $snapshot) {
            echo '<li> ' . $this->Html->link($snapshot, 
	        "/admin/reloadDatabase/$i?filename=$snapshot&" .
                AppController::ID_KEY . "=" . 
		$this->Session->read(AppController::ID_KEY),
		array(), 
		'Are you sure you want to reload the database?') . 
                '</li>';
            $i++;
        }
    }
?>
</ul>

</div>


<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links)); ?>
