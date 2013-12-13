<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/

define ("CHECKED_HTML", 'checked=\'checked\'');
$surveyLayoutClass = 'survey-layout-regular';
if ($project['ui_small']){
    $surveyLayoutClass = 'survey-layout-small';
}
if ($surveyLayoutClass == 'survey-layout-small') {
    define ("ICON_SIZE", '');
} else {
    define ("ICON_SIZE", ' icon-3x');
}


/**
* Revised radio button function - 201309. No longer uses tables.
*/
function radio_button($question, $optionId, $optionIds, $type, $answer, $bodyText, $layout) {

//        CakeLog::write('debug', __FUNCTION__ . '(), params: ' . print_r(func_get_args(), true));

    $class = "radio-button";
    $iconCheck = "icon-circle-blank";
    $isChecked = "";
    $dataPrev ="";

    if($answer) {
        if($answer == $optionId || (!in_array($answer, $optionIds) && $type == 'combo-radio')){
            $class .= " selected";
            $iconCheck = "icon-circle";
            $isChecked = "checked";
            $dataPrev = " data-prev='checked'";
        }
    }

    $button = "<label for='$question-$optionId'>";
    if ($layout != 'horizontal' && $layout != 'matrix') {
        $button .= "<span>$bodyText</span>";
    }
    $button .=
        "<input type='radio' $dataPrev name='$question' id='$question-$optionId' class='$class' $isChecked />";
    $button .=
        "<i class='$iconCheck".ICON_SIZE."'></i>";
    if ($layout == 'horizontal') {
        $button .= "<div>$bodyText</div>";
    }
    $button .= "</label>";

    return $button;
}

/**
 * Revised checkbox function - 201309. No longer uses tables.
 */
function echoCheckboxOption($q, $opt, $class, $checked, $comboText = null){

    //CakeLog::write('debug', 'echoCheckboxOption() for option w/ BodyText:' . $opt['BodyText']);
    $isChecked = 'icon-check-empty';
    if ($checked != '') {
        $isChecked = 'icon-check';
    }
    $comboBoxClass = ($opt['OptionType'] == 'combo-check') ? "combo-label" : "";

    echo "<label for='$q[id]-$opt[id]'>";
    echo "<input class='$class $comboBoxClass' $checked value='$opt[id]' type='checkbox' name='$q[id]' id='$q[id]-$opt[id]' value=''/>";
    echo "<i class='$isChecked".ICON_SIZE."'></i><span>$opt[BodyText]</span></label>";

    if ($opt['OptionType'] == 'combo-check'){
        $textInputHide = "";
        if ($checked != CHECKED_HTML) $textInputHide = 'hide';
        echo "<div class='comboTextHolder $textInputHide'>";
        echo "<span class='inline-help'><em>";
        // Check for ancillary text and display it
        if (
            isset($opt['AncillaryText']) and
            $opt['AncillaryText']
        )
            echo __($opt['AncillaryText']);
        else
            echo __('Please Specify');
        echo ":</em></span> ";
        // $opt["ValueRestriction"] can limit the type of input. Currently
        // used for numeric only on input - see cpro.jquery for functions
        echo "<input class='comboText combo-check {$opt['ValueRestriction']}";
        if ($opt["ValueRestriction"] == 'numeric') echo " input-mini";
        echo "'  value='$comboText' type='text' id='$q[id]-$opt[id]-combo' /></div>";
    }

}// function echoCheckboxOption


if (!empty($takingSurveyAs)) {

    echo "<div id='flashMessage' class='alert alert-info flash-message'>";
    if ($patient['User']['first_name'] and $patient['User']['last_name'])
        echo __(
            "Administering assessment for <strong>%s</strong>",
            "{$patient['User']['first_name']} {$patient['User']['last_name']}"
        );
    else
        echo __(
            "Administering assessment for <strong>%s</strong>",
            "#{$patient['User']['id']}"
        );
    echo "</div>";
}

