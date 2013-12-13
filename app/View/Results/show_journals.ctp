<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * vars passed from the controller:
	 $journalEntries: an array of entries, each with keys text and displayDate
*/
?>
<div id="linkmaintopright">
<?php
    print $this->Html->link('Return to chart selection',
                    //'results/index/', false, false, false);
                    '/results');
?>
</div>
<h1>Journals</h1>

<div class="subsection left chartpage">
    <div id="text" style="background-color:#eee; border:1px solid #999;">
        <ul>
            <li><a href="#share">Share this journal</a></li>
            <!--li><a href="#options">Advanced Customization</a></li-->
            <li><a href="#about">About this journal</a></li>
        </ul>
        <div id="about">
            <!-- p>This is the default text about Journals, and perhaps some instructions for using the interface. Maybe there is mention of clicking various tabs and selecting the date range. </p -->
        </div>
        <div id="share">
            <p>You are currently sharing this journals with two people. To change your sharing options please visit the 
        <?php echo $this->Html->link("settings", "/associates/edit_list"); ?> page.
            </p>
        </div>
    </div>
</div>

<?php echo $this->element('resultsdateseln',
                            array("chartsDisplayed" => 
                                    false)); ?>

<div class="rounded" style="background-color:#E7F0F5;">

	<h2>Journal Entries</h2>
	<p>These are the journal entries for the selected time period.</p>
	<div id="journal-entries-container">
        </div>
</div>

<!-- loadJournals defined in views/elements/journals.ctp -->
<?php 
    echo $this->element(
                'journals',
                array("patient" => $patient,
                      "journalAction" => "index"));
?>


<script id="source" language="javascript" type="text/javascript">

// jquery launch on document ready 
jQuery(function($) { 
    loadJournals(function() {
        $("#journal").show();
	    $(".journal-entry").addClass(".editable");
    });

    $("div#text").tabs();
});

</script>
