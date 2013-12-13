<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
$nextLinkSpans = 'span6';
if ($page["ProgressType"] == 'graphical') {
    $nextLinkSpans = 'progress-container span';
}
?>

<h2><?=__("Skipped Questions")?></h2>
<?php
if (sizeof($skipped) > 0){
    echo "<p>" . __("Below is a list of sections in which you did not answer at least one of the questions. If you would like to add an answer, click on the question.") . "</p>";
    echo "<p>" . __("When you have finished all the questions you wish to answer, click the 'Finish' button at the bottom of the list. You will not be able to change your answers after clicking the 'Finish' button.") . "\n</p>";
  foreach($skipped as $id => $skip) {
    $questions = $skip["Questions"];
    $title = $skip["Questionnaire"]; 
    if($title == "Quality of Life and Peripheral Neuropathy")
        $title = "Quality of Life"; # FIXME esrac2 hack
    print "<h3>$title</h3>";
    print "<ul id='qr-$id'>\n";

    foreach ($questions as $question) {
        $text = $question["text"];
        $page = $question["page_id"];
        $link = $this->Html->link($text, "/surveys/show/$page?fromSkipped=true", 
                            array('escape' => false));
        print "<li>$link</li>\n";
    }
    print "</ul>\n\n";
  }
}
else {
    echo "<p>" . __("You do not have any skipped questions. If you're satisfied with your answers, touch or click 'Next' below. After doing so, you won't be able to change any of your answers.") . "</p>\n";
}
?>
<div class="row">
    <div class="<?= $nextLinkSpans ?>">
        <br />
        <?php
        if(strstr($page["NavigationType"], "prev")){
            echo $this->Html->link('<i class="icon-chevron-left"></i> ' .__('Previous'),
                    $back_link, array('class' => 'btn progress-prev', 'id' => 'prev-arrow-link', 'title' => __('Previous Page'), 'escape' => false), false);
        }
        // progress bar
        if ($page["ProgressType"] == 'graphical') { ?>
        <div title="<?= __("Your progress") ?>">
            <div class="progress">
                <div class="bar" style="width: <?php echo ($donePercent * 100)."%"; ?>"></div>
            </div>
        </div>
        <?php
        } // end progress bar
        echo $this->Html->link(__('Finish') . ' <i class="icon-chevron-right icon-white"></i> ', '/surveys/complete/1', array('class' => 'btn btn-primary btn-large progress-next calcInit', 'id' => 'next-arrow-link', 'title' => __('Click to finish answering questions'), 'escape' => false), false);
        ?>
        
    </div>
</div>

<?php
echo $this->element('session_complete_gate');
?>

<script>
$(".collapser").collapser();
    
// Make height of left sidebar fill the entire window
function resizeSide() {
    var currentHeight = $(window).height() - $('.esrac-header.row').height();
    $("#surveySidebar").css("height", currentHeight + "px");
}
$(document).ready(function(){
    resizeSide();
    $(window).bind('resize', resizeSide);
});
</script>