if($not_allowed) {
    echo "<div class='alert'>" . __("Warning: this portion of the survey is no longer editable. Though you may view answers, they cannot be changed.") . " ";
    echo $this->Html->link("Click here to ".$not_allowed_text, $not_allowed_link);
    echo "</div>";
}

if ($page["Header"]) {
  echo '<div class="pageHeader">'.$page["Header"];
  if (isset($iterable) and $iterable){
      echo " - #";
      echo  $iteration+1;
  }
  echo '</div>';
}

// number of questions, used in page text to add/show border-bottom
$numQs = sizeof($questions);

if ($page["BodyText"]) {
  $noQuestionClass = "";
  if ($numQs == 0) $noQuestionClass = "no-question";
  echo "<div class='question-page-text $surveyLayoutClass $noQuestionClass'><p>" .
          $page["BodyText"] . "</p></div>";
          //$page["BodyTextSpanish"] . "</b></p></div>";
}

$currentQ = 0;
foreach($questions as $question) {

  $lastQ = false;
  if ($currentQ == ($numQs - 1)) $lastQ = true;

  echo $this->element('question',
      array("question" => $question,
              "numQs" => $numQs,
              "lastQ" => $lastQ));
  $currentQ++;
}

if (isset($iterable) and $iterable){
    echo $this->Html->link('<i class="icon-plus icon-white"></i> '.__('Add another: ').$page["Header"], array('controller'=>'surveys', 'action'=>'show', $page['id'], $iteration+1), array('class' => 'btn btn-primary', 'style' => 'margin: 1px 0 0 6px; float: left', 'id' => 'next-arrow-link', 'title' => __('Add another: ').$page["Header"], 'escape' => false), false);
    echo '<br><br>';
}

# TODO: make an element
  if($alerts) {
      foreach($alerts as $alert) {
          print "<div class='alert alert-danger'>" . $alert["Alert"]["message"] . "</div>";
      }
  }

// ProgressType graphical means display the progress bar and adjust the nav
// positioning slightly.
$nextLinkSpans = 'span6';
if ($page["ProgressType"] == 'graphical') {
    $nextLinkSpans = 'progress-container span';
}

// Do not show next/prev navigation if coming from skipped questions
if(defined('SKIPPED_QUESTIONS_PAGE')
    && !$patient['Patient']['test_flag'] 
    && isset($fromSkipped) && $fromSkipped) {
} 
else {
?>

<div class="row">
    <div class="<?= $nextLinkSpans ?>">
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

        if(strstr($page["NavigationType"], "next")){
            echo $this->Html->link(__('Next') . ' <i class="icon-chevron-right icon-white"></i> ', $next_link, array('class' => 'btn btn-primary btn-large progress-next', 'id' => 'next-arrow-link', 'title' => __('Next Page'), 'escape' => false), false);
        }
        elseif(strstr($page["NavigationType"], "finish")){
            //TODO impl if need be...
            $description = 'Finish';
            if(!defined('SINGLE_STAGE_FINALIZATION')
                        || SINGLE_STAGE_FINALIZATION === false){

                $description = 'Submit';
            }
            echo $this->Html->link($description, 'surveys/complete', array('class' => 'button-link next-button', 'id' => 'next-arrow-link', 'title' => $description, 'escape' => false), false);
        }
        ?>
    </div>
</div>
<?php
} // End - Do not show next/prev navigation

// Results link (in use?)
if (isset($resultsLink) && $resultsLink) {
    echo '<br /><br /><div class="text-center">';
    if ($resultsLink == SurveysController::NO_ASSOCIATED_RESULTS) {
        echo $this->Html->link(__("results page"),
                            "/results/");
    } else {
        echo $this->Html->link(__("results for this question"),
                            $resultsLink);
    }
    echo '</div><br clear="all" />';
}
// Skipped questions link
if (defined('SKIPPED_QUESTIONS_PAGE')
    && (isset($fromSkipped) && $fromSkipped)) {
        echo '<br /><br /><div class="text-center">';
        echo $this->Html->link(
                __("Save answer and return to skipped questions page").' <i class="icon-chevron-right icon-white"></i>',
                "/surveys/show/" . SKIPPED_QUESTIONS_PAGE,
                array('class'=>'returnToSkipped btn btn-large btn-primary', 'escape' => false));
        echo "</div><br /><br />";
}
?>
</div>

