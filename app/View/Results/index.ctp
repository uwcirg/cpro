<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div class="span10 offset2">
    
    <h2>View My Reports</h2>

    <p>Below is a list of different symptoms and quality of life issues that were included in your report. There is a small graph for each of these showing change over time. Click on any small graph to see a larger version and to learn more about the symptom or issue and what you can do about it. A symptom or issue in the red requires attention.</p>

    <?php echo $this->element('scale_thumbnails',
        array("forAssociateView" => false)); ?>
    
</div>
