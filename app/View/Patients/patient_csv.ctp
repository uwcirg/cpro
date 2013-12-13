<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * render patient list as a csv
*/

    // headings
    $this->Csv->addField('Patient ID');
    $this->Csv->addField('Consent Status');
    $this->Csv->addField('Next Appt');
    $this->Csv->addField('Clinic');
    $this->Csv->addField('Clinical Service');
    $this->Csv->endRow();

    foreach ($patients as $patient) {
        $this->Csv->addField($patient['Patient']['id']);
        $this->Csv->addField($patient['Patient']['consent_status']);
        $this->Csv->addField($patient['Patient']['next_appt_dt']);
        $this->Csv->addField($patient['Clinic']['name']);
        $this->Csv->addField($patient['Patient']['clinical_service']);
        $this->Csv->endRow();
    }

    $datetime = str_replace(' ', '_', date('Y-m-d'));
    echo $this->Csv->render("patient.$datetime.csv");
?>
