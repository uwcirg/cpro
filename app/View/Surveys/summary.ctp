<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<div class="span2">
    <div class="intervention-sidebar" data-spy="affix"  data-offset-top="165">
        <ul id="navbar" class="nav nav-list">
            <?php echo $tableOfContents ?>
        </ul>
    </div>
</div>

<div class="span10">
    <?php 
    echo $projectInfo;
    ?>
    <p><strong>To Edit</strong>: This pages uses "inline" editing. When you put 
        your mouse over any page title, page body text, question or answer your
        cursor will become a pointer. Click to begin editing that section.</p>
    <p>When you've completed your edits, click on "OK" to save. <em>Please note:
        </em> some elements contain HTML as well as variables from the database. 
        Please edit with care.</p>
    <hr />
        
    <p>This page displays the survey's sequence of questionnaires and pages, including the content of each page and any conditions applying to questionnaires or pages. It also displays scoring information for scales, subscales & items; these are mapped per questionnaire but those don't always align perfectly, so display of scoring information is not perfect.</p>
    <ul><li>The first parameter is the project number</li>
    <li>The second parameter specifies language. It defaults to 'all', which will display text for all defined languages. Other possible values: 'none' displays the english version only; or eg 'Spanish' to have only one language displayed w/out the english version.</li>
    <li>The third parameter specifies whether only patient-readable text should be displayed, as opposed to ID's, conditions and the like along with text. It defaults to false.</li></ul>
    <p>The "value" displayed for each option is its sequence; if the option has an analysis value that will be displayed as well.'.</p>

    <hr />
    <?php
    echo $surveyHtml;
    ?>
</div>

<div id="editorPreviewModal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Preview:</h3>
  </div>
  <div class="modal-body" id="modalBody">
    
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">Close</a>
  </div>
</div>

<script>
/*** jquery for use with glossary popover. Builds on cpro.p3p.js ***/
// Disable popver function on editable div - causes conflicts if not
$('.editable [rel=popover]').popover('disable');
// View text in modal to interact with popover glossary function
$('#editorPreviewModal').on('show', function () {
  $("#modalBody [rel=popover]").on("click").popover({placement: 'top'});
})
</script>