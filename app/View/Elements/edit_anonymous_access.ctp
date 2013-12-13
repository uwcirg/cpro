<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
    $webkey = array_shift(Hash::extract(
        $this->request->data,
        'Webkey.{n}[purpose=anonymous_access]'
    ));
?>
    <h3>Patient Anonymous Access URL</h3>
    <div class="well admin-edit-section">
        <div>Highlight and copy the URL to use in an e-mail:</div>
        <div style="font-size: 13px; margin: 4px 0 1em"><?php
            echo $this->Html->url(array(
                'controller'=>'users',
                'action'=>'identify',
                $webkey['text']
            ), true);
        ?>
        </div>
    </div>
    

