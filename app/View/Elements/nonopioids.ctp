<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
   echo '<tr data-newrow-id="'.$id.'"><td>';
   echo $this->Form->input("MeddayNonopioid.$id.name", 
                           array('label' => '', 
                                 'class' => 'input-medium',
                                 'value' => $name));
   echo '</td><td>'; 
   echo $this->Form->input("MeddayNonopioid.$id.dose", 
                           array('label' => '',
                                 'class' => 'input-small',
                                 'value' => $dose));
   echo '</td><td>';
   echo $this->Form->input("MeddayNonopioid.$id.frequency",
                           array('label' => '',
                                 'class' => 'input-small',
                                 'value' => $frequency));
   echo '</td><td>'; 
   echo $this->Form->select("MeddayNonopioid.$id.take_as", 
                            $takeAsOptions, 
                            array('label' => '',
                                  'value' => $takeAs,
                                  //'class' => 'span2',
                                  'style' => 'margin-top: 5px',
                                  'empty' => false)); 
   echo '</td>';
   echo '<td class="v-center"><button class="remove-row btn btn-mini" 
       title="Delete this medication"><i class="icon-trash"></i></button>
       </td></tr>';
?>
