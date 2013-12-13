<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

echo "<h2>$heading</h2>";

if (empty($patients)) {
?>
<p>None</p>

<?php
} else {
?>
<table class="audioFiles" >
    <tr>
        <th><?php echo 'Last name, First name'; ?></th>
        <th><?php echo 'Patient ID'; ?></th>
        <th><?php echo 'Clinic'; ?></th>
        <th>&nbsp;</th>
    </tr>
<?php
    foreach($patients as $patient) {
?>
    <tr>
        <td><?php
            echo $patient['User']['last_name'] . ', ' .
                 $patient['User']['first_name'];
        ?></td>
        <td><?php echo $patient['Patient']['id']; ?></td>
        <td><?php echo $patient['Clinic']['name']; ?></td>
        <td><?php
            echo $this->Html->image('magnify.gif', array(
                'alt' => 'View',
                'height' => '25',
                'url' => array(
                    'controller' => 'patients',
                    'action' => 'view',
                    $patient['Patient']['id']
                )
            ));
            echo $this->Html->image('audio.png', array(
                'alt' => 'Audio File',
                'height' => '25',
                'url' => array(
                    'controller' => 'audio_files',
                    'action' => 'index',
                    $patient['Patient']['id']
                )
            ));
        ?></td>
    </tr>
<?php
    }
}
?>
</table>
