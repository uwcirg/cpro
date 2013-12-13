<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
     vars passed from the controller:
	 $subscaleId : an int: the subscale id for the current page
	 $subscaleData : string of javascript data for this subscale
	 $subscaleName: a string with the name of this subscale
     $journalEntries: an array of entries, each with keys text and displayDate
     $teachingTip: array for teaching tip
*/
// Creating color array - uses 5 colors families, each with light and dark version. Light iterates first, then dark.
$colorPalette = array(0 => '#A6Cee3', '#b2df8a','#fdbf6f','#cab2d6','#dfc27d','#1f78b4','#33a02c','#ff7f00','#6a3d9a');
?>

<div class="span2">
    <?php
    echo "<br />";
    echo $this->Html->link("<i class='icon-chevron-left'></i> Return to View My Reports",
            "/results", array('title'=>'Return to View My Reports', 'class' => 'btn btn-mini', 'escape' => false));
    if (!empty($surveyLink)) {
        echo "<br /><br />";
        echo $this->Html->link("What question did I answer?",
                $surveyLink, array('class' => 'btn btn-small', 'escape' => false));
    }
    ?>
</div>
<div class="span10">
    
    <h2><?php echo $subscaleName ?></h2>

<?php
// Control text at top of page based on # of surveys completed.
for($i = 0; $i < count($subscalesData); $i++) {
    if ( $subscaleId == $subscalesData[$i]["id"] ) {
        $matchCount = preg_match_all(
            '(\[([0-9]+),([0-9]+)((\.([0-9]*))?)\])', 
            $subscalesData[$i]['data'], 
            $matches);
        if ($matchCount > 1) {
        ?>

<p>This chart is based on your answers to the "Report My Experiences" questions. Scroll down to <a href="#learn-more">learn more</a> about <?php echo $subscaleName ?>.</p>

<?php
/*
 * Highcharts display of experienes.
 * 
 * Notes:
 * Try moving graph to top (Highcharts graph) with all elements for a particular scale included. Visibility is false for all but the selected, but others can be added. 
 * Moving "Teaching Tips" below chart. Consider renaming. 
 * Removed Date Range selector. Due to limited number of data points, just display the entire range.
 * Consider changing how $matchCount = 1 is displayed (currently just a single point on graph.
 * 
 * Displays when $matchCount is > 0.
 */

?>

<!-- 1a) Optional: add a theme file -->
<!--
    <script type="text/javascript" src="../js/themes/gray.js"></script>
-->

<!-- 1b) Optional: the exporting module -->
<!--script type="text/javascript" src="js/modules/exporting.js"></script-->
    
    
<!-- 2. Add the JavaScript to initialize the chart on document ready -->
<script type="text/javascript">
    
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container'
            },
            credits: {
                    enabled: false
            },
            title: {
                text: 'Charting Your Experiences<?php // echo $subscaleName ?>',
                <?php
                if ( count($subscalesData) > 1 ) {
                ?>
                margin: 50
                <?php
                } else {
                ?>
                margin: 20,
                style: {
                    lineHeight: '20px'
                }
                <?php
                }
                ?>
            },
            <?php if ( count($subscalesData) > 1 ) { ?>
            subtitle: {
                text: 'Add other responses to the chart by clicking on the names to the right.<br><br>Points in the red shaded area may indicate a higher level of symptom distress.'
            },						
            <?php } ?>
            xAxis: [{
                type: 'datetime',
                lineColor: '#ffffff',
                labels: {
                    formatter: function() {
                            return Highcharts.dateFormat('%b. %e', this.value);
                    }
                }
            }],
            yAxis: { // Primary yAxis
                labels: {
                    style: {
                        color: '#9ea58e'
                    }
                },
                title: {

<?php
    $yBottomLabel = 'Better';
    $yTopLabel = 'Worse';
    if (isset($scaleInverse) && ($scaleInverse == 1)){
        $yBottomLabel = 'Worse';
        $yTopLabel = 'Better';
    }
?>

                    text: '<br><?=$yTopLabel;?><br><span style="color: white; ">|</span><br><span style="color: white; ">|</span><br><span style="color: white; ">|</span><br><span style="color: white; ">|</span><br><span style="color: white; ">|</span><br><?=$yBottomLabel;?>',
                    style: {
                        color: '#9ea58e',
                        backgroundColor: '#fff',
                        fontSize: '13px',
                        lineHeight: '36px'
                    },
                        margin: 30,
                        rotation: 0,
                        align: 'high'
                },
                <?php
                if ( $subscaleMax > 11 ) {
                    echo "min: -($subscaleMin + 2),";
                    echo "max: ($subscaleMax * 1.1),";
                } else {
                    echo "min: -$subscaleMin.2,";
                    echo "max: $subscaleMax.2,";
                }
                if ( $scaleCritical != '0' ) { ?>									
                plotBands: [{
                    color: '#ffd9d9',
                    from: <?php echo $scaleCritical ?>,
                    to: <?php echo  $subscaleMax ?>
                }],
                <?php } ?>
                allowDecimals: true,
                endOnTick: false,
                startOnTick: false,
                showLastLabel: false,
                showFirstLabel: false,
                tickPixelInterval: 
                <?php
                if ( $subscaleMax > 9 ) {
                    echo '50';
                } else {
                    echo '80';
                }
                ?>
            },  //yAxis
            tooltip: {
                crosshairs: true,
                shared: true
            },
            legend: {
                layout: 'vertical',
                <?php
                if ( count($subscalesData) < 6 ) {
                    echo "backgroundColor: '#FFFFFF',\nsymbolWidth: 35,";
                } else { 
                    echo "backgroundColor: 'rgba(255,255,255,0.8)',\nsymbolWidth: 35,";
                } ?>
                floating: true,
                y: 0,
                verticalAlign: 'top',
                align: 'right'
//				itemStyle: {
//					width: '150px !important'
//					lineHeight: '40px',
//					height: '40px',
//					fontSize: '24px'
//              }
            },
            plotOptions: {
                series: {
                    marker: {
                        radius: 5
                    },
                    visible: false
                }
            },
            series: [
						<?php
						for($i = 0; $i < count($subscalesData); $i++) {
							echo "{";
							echo "name: '";
							echo $subscalesData[$i]["name"];
							echo "',";
							echo "color: '";
							echo $colorPalette[$i];
							echo "',";
							echo "type: 'line',";
							if ( $i > 4 ) {
								echo "dashStyle: 'shortDot',";
							}
							if ( $subscaleId == $subscalesData[$i]["id"] ) {
                                                            echo "visible: true,";
							}
							echo "data: [";
							echo $subscalesData[$i]['data'];
							echo "]";
							echo "},";
						}
						?>
            ]
        });
        
    });
        
