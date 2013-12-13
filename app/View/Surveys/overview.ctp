<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

    <div class="span2 visible-desktop">
    </div>
    <div class="span10">

    <h2>Editors for assessments and other content</h2>
    
    <p>Use the following links to directly edit content from this application's database.</p><br />

    <?php

        foreach($projects as $project){

            $title = $project["Project"]["Title"];
            $id = $project["Project"]['id'];

            print $this->Html->link("View and Edit Assessment for \"$title\" (project $id)", '/surveys/summary/' . $id . '/all', array('escape' => false, 'class' => 'btn btn-large btn-primary'));
            echo "<br /><br />";

        }

        if (in_array('p3p_teaching', Configure::read('modelsInstallSpecific'))){
            print $this->Html->link("Edit P3P Teachings", '/p3p/overview/', array('escape' => false, 'class' => 'btn btn-large btn-primary'));
            echo "<br /><br />";
        }
        
    ?>
    
    

    </div>




