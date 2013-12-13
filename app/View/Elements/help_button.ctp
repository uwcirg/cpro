<?php

echo $this->Html->link(
    "<i class='icon-question-sign'></i> ".__('Help'), '/users/help',
    array(
        'title'=>'Help page',
        'class'=>'btn btn-small',
        'style' => 'margin-right: 12px',
        'escape' => false,
    )
);

?>