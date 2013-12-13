<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
  /* vars passed from the controller:
	 $subscaleId : an int: the subscale id for the current page
	 $subscaleData : string of javascript data for this subscale
	 $subscaleName: a string with the name of this subscale
     $journalEntries: an array of entries, each with keys text and displayDate

     FIXME: this whole file needs ot be updated to match show.ctp
     or as another option within show.ctp. I (AS) am not sure what
     exactly the page should look like, so I'll leave it for later
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

<?php
    echo "<h1>";
    echo $this->Html->link("View Reports", "/results/others",
                        array('class' =>'dontUnderlineLinks'));
    echo " - $subscaleName</h1>";
?>


<div class="subsection left chartpage">
<div id="text" style="background-color:#eee; border:1px solid #888;">
  <ul>
<?php if(isset($teachingTip) && $teachingTip) { ?>
    <li><a href="#result-coach">Teaching Tips</a></li>
<?php } ?>
  </ul>
<?php if(isset($teachingTip) && $teachingTip) { ?>
<div id="result-coach" class="scale">
<span class="siteId"><?php echo $siteId; ?></span>
<?php 
    echo $teachingTip["text"]
?>
</div>
<?php } ?>
</div> <!-- text -->
</div> <!-- subsection left chartpage -->
<?php 
    echo $this->element('show_chart',
                        array("forAssociate" => true)); 
?>
<script>
$(function() {
    $.enableTips();
});
function loadAgain() {
    // That we have to do this is a sign something is wrong.
    // One of the jQuery selectors in the normal chart initialization
    // is failing, so we have to do it again manually...
    $.journals = 
        new JournalsController(
            { 
                baseUrl: "<?php echo Router::url("/journals/"); ?>",
                patientId: <?php echo $patient['Patient']['id']; ?>,
                editable: false
            }
    );
    $.dates = new DatesController({ callbacks: [ $.journals ] });
    $.journals.initialize();
    $.dates.initialize();

}
loadAgain();
</script>
