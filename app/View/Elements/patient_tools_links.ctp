<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<!-- start_pdf_no -->
<ul class="nav nav-pills patient_tools_links">
   
    <?php
    // Main summary/edit page
    echo "<li class='";
    if ($this->request->params['action'] == 'edit') {
        echo "active";
    }
    echo "'>".$this->Html->link('Summary', '/patients/edit/' . 
                    $patientId)."</li>";

    // PainTracker actions
    if (isset($medications_patient)) {
        echo "<li class='";
        if ($this->request->params['action'] == 'medications') {
            echo "active";
        }
        if ($sessionToday) {
            echo "'>".$this->Html->link('Today\'s Medications', '/patients/medications/' . $patientId)."</li>";
        } else {
            echo " disabled' title='Can be entered after an assessment is completed.'><a href='#'>Today's Medications</a></li>";
        }
    }
    if (defined('DASHBOARD') && DASHBOARD ) {
        echo "<li class='";
        if ($this->request->params['action'] == 'dashboard') {
            echo "active";
        }
        if ($finishedSession){
            echo "'>".$this->Html->link('Dashboard', '/patients/dashboard/' . $patientId)."</li>";
        }
        else {
            echo " disabled' title='Available after an assessment is completed.'><a href='#'>Dashboard</a></li>";
        }
    } 
    if (isset($showLinkToClinicianReport) and $showLinkToClinicianReport) {
        echo "<li class='";
        if ($finishedSession){
            echo "'>".$this->Html->link('Clinician Report', '/medical_records/clinic_report_p3p/' . $patientId . '/' . P3P_BASELINE_PROJECT)."</li>";
        }
        else {
            echo " disabled' title='Available after an assessment is completed.'><a href='#'>Clinician Report</a></li>";
        }
    } 
    // For use with SME Activity Diary
    if (isset($entries)) {
        echo "<li class='";
        if ($this->request->params['action'] == 'activityDiary') {
            echo "active";
        }
        echo "'>".$this->Html->link('View Activity Diary',
                '/patients/activityDiary/' . $patientId)."</li>";
    }    
    
    // Login as patient and assess patient actions (go to non /patient/ views)
    if ($canEdit) {
        echo '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown"
            href="#">Assess this Patient <b class="caret"></b></a>';
        echo '<ul class="dropdown-menu">';
        foreach($patientProjectsStates as $projectId => $state){
            $project = $state->project['Project'];

            //TODO would be nice to use DhairAuth for this
            if (!strstr($project['roles_that_can_assess'], 'aroClinicStaff')){
                continue;
            }
            if (
                $state->apptForNewSession or
                $state->apptForResumableSession or
                $state->initableNonApptSessionType or
                $state->resumableNonApptSession
            ) {
                echo "<li>".$this->Html->link("Assess this Patient for '" . $project['Title'] . "'",
                         "/patients/takeSurveyAs/$patientId/" 
                                . $project['id'] . "?" .
                                AppController::ID_KEY . "=" .
                                $this->Session->read(AppController::ID_KEY))."</li>";    
            } else {
                 echo "<li class='disabled' title='Available when another appointment is scheduled.'><a href='#'>Assess this Patient for '" . $project['Title'] . "'</a></li>";
            }
        }// foreach($patientProjectsStates as $projectId => $state){
        echo "</ul></li>";

        if (defined('LOGIN_AS_PATIENT_ALLOWED') && LOGIN_AS_PATIENT_ALLOWED
            && (!defined('ELIGIBILITY_WORKFLOW')
                || (defined('ELIGIBILITY_WORKFLOW') && !ELIGIBILITY_WORKFLOW)
                || ($this->request->data['Patient']['eligible_flag'] == '1')))
        {
            echo "<li>".$this->Html->link('Login As This Patient',
                            "/patients/loginAs/$patientId?" .
                                AppController::ID_KEY . "=" .
                                $this->Session->read(AppController::ID_KEY))."</li>";
        }
    }
    
    // Standard links, should appear for all users
    echo "<li>".$this->Html->link('Reset Password',
                     "/patients/resetPassword/$patientId?" .
                            AppController::ID_KEY . "=" .
                            $this->Session->read(AppController::ID_KEY))."</li>";
    echo "<li>".$this->Html->link('User log', '/logs/index/' . $patientId)."</li>";
    ?>
</ul>
<script>
  $('.dropdown-toggle').dropdown();
</script>
<!-- end_pdf_no -->

