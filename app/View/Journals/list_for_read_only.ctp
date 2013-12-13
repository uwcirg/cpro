<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>    

    <table id="journal-entries">
    <thead><tr><th>Date</th><th>Journal Entry</th></tr></thead>
    <tbody>
<?php 
foreach($journalEntries as $entry) {
    $entry = $entry["JournalEntry"];
        $timestamp = strtotime($entry['date']) . "000";
        echo "<tr class='journal-entry'>";
        echo "<td class='journal-date' id='$timestamp'><p class='entry'><strong>" . date("m/d/y", strtotime($entry['date'])) . "</strong></p></td>";
        echo "<td class='journal-text'><p class='text' id='$entry[id]'>" . $entry["text"] . "</p></td>";
        echo "</tr>";
}?>

    </tbody>
    </table>

    <script language="JavaScript">

</script>