<?php
$url = $this->Html->url("/surveys/answer/");
?>
<script>
<?php echo "iteration = $iteration;\n"; ?>
<?php echo "page_id = {$page['id']};\n"; ?>
$(function() {

    var keyPresses = 0;
    var keyInterval = 20;
    function postTextAreaForKeypresses(e){
        keyPresses++;
        if (keyPresses % keyInterval == 0){
            postTextArea(e);
        }
    }

    function postTextArea(e) {
        var item = e.target;
        if ($(this).hasClass('numeric')) {
            validatenumberformat($(this).attr('id'));
        }
        $.post("<?php echo $url ?>" + item.name + ".json",
                { "data[Answer][question_id]" : item.name,
                "data[Answer][body_text]"   : item.value ,
                "data[Answer][iteration]"   : iteration,
                    //+ "; " + e.target +
                    //"; " + e.type,
                "data[Page][id]" : <?php echo $page['id']; ?>,
                "<?php echo AppController::FORMID_KEY;?>" : acidValue
        });
    }

    // combos handled in cpro.jquery.js
    // Previously had blur, change and keypress. Should be able to use just keyup
    $("input[type=text]:not(.comboText)").keyup(postTextArea);
    // Handle textarea a bit differently
    $("textarea").blur(postTextArea);
    $("textarea").change(postTextArea);
    // NEXT WON'T WORK IF THEY PASTE TEXT IN...
    $("textarea").keypress(postTextAreaForKeypresses);
});

// In question container set width of first column of answers to get things
// to be even. TODO - works fine for 1 or 2 columns. How will it work with 3+ ?
$('.multi-col').each(function(){
    var radioAnswerWidth = 0;
    var numColumns = $(this).find('div:first-child .q-option').size();
    var containerWidth = $(this).width();
    var colMax = Math.floor((containerWidth / numColumns) - (30 * (numColumns-1)));
    var checkMax = 100;
    $(this).find('div.q-row .q-option:first-child').each(function(cnt,itm) {
        var radioWidth = $(this).width();
        if (radioWidth >= checkMax) {
            checkMax = radioWidth;
        }
    });
    $(this).find('div.q-row .q-option:first-child').css('width', checkMax);
});

/** Matrix questions **/
// Matrix grid is typically divided so left .q-matrix and right .a-matrix
// are both 50% wide. If there are only two answer choices then changes to
// 60/40 split.
var optionCount = $('.matrix-container').first().find('div.a-matrix .q-option').length;
if (optionCount < 3) {
    $(".survey-layout-small .q-matrix-top, .survey-layout-small .q-matrix").css("width","60%");
    $(".survey-layout-small .a-matrix").css("width","40%");
}
// Add striping for readability. For some reason css nth-child wasn't working
// properly, so addClass instead.
$( ".matrix-container:odd" ).addClass("matrix-striped");
// Adjustments for top row of questions so that vertical spacing is consistent
$('.matrix-container').first().addClass("matrix-first");
var optionHeight = $('.matrix-container.matrix-first').find('div.q-container.a-matrix').height();
// Make left .q-matrix the same height and add wrap to position at bottom
$('.matrix-container .q-matrix-top').css('height',optionHeight);
$('.q-matrix-top').wrapInner('<div class="q-pos-bot">');
// This makes the top matrix question label the same height so that radio
// buttons are even vertically
$('.matrix-first .horizontal-container.a-matrix').each(function() {
    var calcHeight = 0;
    $(this).find('.q-option span').each(function(){
        var theHeight = $(this).height();
        if( theHeight > calcHeight ) {
            calcHeight  = theHeight;
        }
    })
    $(this).find('.q-option span').css("height",calcHeight);
});
// Add class to change spacing of final question row
$('.matrix-container').last().addClass("matrix-last");
/** //matrix questions **/

