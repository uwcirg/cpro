<?php
/**
 * 
 * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
 *
 */
// colorPalette uses default color scheme of Highcharts. Need to make an array
// because normally the colors are used for multiple lines on a single graph.
// In this case we want different colors for each graph.
$colorPalette = array(0 => '#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92');

// Used to count the single subscale to group them on a single line.
// This could be problematic if the single subscales aren't all adjacent in the
// array.
$singleSubscales = 0;
foreach ($scales as $scale) {

    $subscales = $scale["Subscale"];
    $scale = $scale["Scale"];
    
    if (is_array($subscales)) {
        // Count subscales for use in layout
        $subscalesCount = count($subscales);
        
        // Counter to find when to start a new row if there are more than
        // 3 subscales.
        $countForTableRows = 0;
        
        // For use with the scales with a single subscale
        if ($subscalesCount == 1) {
            if ($singleSubscales == 0) {
                echo "<div class='row'>";
            }
            echo "<div class='span3'>";
        }
        echo "<fieldset>";
        echo "<legend>$scale[name]</legend>";
        foreach (array_keys($subscales) as $i) {
            $area = $subscales[$i]["name"];
            $subscaleData = $subscales[$i]['data'];

            // match eg [1311922800000,66.6667]
            $matchCount = preg_match_all(
                    '(\[([0-9]+),([0-9]+)((\.([0-9]*))?)\])', $subscaleData, $matches);
            //echo "preg_matches (matchCount = $matchCount)";
            // json_decode would have been great, 
            //  but sometimes we (need to?) use invalid json 
            //$json = json_decode($subscaleData);
            $base = $subscales[$i]['base'];
            $max = $subscales[$i]['range'] + $base;

            if ($countForTableRows % 3 == 0) {
                echo "<div class='row'>\n";
            }
            echo "<div class='span3' id='" . $subscales[$i]['id'] . "'>";
            // Set links for sparklines if matchcount > 1
            if ($matchCount > 1) {
                if ($forAssociateView) {
                    echo "<a href='" .
                    Helper::url('/results/showToOthers/');
                } else {
                    if (is_numeric($subscales[$i]['id'])) {
                        echo "<a href='results/show/";
                    } else {
                        echo "<a href='results/show_activity_diary_data/";
                    }
                }
                echo $subscales[$i]['id'] . "'>";
                echo "<div class='sparkline'>";
                echo "<div id='chart" . $subscales[$i]['id'] . "'></div>";
                echo "</div>";
                echo "</a>";
            } else { 
            // Otherwise display text only info and link
                echo "<div class='h4-para-head'>";
                // Create links
                if ($forAssociateView) {
                    echo "<a href='" .
                    Helper::url('/results/showToOthers/');
                } else {
                    if (is_numeric($subscales[$i]['id'])) {
                        echo "<a href='results/show/";
                    } else {
                        echo "<a href='results/show_activity_diary_data/";
                    }
                }
                echo $subscales[$i]['id'] . "'>";
                echo "<h4>" . $subscales[$i]["name"] . "</h4></a>";
                if (!is_numeric($subscales[$i]['id'])) {
                    // activity diary -related
                    if ($matchCount == 0) {
                        echo "<p>Not reported over the past two weeks. Go to <a href='" . Helper::url('/activity_diaries') . "'>your Activity Diary</a> to fill in this week’s entries.</p>";
                    } elseif ($matchCount == 1) {
                        echo "<p>Only reported once over the past two weeks. Go to <a href='" . Helper::url('/activity_diaries') . "'>your Activity Diary</a> to fill in this week’s entries.</p>";
                    }
                } else {
                    // not activity diary -related
                    if ($matchCount == 0) {
                        echo "<p>Not reported yet</p>";
                    } elseif ($matchCount == 1) {
                        // eg [[1311922800000,66.6667]]
                        $point = $matches[0][0]; // eg [123,5.67890]
                        $commaPos = strpos($point, ','); // eg 4
                        $point = substr($point, $commaPos + 1, strlen($point) - $commaPos - 2);
                        echo "<p>Reported once at a level of <strong>" . round($point) . " out of " . $max . "</strong> where " . $base . " is better and " . $max . " is worse. After you report your experience a 2nd time, a graph will be shown. ";
                        if ($forAssociateView) {
                            echo "<a href='" .
                            Helper::url('/results/showToOthers/' .
                                    $subscales[$i]['id']) .
                            "'>";
                        } else {
                            if (is_numeric($subscales[$i]['id'])) {
                                echo "<a class='no-underline' href='results/show/"
                                . $subscales[$i]['id'] . "'>";
                            } else {
                                echo "<a class='no-underline' href='results/show_activity_diary_data/"
                                . $subscales[$i]['id'] . "'>";
                            }
                        }
                        echo "Find out more.</a></p>";
                    }
                }// not activity diary -related
                echo "</div>"; // Close text-only
            }
            echo "</div>\n";

            // Calculate when to start a new row
            if ($countForTableRows % 3 == 2
                    || $countForTableRows == count($subscales) - 1) {
                echo "</div>\n";
            }
            $countForTableRows++;
        }// foreach(array_keys($subscales) as $i) {
        echo "</fieldset>";
        // For use with the scales with a single subscale
        if ($subscalesCount == 1) {
            echo "</div>";
            if ($singleSubscales == 2) {
                echo "</div>";
            }
            // Iterate the singSubscales count to properly layout containers
            // FIXME - If the # of scales in a row with only one subscale is not
            // equal to three, there will be some issues with the layout. 
            // Currently not 
            $singleSubscales++;
        }
    }
}
?>    

