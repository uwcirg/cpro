<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
if (Configure::check('PATIENT_EDIT_ELEMENTS')){
    $patientEditElements = Configure::read('PATIENT_EDIT_ELEMENTS');
}
?>

<h3>Follow-ups 
 <button class="btn btn-small edit-btn" name="followUpEdit" data-edit-mode="view">Edit</button> 
 <button class="btn btn-mini minimize-section" data-hide="#followUpEdit"><i class="icon-chevron-up"></i> Hide</button>
</h3>

<div id="followUpEdit" class="well admin-edit-section disable-section">
<?php
if (in_array('1_wk_fu', $patientEditElements)){
    echo $this->element('edit_1_wk_fu'); 
}

if (in_array('1_mo_fu', $patientEditElements)){
    echo $this->element('edit_n_mo_fu', array('project' => P3P_1_MO_FU_PROJECT)); 
}

if (in_array('6_mo_fu', $patientEditElements)){
    echo $this->element('edit_n_mo_fu', array('project' => P3P_6_MO_FU_PROJECT)); 
}
?>
</div>
