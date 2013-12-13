<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<h1>View Reports</h1>

This tab is for viewing reports and journals for patients who would like to share information with you.<br/>
<br/>

<?php                            
    if (sizeof($patientAssociates) > 0){
?>
<fieldset>
<legend>Whose symptom charts would you like to view?</legend>
<br/>
<ul>
<?php 
    foreach($patientAssociates as $pA) {

?>
        <li>
<?php
        if ($pA['PatientAssociate']['has_entered_secret_phrase']
                == true){
?>
            <div class='linkToResultsOfOthers'>
                <?php echo $this->Html->link(
                    $pA['Patient']['User']['first_name'] . ' ' .
                        $pA['Patient']['User']['last_name'] ,
                    'othersReportsList/' . $pA['Patient']['id']);?>
            </div>
<?php
        }
        else{
?>
            <div class='secretB4ResultsOfOthers'>
                <?php echo $pA['Patient']['User']['first_name'] . ' ' .
                        $pA['Patient']['User']['last_name'][0] . '.'; ?>
                <br/>
                <p>Before you can view symptoms for this person you will need to enter the 'secret word'</p>
<?php
                echo $this->Form->create('Associate', // defaults to controller' name
                                        array('action' => "phraseEntry"));
                echo $this->Form->input('secret_phrase', 
                                    array('label' => 'Secret Word'));
                echo "<input type='hidden' name='data[AppController][AppController_id]' value='".
                    $this->Session->read(AppController::ID_KEY) .
                    "'/>";
                echo $this->Form->hidden('PatientAssociate.id',
                            array('value' => 
                                $pA['PatientAssociate']['id']));
                echo $this->Form->submit();
                echo $this->Form->end();
?>
            </div>
<?php
        }
?>
        </li>
        <br/>
<?php
    }
?>
</ul>
</fieldset>

<?php
    }
    else{
?>
There are currently no patients sharing reports with you.
<?php
    }
?>   

