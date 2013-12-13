<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

echo "<h2>$heading</h2>";

if (empty($codedItems)) {
?>
<p>None</p>

<?php
} else {
?>
<table class="audioFiles" >
    <tr>
        <th><?php echo 'Patient ID'; ?></th>
<?php 
    if ($showAll) { 
?>
        <th><?php echo 'Coder 1'; ?></th>
        <th><?php echo 'Coder 2'; ?></th>
<?php 
    } else { 
?>
        <th><?php echo 'Coder'; ?></th>
<?php 
    } 

    if (!$reviewCoding) {
?>
        <th><?php echo 'Assigned On'; ?></th>
<?php
    }
?>
        <th>&nbsp;</th>
    </tr>
<?php
    $reviewPrefix = $showAudioIcon ? '' : '/chart_codings/';
   
    foreach($codedItems as $codedItem) {
        $patientId = $codedItem[$model]['patient_id']; 
        $coder1 = $codedItem[$model]['coder1_id'];
        $coder2 = $codedItem[$model]['coder2_id'];
        $status = $codedItem[$model]['status'];
?>
    <tr>
        <td><?php echo $patientId; ?></td>
<?php
    if ($showAll) { 
?>
        <td>
        <?php 
            echo $staffs[$coder1]; 

            if ($reviewCoding && 
                in_array($status, array(AudioFile::CODER1_DONE, 
                                        AudioFile::CODING_DONE,
                                        AudioFile::TO_BE_RECODED)))
            {
                echo "&nbsp;" . $this->Html->link('Review', 
                    "{$reviewPrefix}review/$patientId/1");
            } else if ($status == AudioFile::CODER1_DONE) {
                echo '&nbsp;' . $this->Html->image('check.jpg', array(
                    'alt' => 'Done',
                    'height' => '15'
                ));
            }
        ?>
        </td>
        <td>
        <?php 
            if (empty($coder2)) {
                echo '&nbsp;';
            } else {
                echo $staffs[$coder2];

                if ($reviewCoding && 
                    in_array($status, array(AudioFile::CODER2_DONE, 
                                            AudioFile::CODING_DONE,
                                            AudioFile::TO_BE_RECODED)))
                {
                    echo "&nbsp;" . $this->Html->link('Review', 
                        "{$reviewPrefix}review/$patientId/2");
                } else if ($status == AudioFile::CODER2_DONE) {
                    echo '&nbsp;' . $this->Html->image('check.jpg', array(
                        'alt' => 'Done',
                        'height' => '15'
                    ));
                }
            }
        ?>
        </td>
<?php
    } else {
?>
        <td>
        <?php 
            echo $staffs[$authUserId]; 

            if ($reviewCoding) {
                $coder = ($coder1 == $authUserId ? 1 : 2);
                echo "&nbsp;" . $this->Html->link('Review', 
                    "{$reviewPrefix}review/$patientId/$coder");
            }
        ?>
        </td>
<?php
    }
 
    if (!$reviewCoding) {
?>
        <td>
        <?php
            if ($heading == 'Assigned for Coding') {
                echo $codedItem[$model]['assigned_timestamp'] . ' GMT';
            } else {  // recoded; print later coding date
                $timestamp1 = $codedItem[$model]['coder1_timestamp'];
                $timestamp2 = $codedItem[$model]['coder2_timestamp'];
                echo max($timestamp1, $timestamp2) . ' GMT';
            }
        ?>
        </td>
<?php
    }
?>

        <td><?php
            echo $this->Html->image('magnify.gif', array(
                'alt' => 'View',
                'height' => '25',
                'url' => array(
                    'controller' => 'patients',
                    'action' => 'view',
                    $patientId
                )
            ));
 
            if ($showAudioIcon) {
                echo $this->Html->image('audio.png', array(
                    'alt' => 'Audio File',
                    'height' => '25',
                    'url' => array(
                        'controller' => 'audio_files',
                        'action' => 'index',
                        $patientId
                    )
                ));
            } else if (!$reviewCoding &&
                       (!$showAll || ($coder1 == $authUserId && 
                                      $status != AudioFile::CODER1_DONE) 
                                  || ($coder2 == $authUserId && 
                                      $status != AudioFile::CODER2_DONE)))
            {
                $recode = $status == AudioFile::TO_BE_RECODED ? 1 : 0;
                echo $this->Html->link('Code', 
                    "/chart_codings/code/$patientId/$recode");
            } 

            if ($reviewCoding && $codedItem[$model]['double_coded_flag'] &&
                $status == AudioFile::CODING_DONE &&
                $codedItem[$model]['agreement'] != 1)
            {
                echo $this->Html->link('Review 3rd coding', 
                    "{$reviewPrefix}review/$patientId/3");
            }
        ?></td>
    </tr>
<?php
    }
}
?>
</table>
