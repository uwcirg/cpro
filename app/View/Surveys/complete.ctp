<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<h2><?=__('Thank you')?></h2>

<p><?=__("Touch or click 'Complete' below to record your answers and finish your report. ")?>
<?php
// render on this is called from the subsitution fxn "complete", so the "show" action controller code is called for this, but complete action code is not
if (isset($sessionType)){
    if ($sessionType != 'T1' && $treatment ){
        echo __("Following this, you may be presented with teaching materials about areas where your answers indicate you may be having problems") . "\n";
    }
}
?>
</p>


        <div style="text-align: center"><?php echo $this->Html->link(__("Complete"), "complete/2", array('class'=>'btn btn-primary btn-large calcInit','style'=>'margin: 40px')); ?></div>
        <br />

<?php 
echo $this->element('session_complete_gate');


echo "<div id='survey-bottom-bar' class='survey'>";
echo "<div id='survey-bottom-center'>";
echo $this->Html->link('<i class="icon-chevron-left"></i>' . __('Previous'), $back_link, array('class' => 'btn', 'style' => 'margin: 6px 6px 0 0; float: left', 'id' => 'prev-arrow-link', 'title' => __('Previous Page'), 'escape' => false), false);
?>
        <div id="progress">
            <div id="progress-bar" style="width:
<?php 
    echo ((int)(1 * ( // completed survey
                344 // shell width
                - 9 // shell opening starts 9 px in
                - 7 // shell opening ends 9 px from right
        ))); 
    ?>px;"></div>
<?php
  echo $this->Html->image("progress-bar-shell.png",
      array(//'style' => 'float:left',
            'id' => "progress-bar-shell",
            'border'=>'0'))
?>
        </div>
               
        </div>
                      

<?php

echo "<div style='display:block;clear:both'>&nbsp;</div>";
echo "</div>";
echo "</div>";
 
?>
