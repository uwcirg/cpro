Hello,<br>

<p>
This email is to inform you
<?php
echo $this->Html->link(
    'patient #'.$patient['id']."'s",
    $url.$this->Html->url(
        array(
            'controller' => 'patients',
            'action' => 'edit',
            $patient['id'],
        )
    )
);
?>
 survey session window has started and will close on <?php echo $currentWindow['stop']->format('n/j/y \(l\)'); ?>.
</p>

You can follow the link
<?php
echo $this->Html->link(
    'here',
    $url.$this->Html->url(
        array(
            'controller' => 'patients',
            'action' => 'edit',
            $patient['id'],
        )
    )
);

?>
 if you would like to view their information.
Thank you<br>

