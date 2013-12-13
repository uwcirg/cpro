<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<tr>
<th>Present During Recording:</th>
<td>
<span style="font-size:60%">
For example: Patient-Male, Family-Female, NP-Female, Attending-Male, etc.
</span>
<?php
    echo $this->Form->input('AudioFile.present_during_recording', 
                      array('label' => ''));
?>
</td>
</tr>
<tr>
<th>T2 Questionnaire completed?:</th>
<td>
<?php
    echo $this->Form->input('AudioFile.questionnaire_completed',
                      array('type' => 'checkbox',
                            'label' => ''));
?>
</td>
</tr>
<tr>
<th>Question 1:</th>
<td>
<?php
    $questionOptions = array('0' => '0',
                             '1' => '1', '2' => '2', '3' => '3', '4' => '4', 
                             '5' => '5', '6' => '6', '7' => '7', '8' => '8', 
                             '9' => '9', '10' => '10');
    echo $this->Form->input('AudioFile.question_1', 
                      array('options' => $questionOptions,
		            'empty' => true,
		            'label' => ''));
?>
</td>
</tr>
<tr>
<th>Question 2:</th>
<td>
<?php
    echo $this->Form->input('AudioFile.question_2', 
                      array('options' => $questionOptions,
		            'empty' => true,
		            'label' => ''));
?>
</td>
</tr>
