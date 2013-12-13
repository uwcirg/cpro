<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * vars passed from the controller:
	 $journalEntries: an array of entries, each with keys text and displayDate
	 $patient: patient record whose journals are being viewed
*/
?>
<div class='viewingInfoFor'>
You are currently viewing reports for'
<?php
    echo $patient['User']['first_name'] . ' ' .
            $patient['User']['last_name'] . '\'. ';
    print $this->Html->link('Reports for other people',
                    '/results/others') . ' can also be viewed.';
?>
</div>

<div id="linkmaintopright">
<?php
    print $this->Html->link('Return to chart selection',
                            '/results/othersReportsList/' . 
                            $patient['Patient']['id']);
?>
</div>

<h1>Journals for 
<?php
    echo $patient['User']['first_name'] . ' ' .
            $patient['User']['last_name'] ;
?>
</h1>

<div class="subsection left chartpage">
    <div id="text" style="background-color:#eee; border:1px solid #999;">
        <ul>
            <li><a href="#about">About this journal</a></li>
        </ul>
        <div id="about">
            <!-- p>This is the default text about Journals, and perhaps some instructions for using the interface. Maybe there is mention of clicking various tabs and selecting the date range. </p -->
        </div>
    </div>
</div>

<?php echo $this->element('resultsdateseln',
                            array("chartsDisplayed" => 
                                    false)); ?>

<fieldset id="journal">
    <legend>Journal Entries
    </legend>
    <p>These are the journal entries for the selected time period.</p>
    <div id="journal-entries-container">
    </div>
</fieldset>


<script id="source" language="javascript" type="text/javascript">

// jquery launch on document ready 
$(function() { 
    
    $.extend({
        journals: new JournalsController(
            { 
                baseUrl: "<?php echo Router::url("/journals/"); ?>",
                patientId: <?php echo $patient['Patient']['id']; ?>,
                editable: !forAssociate 
            }
        )
    });

    // Add controller callbacks with new instances
    $.extend({  
        dates:  new DatesController({callbacks: [$.charts, $.journals]})
    })

    // All components created and linked: initialize and add dom elements
    $([$.journals, $.charts, $.subscales, $.dates]).each(function(i, item) {
        if(item && item.initialize) {
            item.initialize();
        }
    });

    // jQuery UI Tabbed pane call
    $("div#text").tabs();
});
</script>
