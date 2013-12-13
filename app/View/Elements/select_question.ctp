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
$optionTypes = array();
foreach ($options as $option) {
    $optionIds[] = $option['id'];
    $optionTypes[] = $option['OptionType'];
}

echo "<div class='q-container'>";
echo "<div class='q-row'>";

$numOptions = sizeof($options);

// Standard select dropdown
if (in_array("select", $optionTypes)) {

    echo "<select id='$question[id]' name='$question[id]' data-prev='$answer'>";
    echo "<option value=''>Select...</option>";
    foreach ($options as $option) {
        echo "<option value='$option[id]'";
        echo ($answer == $option['id']) ? " selected" : "";
        echo ">$option[BodyText]</option>";
    }
    echo "</select>";

} else {
    // Date select - checks for options and compiles which fields should be included
    $dateType = $maxYear = $minYear = "";
    $optIdM = $optIdD = $optIdY = ""; 
    foreach ($options as $option) {
        if ($option['OptionType'] == 'month') {
            $optIdM = $option['id'];
        }
        if ($option['OptionType'] == 'day') {
            $optIdD = $option['id'];
        }
        if ($option['OptionType'] == 'year') {
            $optIdY = $option['id'];
            if($option['ValueRestriction']) {
                // min and maxYears are stored as comma separated integers in db.
                // 
                // current year. Can either be hard-coded years or begin with
                // + or - to denote diff from current year. For example "-10,+1"
                $checkYears = explode(',', $option['ValueRestriction']);
                $checkMin = substr($checkYears[0], 0, 1);
                $checkMax = substr($checkYears[1], 0, 1);
                if ($checkMin == "-" || $checkMin == "+") {
                    $minYear = date('Y') + $checkYears[0];
                } elseif ($checkMin == "" || $checkMin == "0") {
                    $minYear = date('Y');
                } else {
                    $minYear = $checkYears[0];
                }
                if ($checkMax == "-" || $checkMax == "+") {
                    $maxYear = date('Y') + $checkYears[1];
                } elseif ($checkMax == "" || $checkMax == "0") {
                    $maxYear = date('Y');
                } else {
                    $maxYear = $checkYears[1];
                }
                
            }
        }
    }
    if ($optIdM != "") $dateType .= "M";
    if ($optIdD != "") $dateType .= "D";
    if ($optIdY != "") $dateType .= "Y";
    echo $this->Form->dateTime('dateSelect', $dateType, null, array(
            'style' => 'width: auto',
            'class' => 'required date-select',
            'minYear' => $minYear,
            'maxYear' => $maxYear,
            'name' => $question['id'],
            'id' => array(
                'month' => $question['id'].'-'.$optIdM,
                'day' => $question['id'].'-'.$optIdD,
                'year' => $question['id'].'-'.$optIdY
            ),
            'value' => array(
                'month' => (isset($answer[$optIdM])) ? $answer[$optIdM] : "",
                'day' => (isset($answer[$optIdD])) ? $answer[$optIdD] : "",
                'year' => (isset($answer[$optIdY])) ? $answer[$optIdY] : ""
            ),
            'empty' => array(
                'month' => __('Month...'),
                'day' => __('Day...'),
                'year' => __('Year...')
            )
        ));
}
echo "</div></div>";

//if ($question["Orientation"] == "horizontal") {

?>

<script>
$(function() {
    $("select[id^='<?php echo $question['id'];?>']").selectButtons({
        selected: 'selected',
        page_id: <?php echo $page['id']; ?>,
        lastQ: <?= $lastQ;?>
    });
});
</script>
