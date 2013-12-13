<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<!-- start_pdf_no -->
<div class="intervention-sidebar" data-spy="affix"  data-offset-top="165">
    <div id="navbar">

    <?php 
        foreach($quick_links as $subcat => $links) {
            echo "<div class='section'>".$subcat.":</div>";
            echo '<ul class="nav nav-list">';
            foreach($links as $text => $link) {
                $link = $this->Html->link($text, $link);
                echo "<li>".$link."</li>";
            } 
            echo '</ul>';
        } 
    ?>
        
    </div>
</div>
<!-- end_pdf_no -->
