<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

// Displays note visible to patient
if (!empty($note)) {
    echo "<br /><h4>". __('A Message for You:') ."</h4>";
    echo "<p>{$note['text']}</p>";
}
?>
        
<?php
// "Quick Links" section layout (and content?) deprecated. Removing for now.
?>
<!--
       <h2>Quick Links</h2>

        <ul>
   <?php 
    foreach($quick_links as $text => $link) {
        if (strpos("surveys", $link) >= 0) {
            // avoids errant extra sessions
            $link = $this->Html->link($text, $link, 
                        array('class'=>'session_launch',
                                'escape' => false));
        }
        else {
            $link = $this->Html->link($text, $link);
        }
        echo "<li>$link</li>";
    } 

//    if (isset($treatment) && ($treatment == true)){
//       echo "<li><a href=\"" . INSTRUCTIONAL_MOVIE_URL . "\">See what " . SHORT_TITLE . " can do</a></li>"; 
//
//    }
    ?>

        </ul>

    </div>

</div>
-->