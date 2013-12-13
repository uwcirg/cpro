<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div class='viewingInfoFor'>
<?php 
    print $this->Html->link('Reports for other people',
                    '/results/others') . ' can also be viewed.';
?>
</div>

<h1>Reports for 
<?php echo $patient['User']['first_name'] . ' ' . 
            $patient['User']['last_name']; ?>
</h1>

<?php
if ($journalShared == 1){
    echo $this->Html->link(
        $this->Html->image("page.gif",
            array('width'=>'16', 'height'=>'16',
                'alt' => "Journals", 'align'=>'top',
                'border'=>'0')) . ' View Journal Entries',
            '/results/showJournalsToOthers/' . $patient['Patient']['id'],
            array('escape' => false), false);
?>
        <br/>
        <br/>
<?php
}

$possessiveName = $patient['User']['first_name'];
if (substr($possessiveName, -1) == 's'){
    $possessiveName .= "'";
}
else $possessiveName .= "'s";
?>
Below is a list of different symptoms and quality of life issues that were included in <?php echo $possessiveName?> report. There is a small graph for each of these showing change over time. Select any small graph to see a larger version and to learn more about the symptom or issue and what can be done about it. A symptom or issue in the red area requires attention.
        <br/>
        <br/>

<?php echo $this->element('scale_thumbnails', 
                            array("forAssociateView" => true)); ?>
