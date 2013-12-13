<?php
/**
    * PatientExtension class
    *
    * Allows for instance-specific fields in the Patients table/model.
    * Patient beforeFind and beforeSave will merge the fields into
    *   the Patient data array
    * These fields need not be explicitly queried or saved in code;
    *   instead, the Patient model reads the patient_extensions 
    *   schema to identify them. 
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/

class PatientExtension extends AppModel {

    var $primaryKey = 'patient_id';
    var $hasOne = array('Patient' => array('foreignKey' => 'id', 'dependent' => true));
}
?>
