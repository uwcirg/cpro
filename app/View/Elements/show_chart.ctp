<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * vars passed from the controller:
	 $subscaleId : an int: the subscale id for the current page
	 $subscaleData : string of javascript data for this subscale
	 $subscaleName: a string with the name of this subscale
	 $journalEntries: an array of entries, each with keys text and displayDate
*/

echo $this->element('resultsdateseln',
                            array("chartsDisplayed" => 
                                    true)); ?>
<?php if($showCharts) { ?>
<div class="spacer"> </div>
<div id="customize-chart">
  <fieldset>
    <legend>Customize chart</legend>
    <div id="graph-legend">
    </div>
<?php
    if (!$forAssociate){
        echo "Remember...more symptoms are available under ";
        print $this->Html->link('View My Reports',
                    '/results');
    }
    else{
        echo "Remember...more symptoms may be available under ";
        print $this->Html->link('View Reports',
                    '/results/othersReportsList/' . $patient['Patient']['id']);
    }
?>
  </fieldset>
</div>

<div id="graph"> 
  <ul>
    <li>
    <?php
        echo $this->Html->link(
            $this->Html->image("graph_line.gif",
                array('style'=>'border:0px solid #222',
                    'alt' => "Line graph")),
                '#graph-line',
                array('escape' => false), false);
    ?>
    </li>
    <li>
    <?php
        echo $this->Html->link(
            $this->Html->image("graph_bar.gif",
                array('style'=>'border:0px solid #222',
                    'alt' => "Bar graph")),
                '#graph-bar',
                array('escape' => false), false);
    ?>
    </li>
    <li>
    <?php
        echo $this->Html->link(
            $this->Html->image("graph_table.gif",
                array('style'=>'margin: 0px 5px; border:0px solid #222',
                    'alt' => "Table")),
                '#graph-table',
                array('escape' => false), false);
    ?>
    </li>
    </ul>
<?php if(isset($scaleCritical) && $scaleCritical) { ?>
    <div style="margin: 0px 10px 5px; font-size: 0.95em">Shaded area may indicate a higher level of symptom distress</div>
<?php } ?>
    <div id='graph-line'>
        <a name="graph-line"></a>
        <div class="placeholder" id="line-placeholder">
        </div>
        <br style="clear:both;">
    </div>
    <div id='graph-bar'>
        <a name="graph-bar"></a>
        <div class="placeholder" id="bar-placeholder">
        </div>
        <br style="clear:both;"/>
    </div>
    <div id='graph-table' style="overflow:scroll">
        <a name="graph-table"></a>
        <table class="placeholder" id="table-view" style="margin-top:10px">
            <tbody>
            </tbody>
        </table>
      <br style="clear:both;"/>
    </div>
    <br style="clear:both;"/>&nbsp;
</div>

<?php } 
    echo $this->element('journals',
        array("forAssociate" => $forAssociate));
?>
<script id="source" language="javascript" type="text/javascript">

jQuery(function($) { 
    // Setup UI

    $("div#text").tabs();
    $("div#graph").tabs();

    $("#result-coach-popouts a").click(function() {
        $(this).parent()
            .find("span.hidden").toggle().end()
            .find("span.shown").toggle();
    });

    // Create controller instances with appropriate options
<?php if($forAssociate) { ?>
    var forAssociate = true;
<? } else { ?>
    var forAssociate = false;
<?php } ?>

<?php if($showCharts) { ?>
        $.extend({
            charts: new ChartsController(
                // options    
                {
                    lineGraph: "#graph-line",
                    barGraph:  "#graph-bar",
                    table:     "#graph-table"
                }, 
                // metaData
                {
                    originalSubscale: <?php echo $subscaleId; ?>,
                    min: <?php echo $subscaleMin; ?>,
                    max: <?php echo $subscaleMax; ?>,
                    critical: <?php if(isset($scaleCritical)) { echo $scaleCritical; } else { echo "0"; }?>
                })
        })
        $.extend({
            subscales: new SubscalesController({callbacks: [$.charts] })
        });
<?php } ?>
    
    $.extend({
        journals: new JournalsController(
            { 
                baseUrl: "<?php echo Router::url("/journals/"); ?>",
                patientId: <?php echo $patient['Patient']['id']; ?>,
                editable: !forAssociate 
            }
        )
    });

    // Add controller callbacks with new instances
    $.extend({  
        dates:  new DatesController({callbacks: [$.charts, $.journals]})
    })

    // All components created and linked: initialize and add dom elements
    $([$.journals, $.charts, $.subscales, $.dates]).each(function(i, item) {
        if(item && item.initialize) {
            item.initialize();
        }
    });

});

</script>
