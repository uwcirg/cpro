<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

if ($lastQ) $lastQ = 'true';
else $lastQ = 'false';

if(isset($options[0]['class'])) {
    $class = $options[0]['class'];
} else {
    $class = "";
}

//$this->log("checkbox_question.ctp; options:" . print_r($options, true), LOG_DEBUG);

if ($question['Groups'] > 1){
    $numOptions = sizeof($options);
    $numColumns = $question['Groups'];
    $numRows = ceil((float)($numOptions)/(float)($question['Groups']));

    // perhaps this should be a question property...
    $populateColumnsFirst = true;
    //$this->log("checkbox_question.ctp; numOptions:$numOptions; numColumns:$numColumns; numRows:$numRows", LOG_DEBUG);

    echo "<div class=\"q-container multi-col\">\n";
    $optionCount = 0;
    for ($row = 0; $row < $numRows; $row++){
        // $this->log("checkbox_question.ctp; at row:$row", LOG_DEBUG);

        echo "<div class=\"q-row vertical-list\">\n";

        for ($col = 0; $col < $numColumns; $col++){
            //$this->log("checkbox_question.ctp; at col:$col; optionCount:$optionCount", LOG_DEBUG);
            echo "<div class=\"q-option\">\n";
            if ($populateColumnsFirst){
                    $optionToShow = $row + ($numRows * $col);
            }
            else $optionToShow = $optionCount;
            
            if ($optionToShow < $numOptions){

                // $this->log("checkbox_question.ctp; optionCount($optionCount) < numOptions($numOptions), so will echo checkbox option index $optionToShow", LOG_DEBUG);
                $checked = "";
                $comboText = "";
                if (isset($answer)){
                    //$this->log("answer:" . print_r($answer, true), LOG_DEBUG);
                    // answer an Array of options which have been selected, like 4255 => "blah") - note that value is only set for combo-check
                    foreach($answer as $optionId => $comboTxt){
                        if ($options[$optionToShow]["id"] == $optionId){
                            $checked = CHECKED_HTML;
                            $comboText = $comboTxt;
                        }
                    }
                }
                echoCheckboxOption($question, $options[$optionToShow], 
                    $class, $checked, $comboText);
            }// if ($optionCount < $numOptions){
            $optionCount++;
            echo "</div>\n";
        }

        echo "</div>\n";
    }
    echo "</div>\n";
}// if ($question['Groups'] > 1){

else{
    echo "<div class='q-container'>\n"; // table keeps combo txt positioned 
    foreach ($options as $option) {
        echo "<div class='q-row vertical-list'><div class='q-option'>\n";
        $checked = "";
        $comboText = "";
        if (isset($answer)){ 
            //$this->log("answer: " . print_r($answer, true), LOG_DEBUG);
            // answer an Array of options which have been selected, like 4255 => "blah") - note that value is only set for combo-check
            if (array_key_exists($option['id'], $answer)){
                $checked = CHECKED_HTML;
                $comboText = $answer[$option['id']];
            } 
        } 

        echoCheckboxOption($question, $option, $class, $checked, $comboText);
        echo "</div></div>\n";
    }
    echo "</div>\n";
}
?>

<script>
$(function($) {
    $("input:checkbox[id^='<?php echo $question['id'];?>']").checkboxButtons({
        page_id: <?php echo $page['id']; ?>,
        lastQ: <?=$lastQ; ?>
    });
    $("input:text[id^='<?php echo $question['id'];?>']").comboTextInputs({
        page_id: <?php echo $page['id']; ?>
    });
});
</script>
