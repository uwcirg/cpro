<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div class="subsection right">
	 <fieldset>
	 <legend>Announcements</legend>

	 <ul>
		  <li>It is time to report your Time Point 1 survey. Please try to complete it before this Friday, June 13 at 5:00 pm.</li>
		  <h3><?php echo $this->Html->link("Start Survey", "/surveys/new_session/1"); ?></h3>
	 </ul>
	 </fieldset>
</div>

<fieldset id="journal">
	 <legend>Journal Entries</legend>

	 <div id="new-entry">
	 <textarea></textarea>
	 <input type="submit" value="Add Entry"/>
	 </div>

	 <?php foreach($entries as $date => $entry) { ?>
	 <p class="entry"><strong><?php echo $date; ?></strong> <?php echo $entry; ?></p>
												  <?php } ?>

</fieldset>
