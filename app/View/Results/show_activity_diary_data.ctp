<?php
/**
 * 
 * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
 *
  vars passed from the controller:
  $subscaleName: a string with the name of this subscale
 */
?>

<div class="span2">
    <?php
    echo "<br />";
    echo $this->Html->link("<i class='icon-chevron-left'></i> Return to View My Reports",
            "/results", array('title'=>'Return to View My Reports', 'class' => 'btn btn-mini', 'escape' => false));
    ?>
</div>
<div class="span10">

<?php
echo "<h2>My Reports - Activity and Fatigue Levels</h2>";

// Div with navigation links and title
echo '<div id="graphnav" class="pull-right">View: ';
if ($showAll) {
    echo $this->Html->link('Last Two Weeks', "/results/show_activity_diary_data/$subscaleName", array('class' => "btn btn-small"));
    echo ' <span class="disabled btn btn-small">All Entries</span>';
    $timeperiod = 'All Entries';
    $timesteps = '3';
} else {
    echo '<span class="disabled btn btn-small">Last Two Weeks</span> ';
    echo $this->Html->link('All Entries', "/results/show_activity_diary_data/$subscaleName/all", array('class' => "btn btn-small"));
    $timeperiod = 'Last Two Weeks';
    $timesteps = '1';
}
echo '</div>';
?>

<!-- Highcharts JS -->
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
                text: 'Activity and Fatigue Levels - <?php echo $timeperiod ?>'
            },
            subtitle: {
                text: 'Based on your diary entries'
            },
            xAxis: [{
                    type: 'datetime',
                    labels: {
                        formatter: function() {
                            return Highcharts.dateFormat('  %b. %e', this.value);
                        },
                        step: <?php echo $timesteps ?>,
                        align: 'left'
                    }
                }],
            yAxis: [{ // Primary yAxis
                    labels: {
                        formatter: function() {
                            return this.value +'';
                        },
                        style: {
                            color: '#9ea58e'
                        }
                    },
                    title: {
                        text: 'Activity Duration (minutes)',
                        style: {
                            color: '#9ea58e',
                            backgroundColor: '#fff'
                        }
                    },
                    opposite: true,
                    min: 0,
                    max: 50                    
                }, { // Secondary yAxis
                    gridLineWidth: 0,
                    title: {
                        text: 'Fatigue Level',
                        style: {
                            color: '#AA4643',
                            backgroundColor: '#fff'
                        }
                    },
                    labels: {
                        formatter: function() {
                            return this.value +'';
                        },
                        style: {
                            color: '#AA4643'
                        }
                    },
                    categories: ['0','1','2','3','4','5','6','7','8','9','10'],
                    min: 0,
                    max: 10                     
                }, { // Tertiary yAxis
                    gridLineWidth: 0,
                    title: {
                        text: 'Number of Steps',
                        style: {
                            color: '#4572A7',
                            backgroundColor: '#fff'
                        }
                    },
                    labels: {
                        formatter: function() {
                            return this.value +' steps';
                        },
                        style: {
                            color: '#4572A7'
                        }
                    },
                    opposite: true,
                    min: 0,
                    max: 5000                     
            }],  //yAxis
            tooltip: {
                crosshairs: true,
                shared: true,
                backgroundColor: 'rgba(255,255,255,1)',
                formatter: function() {
                    var s = '<b>' + Highcharts.dateFormat('%A, %B %e %Y', this.x) + '</b>';
                                    
                    $.each(this.points, function(i, point) {
                        s += '<br/><span style="color: '+this.series.color+'; font-weight: bold">'+ point.series.name +'</span>: '+
                            Highcharts.numberFormat(point.y, 0, ',');
                    });
                                    
                    return s;
                }
            },
            legend: {
                x: 60,
                y: 0,
                layout: 'vertical',
                align: 'left',
                verticalAlign: 'top',
                floating: true,
                backgroundColor: '#FFFFFF',
                reversed: true,
                symbolWidth: 35
            },
            plotOptions: {
                column: {
                    <?php
                    // no padding between columns if in "All Entries" view
                    if ($showAll) {
                        echo 'groupPadding: 0,';
                    }
                    ?>
                    minPointLength: 3,
                    pointPadding: 0,
                    fillOpacity: 0.1
                    //states: {
                    //    hover: {
                    //        brightness: 0.5
                    //    }
                    //}
                },
                area: {
                    fillOpacity: 0.9,
                    marker: {
                        radius: 2
                    }
                }
            },
            series: [
            <?php if($strings_data['minutes'] != ']') { ?>
            {
                name: 'Activity Duration',
                color: 'rgba(0,0,0,0.3)',
                type: 'column',
                data: <?php echo $strings_data['minutes']; ?>
            },
            <?php
            }
            if($strings_data['steps'] != ']') {
            ?>
            {
                name: 'Number of Steps',
                <?php
                // change line type based on view
                if ($showAll) {
                    echo "type: 'spline',";
                } else {
                    echo "type: 'line',";
                }
                ?>
                color: '#4572A7',
                yAxis: 2,
                data: <?php echo $strings_data['steps']; ?>,
                dashStyle: 'shortdot'                   
            },
            <?php
            }
            if($strings_data['fatigue'] != ']') {
            ?>
            {
                name: 'Fatigue Level',
                color: '#AA4643',
                <?php
                // change line type based on view
                if ($showAll) {
                    echo "type: 'spline',";
                } else {
                    echo "type: 'line',";
                }
                ?>
                yAxis: 1,
                data: <?php echo $strings_data['fatigue']; ?>      
            }
            <?php
            }
            ?>
            ] // series
        });
        
    });
        
