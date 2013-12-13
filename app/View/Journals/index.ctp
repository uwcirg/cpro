<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>    

    <table id="journal-entries">
    <thead><tr><th>Date</th><th>Journal Entry</th><th>Actions</th></tr></thead>
    <tbody>

    <tr id="add-journal-entry">
	    <td colspan="3"><a href="#" class="addNew"><div class="rounded button" style="float:right;width:auto;background-color:#ccc;border-color:#999;font-size:1em;">Add new entry</div></a></td>    

    </tr>

    <tr id="new-journal-entry" class="journal-entry">
	    <td valign="top" class="journal-date"><input size="8" class="date" name="data[JournalEntry][date]"/></td>
	    <td valign="top" class="journal-text"><textarea class="text" name="data[JournalEntry][text]"/></textarea>
	    <td valign="top" class="actions"><input type="Submit" class="submitnew" value="Save entry"/></td>
    </tr>
<?php 
$count = 0;
foreach($journalEntries as $entry) {
    $entry = $entry["JournalEntry"];

	$jclass = "journal-entry";
	if($count % 2 == 0){
		$jclass = $jclass . " highlightedrow";
	}
        $timestamp = strtotime($entry['date']) . "000";
        echo "<tr class='$jclass'> ";
        echo "<td class='journal-date' id='$timestamp'><p class='entry'><strong>" . date("n/j/y", strtotime($entry['date'])) . "</strong></p></td>";
        echo "<td class='journal-text'><p class='text' id='$entry[id]'>" . nl2br($entry["text"]) . "</p></td>";
        echo "<td class='actions'><p>";
        echo "<a href='#' class='edit'>Edit</a> <br>";
        echo "<a href='#' class='delete'>Delete</a></p>";
        echo "<input type='submit' class='submit' value='Save' style='display:none;'/></td>";
        echo "</tr>";
	$count++;
}?>


    </tbody>
    </table>