<script>
// setOptions for all Highcharts graphs on page
Highcharts.setOptions({
    chart: {
        type: 'line',
        spacingTop: 5,
        spacingLeft: 7,
        spacingRight: 7,
        borderWidth: 1,
        borderColor: '#ccc'
    },
    title: {
        margin: 10
    },
    xAxis: {
        type: 'datetime',
        labels: {
            enabled: false
        },
        title: null,
        lineWidth: 0,
        minorGridLineWidth: 0,
        lineColor: 'transparent',
        minorTickLength: 0,
        tickLength: 0
    },
    yAxis: {
        labels: {
            enabled: false
        },
        title: null,
        endOnTick: false,
        startOnTick: false
    },
    legend: {
        enabled: false                    
    },
    credits: {
        enabled: false
    },
    tooltip: {
        enabled: false
    },
    plotOptions: {
        series: {
            animation: false,
            states: {
                hover: {
                    enabled: false
                }
            },
            marker: {
                radius: 2
            },
            connectNulls: true,
            lineWidth: 2,
            shadow: false
        }
    }
});
// Create a sparkline-style chart for each measure
$(document).ready(function() {
<?php
// FIXME - Re-using the same foreach as above. Would be more efficient to combine.
foreach ($scales as $scale) {
    $subscales = $scale["Subscale"];
    $scale = $scale["Scale"];
    if (is_array($subscales)) {
        foreach (array_keys($subscales) as $i) {
            $matchCount = preg_match_all(
                    '(\[([0-9]+),([0-9]+)((\.([0-9]*))?)\])', $subscaleData, $matches);
            // Changes blanks to null in data (blank causes problems in Highcharts
            $chartData = str_replace(",]", ",null]", $subscales[$i]['data']);
            // Sets min/max with a little extra space - needed so that points at
            // min/max value aren't cut off a bit.
            $subscaleRange = $subscales[$i]['range'] + $subscales[$i]['base'];
            if ($subscaleRange > 11 ) {
                $subscaleSparkMin =  -($subscales[$i]['base'] + 2);
                $subscaleSparkMax = $subscaleRange * 1.1;
            } else {
                $subscaleSparkMin =  -($subscales[$i]['base'] + .2);
                $subscaleSparkMax = $subscaleRange + .2;
            }
            // Set how often yAxis lines should appear varies by scale. Called
            // with tickInterval
            if ($subscales[$i]['range'] == 3) {
                $subscaleSparkTicks = 1;
            } elseif ($subscales[$i]['range'] == 6) {
                $subscaleSparkTicks = 2; 
            } elseif ($subscales[$i]['range'] == 27) {
                $subscaleSparkTicks = 9; 
            } else { 
                $subscaleSparkTicks = $subscales[$i]['range'] / 2;
            }
            
            // Now output the highchart with these variables
            // Re-use $matchCount from above to only output charts when there
            // is more than one data point.
            if ($matchCount > 1) {
?>
    chart<?php echo $subscales[$i]['id']?> = new Highcharts.Chart({
        chart: {
            renderTo: "chart<?php echo $subscales[$i]['id']?>"
        },
        title: {
            text: '<?php echo $subscales[$i]["name"] ?>'
        },
        yAxis: {                        
            min: <?php echo $subscaleSparkMin ?>,
            max: <?php echo $subscaleSparkMax ?> ,
            tickInterval: <?php echo $subscaleSparkTicks ?>,
            plotBands: [{
                color: "#ff9999",
                from: <?php
                    if ($subscales[$i]['id'] == SEXUALITY_SUBSCALE){
                        echo "79";
                    } else {
                        echo ($scale['critical'] > 0 ? $scale['critical'] : $subscales[$i]['range'] + $subscales[$i]['base']);
                    }?>,
                to: <?php echo $subscales[$i]['range'] + $subscales[$i]['base'] ."\n"; ?>
            }]
        },
        series: [{
            data: <?php echo $chartData ?>,
            color: '<?php echo $colorPalette[$i] ?>'
        }]
    })
<?php
        
            } // End if ($matchCount > 1) {
        } // End foreach (array_keys($subscales) as $i) {
    } // End if (is_array($subscales)) {
} // End foreach ($scales as $scale) {
?>
});
// Adds subtle hover effect to charts
$(".sparkline").mouseenter(function(){
    $(this).find('rect').attr('fill','#F7F9F9');
}).mouseleave(function(){
    $(this).find('rect').attr('fill','#fff')
})
</script>

