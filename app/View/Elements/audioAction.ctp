<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

    if (!empty($link)) {
        if ($confirm) {
            echo $this->Html->link($anchor, $link, array(), 'Are you sure?');
        } else {
            echo $this->Html->link($anchor, $link);
        }
    } else if (strpos($anchor, 'Upload ') === 0) {
        echo "$anchor<br/>";
        echo $this->element('audioUpload',
                            array('patientId' => $patientId,
                                  'confirm' => $confirm,
                                  'initialUpload' => false));
    } else {
        echo $anchor;
    }
?>

