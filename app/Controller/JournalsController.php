<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
App::uses('Sanitize', 'Utility');
class JournalsController extends AppController {

    var $components = array("RequestHandler");
    var $helpers = array("Html", "Form");
    var $uses = array("JournalEntry", "User", "Patient", 
                        'Associate', 'PatientAssociate');

    function beforeFilter() {
        parent::beforeFilter();

        $this->TabFilter->selected_tab("Results");
        $this->TabFilter->show_normal_tabs();
    
        //$this->log("JournalsController beforeFilter()", LOG_DEBUG); 
       
        //$this->DhairLogging->logArrayContents($this->request->params, "params");

        if($this->request->isAjax()) {
            Configure::write('debug', 0);
            header('Pragma: no-cache');
            header('Cache-control: no-cache');
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        }
    }
  
    function index()
    {
        /**$this->log("JournalsController index(patient_id: " .
                    $patientId . "); trace:" . Debugger::trace(), 
                    LOG_DEBUG); */
        $patientId = $this->authd_user_id;
        
        $patientJournalEntries =
                $this->JournalEntry->displayedFor($patientId);
        $this->set('journalEntries', $patientJournalEntries);
        /**
        $this->DhairLogging->logArrayContents($patientJournalEntries, 
                "patientJournalEntries for patientId " . $patientId);
        */
    }

    function listForReadOnly($patientId)
    {
        /**$this->log("JournalsController listForReadOnly(patient_id: " .
                    $patientId . "); trace:" . Debugger::trace(), 
                    LOG_DEBUG); */
        // check that this user has access to the patient's journal entries
        $patientAssociate = 
	    $this->PatientAssociate->forPatientAndAssociate($patientId, 
	                                                    $this->authd_user_id);

        if (empty($patientAssociate) || 
	    !$patientAssociate['PatientAssociate']['share_journal']) 
        {
	    $this->log("Journals.listForReadOnly: illegal attempt to read journal entries for $patientId by user {$this->authd_user_id}");
	    $this->set('journalEntries', array());
        } else {
            $patientJournalEntries =
                $this->JournalEntry->displayedFor($patientId);
            $this->set('journalEntries', $patientJournalEntries);
        }

        /**
        $this->DhairLogging->logArrayContents($patientJournalEntries, 
                "patientJournalEntries for patientId " . $patientId);
        */
    }

    function delete($journalEntryId) {
        // check that this user created the journal entry
        $journal = $this->JournalEntry->findById($journalEntryId);

        if ($journal["JournalEntry"]["patient_id"] != $this->authd_user_id) {
	    $this->log("Journals.delete: illegal attempt to delete $journalEntryId by user {$this->authd_user_id}");
        } else {
            $journal["JournalEntry"]["display"] = false; 
            $this->JournalEntry->save($journal);
        }

        if($this->request->isAjax()) {
            exit();
        }
    }

    function create() {
        $patientId = $this->authd_user_id;

        if($this->request->data) {
            $journal =& $this->request->data;
            $journal["JournalEntry"]["text"] = str_replace("\\n", " ", $journal["JournalEntry"]["text"]);
            $journal["JournalEntry"]["text"] = strip_tags($journal["JournalEntry"]["text"]);
            $journal["JournalEntry"]["date"] = date("Y-m-d H:i:s", strtotime($journal["JournalEntry"]["date"]));
            $journal["JournalEntry"]["display"] = true;
            $journal["JournalEntry"]["patient_id"] = $patientId;
            $this->JournalEntry->save($journal);
            exit();
        }
    }

    function updateText() {
        if($this->request->data) {
            // Auth: check this entry belongs to current user
	    $journalEntryId = $this->request->data["JournalEntry"]["id"];
            $journal = $this->JournalEntry->findById($journalEntryId);

            if ($journal["JournalEntry"]["patient_id"] != $this->authd_user_id) {
	        $this->log("Journals.update: illegal attempt to update $journalEntryId by user {$this->authd_user_id}");
            } else {
                $journal["JournalEntry"]["text"] = $this->request->data["JournalEntry"]["text"];
                $journal["JournalEntry"]["text"] = str_replace("\\n", " ", $journal["JournalEntry"]["text"]);
                $journal["JournalEntry"]["text"] = strip_tags($journal["JournalEntry"]["text"]);
                $this->JournalEntry->save($journal);
                $this->set('text', $journal["JournalEntry"]["text"]);
            }
        }
    }
}
?>