</script>
    
<!-- 3. Add the container -->

<div id="container" style="height: 400px; margin: 0 auto"></div>
<a name="learn-more"></a>

<?php
// End Highcharts graph

        }// End $matchCount > 1

        if ($matchCount == 1) {

            $point = $matches[0][0]; // eg [123,5.67890]
            $commaPos = strpos($point, ','); // eg 4
            $point = substr($point, 
                $commaPos + 1,
                strlen($point) - $commaPos - 2);
            echo "<p>You have reported your experiences once at a level of <strong>$point out of $subscaleMax</strong> for $subscaleName  where " . $subscaleMin ." is better and " . $subscaleMax . " is worse.</p>
            <p>After you report your experience for a second time, you'll be able to see a chart where you can see how your experiences change over time.</p>";
            
        } 
        if ($matchCount == 0) {
            echo "<p>You have not reported your experiences yet for this category.</p>";
        }
    }
}
// Grab final value for this subscale
$criticalValue = False;
for($i = 0; $i < count($subscalesData); $i++) {
    if ( $subscaleId == $subscalesData[$i]["id"] ) {
         $s = $subscalesData[$i]['data'];
        $matches = array();
        $t = preg_match('/,(.*?)\]/s', $s, $matches);
        // If that final value is higher than the critical, then a class is
        // added to #chart-tips below so that .survey-intro will be
        // displayed.
        if ($matches[1] > $scaleCritical ) {
            $criticalValue = True;
        }
    }
}
?>

<h3>Learn More - <?php echo $subscaleName; ?></h3>

<div id="<?php echo $siteId; ?>" class="site-id hidden"></div>

<fieldset class="subscale " id="<?php echo $subscaleId; ?>">

    <div id="<?php if ($criticalValue) { echo "critical-value"; } else { echo "normal"; } ?>" class="teaching-tips">
    <?php
    if (isset($teachingTip)) { echo $teachingTip["text"]; }
    ?>
    </div>
    
</fieldset>

    
<script>
$(function() {
    $.enableTips();
});
</script>

</div>
