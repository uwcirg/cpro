<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<h2>Teaching Tips</h2>
<h3><?php 
if (isset ($teaching_tip["Subscale"]["TeachingTip"]["title"])){
    echo $teaching_tip["Subscale"]["TeachingTip"]["title"]; 
}
else echo $teaching_tip["Subscale"]["name"]; 
?></h3>
<?php echo $teaching_tip["Subscale"]["TeachingTip"]["text"]; ?>
 
<div id='survey-bottom-bar' class='survey'>
<div id='survey-bottom-center'>
<?
   echo $this->Html->link(__('Next Page'), 
                    "/surveys/show/". TEACHING_TIPS_PAGE, 
                    array('class' => 'button-link next-button', 
                            'id' => 'next-arrow-link', 
                            'title' => 'Next Page', 
                            'escape' => false), 
                    false);
?>
</div>
<script>
$(function() {
    $.enableTips();
});
</script>