// Makes radio or checkbox buttons (output as <i> tags) be vertically aligned
// in center of answer text if answer text is 2 or more lines long
$('.q-row.q-vertical-list').each(function() {
    var spanHeight = $(this).find('.q-option span').height();
    var iconHeight = $(this).find('.q-option label i').height();
    if( spanHeight > iconHeight ) {
        $(this).find('.q-option label i').css("line-height",spanHeight+"px");
    }
});

$(document).ready(function(){
    // Add error msg to any numeric-restricted fields that have non-numeric
    // answers on load. Useful if they prev filled out wrong and are returning.
    $('input.numeric').each(function(){
        var numId = $(this).attr('id');
        validatenumberformat(numId);
    })
});

<?
if (defined('SHOW_TOUR') && SHOW_TOUR && !$is_staff) {
    /** FIXME - Testing guiders - display guider if ID is 1463 (currently the 1st question)
     *  Will fix this when guided tour is actually implemeted. **/
    if ($page['id'] == '1463') {
    ?>

    /** First guider, shows on page load **/
    guiders.createGuider({
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "We'll ask you a series of questions that we'll use to build a personalized profile for you.<br /><br />Let's see how it works before you get started. Press the \"Next\" button below to learn how to use the system or choose \"Exit Tour\" if you want to jump right in.",
      id: "guide1",
      next: "guide2",
      overlay: true,
      title: "Get started building your profile.",
      xButton: true
    }).show();

    /** Additional Guiders **/
    guiders.createGuider({
      attachTo: ".header-links li:last-child",
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "First off, if you need to take a break or would like help using the system, use these links in the upper right.",
      id: "guide2",
      next: "guide3",
      overlay: true,
      position: 6,
      title: "Some handy links.",
      xButton: true
    });
    guiders.createGuider({
      attachTo: ".q-text .guider-point",
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "Each page will have one question. For example, the first question asks about your age group.",
      id: "guide3",
      next: "guide3a",
      overlay: true,
      position: 6,
      title: "Answering the questions",
      xButton: true
    });
    guiders.createGuider({
      attachTo: "#surveyPage ul.radio-buttons .guider-point",
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "Most questions allow you to choose one answer from a set of 4 or 5 choices. To answer, click on the round circle next to your answer choice. When you select an answer the circle will get darker.",
      id: "guide3a",
      next: "guide4",
      overlay: true,
      position: 3,
      title: "Answering the questions",
      xButton: true
    });
    guiders.createGuider({
      attachTo: "#next-arrow-link",
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "Once you've selected an answer, click or touch the \"Next\" button to move to the next question.",
      id: "guide4",
      next: "guide5",
      overlay: true,
      position: 12,
      title: "Go to the next question",
      xButton: true
    });
    guiders.createGuider({
      attachTo: "#prev-arrow-link",
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "If you ever want to change an answer, you can click on \"Previous\" to back one question.",
      id: "guide5",
      next: "guide6",
      overlay: true,
      position: 12,
      title: "Going back",
      xButton: true
    });
    guiders.createGuider({
      attachTo: "#progress",
      buttons: [
        { name: "Next <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.next
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "This bar will give a rough indication of far you are in building your profile.",
      id: "guide6",
      next: "guide7",
      overlay: true,
      position: 12,
      title: "Your progress",
      xButton: true
    });
    guiders.createGuider({
      attachTo: "#progress",
      buttons: [
        { name: "Get Started <i class='icon-chevron-right icon-white'></i>",
          classString: "btn btn-primary",
          onclick: guiders.hideAll
        },{ name: "<i class='icon-chevron-left'></i> Back",
          classString: "btn btn-small",
          onclick: guiders.prev
        },{ name: "Exit Tour <i class='icon-remove icon-gray'></i>",
          classString: "btn btn-small",
          onclick: guiders.hideAll
        }
      ],
      description: "OK, that's a quick orientation. Click or touch \"Get Started\" below to answer the first question.",
      id: "guide7",
      overlay: true,
      title: "That's it!",
      xButton: true
    });

    <?
    }
} // End - if (defined('SHOW_TOUR') && SHOW_TOUR && !$is_staff) {
?>
</script>
