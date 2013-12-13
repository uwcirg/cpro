<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
$surveyLayoutClass = 'survey-layout-regular';
if ($project['ui_small']){
    $surveyLayoutClass = 'survey-layout-small';
}

// Fix for odd spacing of horizontal questions
if ($question['Question']['Orientation'] == "horizontal") {
    echo "<div class=\"q-box $surveyLayoutClass\">";
    if ($question['Question']['ShortTitle']) {
        echo "<h3>" . $question['Question']['ShortTitle'] . "</h3>";
    }
    ?>
    <p class="q-text"><?= $question['Question']['BodyText']; ?></p>
    <?php
} elseif ($question['Question']['Orientation'] == "matrix-top") {
    echo "<div class=\"q-box matrix-container $surveyLayoutClass\">";
    if ($question['Question']['ShortTitle']) {
        echo "<h3>" . $question['Question']['ShortTitle'] . "</h3>";
    }
    ?>
    <div class="q-matrix-top">
    <?= $question['Question']['BodyText']; ?>
    </div>
    <?php
} elseif ($question['Question']['Orientation'] == "matrix") {
    echo "<div class=\"q-box matrix-container $surveyLayoutClass\">";
    ?>
    <div class="q-matrix">
    <?php
    if ($question['Question']['ShortTitle']) {
        echo "<h3>" . $question['Question']['ShortTitle'] . "</h3>";
    }
    echo $question['Question']['BodyText'];
    ?>
    </div>
<?php
} else {
    // Regular formatting for questions
    echo "<div class=\"q-box $surveyLayoutClass\">";

    if ($question['Question']['ShortTitle'] != '') {
        echo "<h3>" . $question['Question']['ShortTitle'] . "</h3>";
    }
    ?>

    <p class="q-text">
    <?= $question['Question']['BodyText']; ?>
    </p>

<?php
// End of fix for horizontal vs regular question formatting
}

  // Check to make sure options isn't empty
  if (isset($question['Option'][0])) {

    //$this->log("question: " . print_r($question, true), LOG_DEBUG);

    $type = $question['Option'][0]['OptionType'];
    
    if ($type == 'combo-check') $type = 'checkbox';
    if ($type == 'combo-radio') $type = 'radio';
    if ($type == 'select' || $type == 'month' || $type == 'day' || $type == 'year') $type = 'select';

    $viewVars = array(/**'htmlHelper' => $html,*/
                      'options'    => $question['Option'],
                      'question'   => $question['Question'],
                      'numQs'      => $numQs,
                      'lastQ'      => $lastQ);
    if(isset($question["Answer"])) {
        $viewVars['answer'] = $question["Answer"];
    }
    echo $this->element($type."_question", $viewVars);
  }

?>

</div>

