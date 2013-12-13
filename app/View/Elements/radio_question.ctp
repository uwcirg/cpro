<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

if ($lastQ) $lastQ = 'true';
else $lastQ = 'false';

if(!isset($answer)) {
    $answer = false;
}

$optionIds = array();
foreach ($options as $option) {

    $optionIds[] = $option['id'];
}


if ($question["Orientation"] == "horizontal") {
    
    $width = intval(100 / count($options));
    
    echo "<div class='q-container horizontal-container'>";
    echo "<div class='q-row'>";
    foreach ($options as $option) {
        echo "<div class='q-option radio-horizontal' style='width: ".$width."%'>";
        echo radio_button($question['id'], $option['id'], $optionIds, 
                            $option['OptionType'], $answer, $option['BodyText'], $question["Orientation"]);
        echo "</div>";
    }
    echo "</div></div>";
  
}// if ($question["Orientation"] == "horizontal") {

elseif ($question["Orientation"] == "matrix-top" || $question["Orientation"] == "matrix") {

    echo "<div class='q-container horizontal-container a-matrix'>";
    echo "<div class='q-row'>";
    $width = intval(100 / count($options));
    foreach ($options as $option) {
        echo "<div class='q-option radio-horizontal' style='width: ".$width."%'>";
        echo radio_button($question['id'], $option['id'], $optionIds, 
                            $option['OptionType'], $answer, $option['BodyText'], $question["Orientation"]);
        echo "</div>";
    }
    echo "</div></div>";
    
} else {

  if ($question['Groups'] > 1){
    $numOptions = sizeof($options);
    $numColumns = $question['Groups'];
    $numRows = ceil((float)($numOptions)/(float)($question['Groups']));
    // perhaps this should be a question property...
    $populateColumnsFirst = true;
    echo "<div class=\"q-container multi-col\">\n";
    $optionCount = 0;
    
    for ($row = 0; $row < $numRows; $row++){
        echo "<div class=\"q-row vertical-list\">\n";
        for ($col = 0; $col < $numColumns; $col++){
            if ($optionCount < $numOptions){
                if ($populateColumnsFirst){
                    $optionToShow = $row + ($numRows * $col);
                }
                else $optionToShow = $optionCount;
                echo '<div class="q-option">';
                echo radio_button($question['id'], 
                                    $options[$optionToShow]['id'],
                                    $optionIds,
                                    $options[$optionToShow]['OptionType'],
                                    $answer,
                                    $options[$optionToShow]["BodyText"],
                                    $question["Orientation"]);
                if ($options[$optionToShow]["OptionType"] == 'combo-radio') {
                    // Combo-radio input. Hidden unless its corresponding
                    // answer is already selected
                    $class = " hide";
                    if($answer 
                        && (!in_array($answer, $optionIds) 
                            || ($answer == $options[$optionToShow]['id']))) {
                        $class = "";
                    }

                    echo "<div class='comboTextHolder".$class."'>";
                    echo "<span class='inline-help'><em>";
                    // Check for ancillary text and display it
                    if (
                        isset($options[$optionToShow]['AncillaryText']) and
                        $options[$optionToShow]['AncillaryText']
                    )
                        echo __($options[$optionToShow]['AncillaryText']);
                    else
                        echo __('Please Specify');
                    echo ":</em></span> ";
                    echo "<input class='comboText combo-radio ".$options[$optionToShow]["ValueRestriction"];
                    if ($options[$optionToShow]["ValueRestriction"] == 'numeric') echo " input-mini";
                    echo "' type='text' value='";
                    // If there's already an answer that isn't an ID, show it
                    if(!in_array($answer, $optionIds)) echo $answer;
                    echo "' id='".$question['id']."-".$options[$optionToShow]['id']."-combo' /></div>";
                }
                echo '</div>';
            }
            $optionCount++;
        }
        echo '</div>';
    }
    echo "</div>";
      
  }// if ($question['Groups'] > 1){

  else{
    
    // Just have vertical list
    echo "<div class='q-container'>";
    foreach ($options as $option) {
        echo "<div class='q-row q-vertical-list'><div class='q-option'>";
        echo radio_button($question['id'], $option['id'], $optionIds, 
                            $option['OptionType'], $answer, $option['BodyText'], $question["Orientation"]);
        if ( $option['OptionType'] == 'combo-radio') {
            // Combo-radio input. Hidden unless its corresponding
            // answer is already selected
            $class = " hide";
            if($answer 
                && (!in_array($answer, $optionIds) 
                    || ($answer == $option['id']))) {
                $class = "";
            }

            echo "<div class='comboTextHolder".$class."'>";
            echo "<span class='inline-help'><em>";
            // Check for ancillary text and display it
            if (
                isset($option['AncillaryText']) and
                $option['AncillaryText']
            )
                echo __($option['AncillaryText']);
            else
                echo __('Please Specify');
            echo ":</em></span> ";
            echo "<input class='comboText combo-radio ".$option["ValueRestriction"];
            if ($option["ValueRestriction"] == 'numeric') echo " input-mini";
            echo "' type='text' value='";
            // If there's already an answer that isn't an ID, show it
            if(!in_array($answer, $optionIds)) echo $answer;
            echo "' id='".$question['id']."-".$option['id']."-combo' /></div>";
        }
        echo "</div></div>";
    }
    echo "</div>";
  }

}
/*
Look in main.css for rules about display of radio buttons
and in cpro.jquery.js for the javascript that turns on the radio buttons
and runs the ajax requests. This should be done with native form elements
and an html form with normal post requests, which are then torn out and replaced
with js-based ajax widgets by jQuery for advanced browsers, but that
idea was shot down by our accessibility expert. Sorry, two-thirds of the world.
*/
?>

<script>
$(function() {
    $(".radio-button[id^='<?php echo $question['id'];?>']").radioButtons({
        selected: 'selected',
        page_id: <?php echo $page['id']; ?>,
        lastQ: <?= $lastQ;?>
    });
    $(".radio-buttons label[for^='<?php echo $question['id'];?>']").radioButtonLabels();
    $("input:text[id^='<?php echo $question['id'];?>']").comboTextInputs({
        page_id: <?php echo $page['id']; ?>
    });
});
</script>