</script>

<!-- Chart container -->
<div id="container" style="height: 400px; margin: 0 auto"></div>

<br />

<div><strong>My Results - <?php echo $timeperiod ?></strong></div>

<ul>
    <?php
    // My Results stats - Near duplicate of summation on main Activity Diary.
    if ($showAll) {
        // If "All Entires"
        $total = 0;
        $denom = 0;
        foreach ($entries as $entry) {
            if ($entry['ActivityDiaryEntry']['fatigue'] != '') {
                $denom += 1;
            }
            $total += $entry['ActivityDiaryEntry']['fatigue'];
        }
        if ($denom > 0) {
            echo '<li>Average fatigue level: ';
            echo round($total / $denom, 1);
            echo '</li>';
        }
        $total = 0;
        $denom = 0;
        foreach ($entries as $entry) {
            if ($entry['ActivityDiaryEntry']['minutes'] != '') {
                $denom += 1;
            }
            $total += $entry['ActivityDiaryEntry']['minutes'];
        }
        if ($denom > 0) {
            echo '<li style="margin-top: 8px">Average minutes exercised per day: ';
            echo number_format($total / $denom);
            echo '</li>';
        }
        echo '<li>Total exercise time: ';
        echo number_format($total);
        echo ' minutes';
        if ($total > 180) {
            echo ' - that\'s more than ';
            echo floor($total / 60);
            echo ' hours!';
        }
        echo '</li>';
        $total = 0;
        $denom = 0;
        foreach ($entries as $entry) {
            if ($entry['ActivityDiaryEntry']['steps'] != '') {
                $denom += 1;
            }
            $total += $entry['ActivityDiaryEntry']['steps'];
        }
        if ($denom > 0) {
            echo '<li style="margin-top: 8px">Average steps per day: ';
            echo number_format($total / $denom);
            echo '</li>';
        }
        echo '<li>Total number of steps taken: ';
        echo number_format($total);
        if ($total > 10000) {
            echo ' - that\'s approximately ';
            echo round($total / 2000, 1);
            echo ' miles!';
        }
        echo '</li>';
    } else {
        // If "Last Two Weeks"
        $total = 0;
        $denom = 0;
        foreach ($entries as $entry) {
            if ($entry['ActivityDiaryEntry']['fatigue'] != '') {
                $denom += 1;
            }
            $total += $entry['ActivityDiaryEntry']['fatigue'];
        }
        if ($denom > 0) {
            echo '<li>Average fatigue level: ';
            echo round($total / $denom, 1);
            echo '</li>';
        }
        $total = 0;
        $denom = 0;
        foreach ($entries as $entry) {
            if ($entry['ActivityDiaryEntry']['minutes'] != '') {
                $denom += 1;
            }
            $total += $entry['ActivityDiaryEntry']['minutes'];
        }
        if ($denom > 0) {
            echo '<li>Average minutes exercised per day: ';
            echo number_format($total / $denom);
            echo '</li>';
        }
        $total = 0;
        $denom = 0;
        foreach ($entries as $entry) {
            if ($entry['ActivityDiaryEntry']['steps'] != '') {
                $denom += 1;
            }
            $total += $entry['ActivityDiaryEntry']['steps'];
        }
        if ($denom > 0) {
            echo '<li>Average steps per day: ';
            echo number_format($total / $denom);
            echo '</li>';
        }
    } // End "My Results" stats
    ?> 
</ul>


<p>View the <?php echo $this->Html->link("Manage My Fatigue page", "/teaching/manage_fatigue"); ?> for tips on staying active, keeping a diary and using a pedometer.</p>

</div>
