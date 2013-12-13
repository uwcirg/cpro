<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
abstract class CodedItemsController extends AppController
{
    var $uses = array('Patient', 'User', 'Clinic', 'AudioFile');
    var $components = array('DhairDateTime');

    /** Name of the underlying model */
    protected $model;

    /** Instance of the underlying model */
    protected $modelInstance;

    /** User-viewable name for the type of coded item */
    protected $displayString;

    public function __construct($model, $displayString) {
        parent::__construct();
        $this->model = $model;
        $this->displayString = $displayString;
    }

    function beforeFilter($modelInstance) {
        parent::beforeFilter();
        $this->modelInstance = $modelInstance;
        $this->initPatientsTabNav();

        // use the regular checkboxes
	$this->set('standard_checkboxes_for_layout', 'true');
    }
   
    /**
     * Callback function to get a staff id from a row of a db query
     * @param row Array containing the row
     * @param return The id field from the row
     */
    private function getStaffId($row) {
        return $row['users']['id'];
    }

    /**
     * Callback function to get a staff name from a row of a db query
     * @param row Array containing the row
     * @param return The name field from the row
     */
    private function getStaffName($row) {
        return $row['users']['username'];
    }

    /**
     * Create an array of staffid/staff name pairs 
     * @param siteId If not null, limit array to staff from a particular site
     * @return array of staffid/staff name
     */
    protected function getStaffArray($siteId = null) {
        $staff = $this->modelInstance->getStaff($siteId);
        return array_combine(
            array_map(array('CodedItemsController', 'getStaffId'), $staff),
            array_map(array('CodedItemsController', 'getStaffName'), $staff));
    }

    /**
     * Can a user assign coders?
     * @param id Id of the user
     * @return whether the user can assign coders
     */
    protected function canAssignCoders($id) {
        return $this->DhairAuth->centralSupport($id);
    }

    /**
     * Check that a patient is consented; fail if they are not
     * @param $id patient's id
     */
    protected function checkConsented($id) {
        if (!$this->Patient->isParticipant($id)) {
            $this->Session->setFlash(
	        "Only participants should have {$this->displayString}s!");
            $this->redirect("/users/index");
        }
    }

    /**
     * Show a summary of all coded items, plus special lists for items
     * that need the user's attention
     */
    function viewAll() {
	$summary = $this->modelInstance->countStatuses();

	$summary['t2sConsent'] = $this->Patient->countCompletedT2s();

        $authUserId = $this->Auth->user('id');
        $centralSupport = $this->DhairAuth->centralSupport($authUserId);
        $researchStaff = $this->DhairAuth->researchStaff($authUserId);


	$toBeRecoded = $this->modelInstance->getToBeRecoded($authUserId,
            $centralSupport, $researchStaff);
	$toBeCoded = $this->modelInstance->getToBeCoded($authUserId, 
            $centralSupport, $researchStaff);
	$alreadyCoded = $this->modelInstance->getAlreadyCoded($authUserId, 
            $centralSupport, $researchStaff);
        // add 'unknown coder' to staff array
        $staffs = $this->getStaffArray();
        $staffs[UNKNOWN_CODER_ID] = 'Not yet known';

	$this->set('centralSupport', $centralSupport);
	$this->set('authUserId', $authUserId);
	$this->set('summary', $summary);
	$this->set('staffs', $staffs);
	$this->set('toBeRecoded', $toBeRecoded);
	$this->set('toBeCoded', $toBeCoded);
	$this->set('alreadyCoded', $alreadyCoded);
        return $summary;
    }

    /**
     * Randomly assign coded items to be double-coded (15% chance, independent)
     * @param patients Patients to be assigned
     * @return A list of patients to be double-coded
     */
    private function assignDoubleCoding($patients) {
	$doubleCodedPatients = array();

        foreach ($patients as $patient) {
	    if (mt_rand(1,100) <= 15) {  // 15% chance to double-code 
	        $doubleCodedPatients[] = $patient;
            }
        }

	return $doubleCodedPatients;
    }

    /**
     * Get a list of patients a staff member is eligible to code
     * @param patients List of all patients to code
     * @param staffId Id of the staff member
     * @return The list of patients a staff member is eligible to code
     */
    private function getEligiblePatients($patients, $staffId) {
        $eligible = array();

        foreach ($patients as $patient) {
	    if ($patient['Patient']['consenter_id'] != $staffId) {
	        $eligible[] = $patient;
            }
	}

        return $eligible;   
    }

    /**
     * For each staff member, get a list of patients they are eligible to
     * code, as well as the number of files they should code (as a integer),
     * and a number indicating how hard it will be to match them to eligible
     * patients.  
     *
     * @param patients Patients to be assigned
     * @param percents Array of percentages, indexed by staff id
     * @param totalAssignments Total number of files to assign (some can
     *    be assigned to 2 staff members, so this is not necessarily
     *    count($patients))
     * @return array; one entry per staff member,
     *    giving the (staff id, list of eligible patients, 
                      # of files to assign, 
     *                # of files / # of eligible patients), 
     */
    private function getStaffInfo($patients, $percents, $totalAssignments) {
        $staffInfo = array();
	$totalDefiniteAssignments = 0;
	// keep track of how many staff can possibly be assigned an extra file
	$staffWithPossibleExtraFile = 0;  

	foreach (array_keys($percents) as $staffId) {
	    $entry = array();
	    $entry['staffId'] = $staffId;
	    $entry['eligiblePatients'] = 
	        $this->getEligiblePatients($patients, $staffId);

            /* Keep filesToCode an integer to avoid floating point problems.  
	       To get the real number of files to code, divide by 100 */
            $filesToCode = $totalAssignments * $percents[$staffId];
	    $entry['filesToCode'] = $filesToCode;
            $totalDefiniteAssignments += intval($filesToCode / 100);

	    if ($filesToCode % 100 != 0) {
	        $staffWithPossibleExtraFile++;
            }

	    $staffInfo[] = $entry;
        }

        /* Assign each staff member a real number for filesToCode.  As noted
	   above, filesToCode needs to be divided by 100 to get the real 
	   number of files to code, but that could yield a floating point 
	   number; we want an integer.  Resolve this as follows:

	   Each staff member is assigned <n> files to code, where <n> is
	   the integer component of the (filesToCode / 100), plus 1
	   extra file iff a random number between 1 and 100 is less than the 
	   filesToCode % 100. 

	   So, for example, if filesToCode is 425, the staff member will get
	   4 files + an extra file with 25% probability.
	   
	   Two caveats:
	      if we have already assigned the number of remaining assignments,
	         no extra file is assigned
              if we have <k> remaining assignments and only <k>
	         staff left that might be assigned an extra file, the 
		 last <k> staff get an extra file
	 */

        /* Compute the number of 'leftover' files that won't be assigned 
	   if we just assign staff the integer part of filesToCount / 100.
	 */
	$remainingAssignments = $totalAssignments - $totalDefiniteAssignments;
	$remainingStaff = $staffWithPossibleExtraFile;

	foreach ($staffInfo as $key => $info) {
	    $filesToCode = $info['filesToCode'];
	    $intPart = intval($filesToCode / 100);
	    $remainder = $filesToCode % 100;

	    if ($remainder > 0) {
	        if ($remainingAssignments > 0 &&
	            ($remainingAssignments >= $remainingStaff ||
	             mt_rand(1, 100) <= $remainder))
                {
	            $filesToCode = $intPart + 1;
		    $remainingAssignments--;
                } else {
	            $filesToCode = $intPart;
	        }

                $remainingStaff--;
            } else {
	        $filesToCode = $intPart;
            }

            $staffInfo[$key]['filesToCode'] = $filesToCode;
	    /* compute how difficult it will be to match patients to this
               staff member */
            $eligiblePatientCount = count($info['eligiblePatients']);

            if ($eligiblePatientCount == 0) {	// impossible
                $staffInfo[$key]['matchDifficulty'] = 500;
            } else {
	        $staffInfo[$key]['matchDifficulty'] = 
	            $filesToCode / count($info['eligiblePatients']);
            }
        }

	return $staffInfo;
    }

    /**
     * Get staffinfo (as above) in special case where only one coder was
     * specified.  In this case, we assume that we still want to be able
     * to have double-coded patients, but currently only coder is known.
     * So we want to assign the single coder to all patients (if this is 
     * allowed) and assign a special, unknown coder as the second coder for
     * all double-coded patients.  This coder will have to be specified
     * later (by changing the database).
     *
     * @param patients Patients to be assigned
     * @param percents Array of percentages, indexed by staff id (should be
     *    count() = 1
     * @param totalAssignments Total number of files to assign (some can
     *    be assigned to 2 staff members, so this is not necessarily
     *    count($patients))
     * @return array; two entry, one for the single coder, one for the unknown
     *    coder,  Each entry consists of, 
     *                (staff id, list of eligible patients, 
     *                 # of files to assign, 
     *                 # of files / # of eligible patients), 
     */
    private function getStaffInfoOneCoder($patients, $percents, 
                                          $totalAssignments) 
    {
        $staffInfo = $this->getStaffInfo($patients, $percents, 
                                         $totalAssignments);

        $numPatients = count($patients);

        if ($numPatients == $totalAssignments) { // no unknown coder needed
            return $staffInfo;
        }

        // get the staffInfo for the single coder
        $coderInfo = $staffInfo[0];

        // assign the single coder to code all patients
        $coderInfo['filesToCode'] = $numPatients;
        $unknownCoderInfo = array(
            'staffId' => UNKNOWN_CODER_ID,
	    'eligiblePatients' => $patients,
	    'filesToCode' => $totalAssignments - $numPatients,
            'matchDifficulty' => 0);   // ensure unknown coder is assigned last
   
        return array($coderInfo, $unknownCoderInfo);
    }

    /**
     * Is the coder1 slot for the patient unassigned?
     */
    private function coder1Unassigned($patientId, $assigned) {
        return !array_key_exists($patientId, $assigned) ||
               !array_key_exists('coder1_id', $assigned[$patientId]);
    }

    /**
     * Is the coder2 slot for the patient unassigned?
     */
    private function coder2Unassigned($patient, $assigned, 
                                      $doubleCodedPatients) 
    {
        $patientId = $patient['Patient']['id'];

        return in_array($patient, $doubleCodedPatients) && 
               (!array_key_exists($patientId, $assigned) ||
                !array_key_exists('coder2_id', $assigned[$patientId]));
    }

    /**
     * Assign a staff member to be a coder for a patient
     * @param coder 1 or 2, indicating which coder
     * @param patientId Id of the patient
     * @param staffId Id of the staff
     * @param assigned Array that holds the assignements
     */
    private function assignCoder($coder, $patientId, $staffId, &$assigned) {
        $field = "coder{$coder}_id";

        if (array_key_exists($patientId, $assigned)) {
	    $assigned[$patientId][$field] = $staffId;
        } else {
	    $assigned[$patientId] = array($field => $staffId);
        }
    }

    /**
     * Make assignments of files to staff
     * @param staffInfo Info about eligible patients and number of files to
     *    assign for each staff (computed in getStaffInfo)
     * @param patients Patients to assign
     * @param doubleCodedPatients Patients to assign twice
     * @return An array of assignments, or null if the assignment process
     *    fails
     */
    private function makeAssignments($staffInfo, $patients, 
                                     $doubleCodedPatients)
    {
        // sort staff members by difficulty of picking patients, hardest first
        $sortedStaff = Set::sort($staffInfo, '{n}.matchDifficulty', 'desc');

	$assigned = array();

        foreach ($sortedStaff as $info) {
	    $eligiblePatients = $info['eligiblePatients'];
	    $toBeAssigned = $info['filesToCode'];
	    $staffId = $info['staffId'];

	    while ($toBeAssigned > 0) {
	        if (count($eligiblePatients) == 0) {
		    return null;	// FAILURE
                }

                // pick a candidate for next patient
		$nextPatientIndex = mt_rand(0, count($eligiblePatients) - 1);
	        $nextPatient = $eligiblePatients[$nextPatientIndex];

                // remove from eligible patients array
		unset($eligiblePatients[$nextPatientIndex]);
		/* maintain indexing of eligiblePatients array (otherwise 
		   mt_rand may not return a valid index) */
		$eligiblePatients = array_values($eligiblePatients);

                $patientId = $nextPatient['Patient']['id'];

                // Does the patient have an assignment left?
		if ($this->coder1Unassigned($patientId, $assigned) ||
		    $this->coder2Unassigned($nextPatient, $assigned, 
                                            $doubleCodedPatients))
                {
		    $toBeAssigned--;

                    if ($this->coder1Unassigned($patientId, $assigned)) {
		        if ($this->coder2Unassigned($nextPatient, $assigned, 
                                                    $doubleCodedPatients))
                        {
                            /* pick whether staff member is coder 1 or 2 
                               at random */
                            $this->assignCoder(mt_rand(1, 2), $patientId, 
                                               $staffId, $assigned);
                        } else {
                            // must be coder 1
		            $this->assignCoder(1, $patientId, $staffId, 
                                               $assigned);
                        }
                    } else {
                        // must be coder 2
		        $this->assignCoder(2, $patientId, $staffId, $assigned);
                    }
                }
            }
        }

	return $assigned;
    }

    /**
     * After a database failure making coding assignments (on firstFailureId),
     * rollback the previous assignments.
     * @param assignments All assignments
     * @param firstFailureId Id on which it failed
     */
    private function undoAssignments($assignments, $firstFailureId) {
        $this->log(
	    "undoAssignments called due to DB failure on $firstFailureId");

        foreach ($assignments as $patientId => $assignment) {
	    if ($patientId == $firstFailureId) {   // done
	        break;
	    }

	    $this->modelInstance->create();
	    $this->request->data = $this->modelInstance->findByPatientId($patientId);
	    $this->request->data[$this->model]['coder1_id'] = null;
	    $this->request->data[$this->model]['coder2_id'] = null;
	    $this->request->data[$this->model]['status'] = AppModel::SCRUBBED;
	    $this->request->data[$this->model]['double_coded_flag'] = false;
            $this->request->data[$this->model]['assigned_timestamp'] = null;
            $success = $this->modelInstance->save($this->request->data, 
	        array('fieldList' => array('coder1_id', 'coder2_id', 'status', 
		                           'assigned_timestamp', 
		                           'double_coded_flag')));

	    if (empty($success)) {
	        $this->log("Failed to rollback id $patientId");
            } else {
	        $this->log("Successfully rolled back id $patientId");
            }
        }
    }

    /**
     * Get an instance of the coded item for the patient, just before a coder
     * is assigned.  Depending on the workflow of a particular subclass, the 
     * instance may already exist or may need to be created
     * @param patientId Id of the patient
     */
    abstract protected function getInstanceForAssignment($patiendId);

    /**
     * Make the coding assignments in the database
     * @param assignments Assignments as an array, indexed by patient id
     * @return True on success, false on failure
     */
    private function assignInDatabase($assignments) {
        $success = true;

        foreach ($assignments as $patientId => $assignment) {
	    $this->modelInstance->create();
            $this->request->data = $this->getInstanceForAssignment($patientId);
	    $this->request->data[$this->model]['coder1_id'] = $assignment['coder1_id'];
	    $this->request->data[$this->model]['status'] = AppModel::ASSIGNED_CODING;
            $this->request->data[$this->model]['assigned_timestamp'] = 
                $this->DhairDateTime->currentGmt();


	    if (!empty($assignment['coder2_id'])) {
	        $this->request->data[$this->model]['double_coded_flag'] = true;
	        $this->request->data[$this->model]['coder2_id'] = 
		    $assignment['coder2_id'];
            } else {
	        unset($this->request->data[$this->model]['double_coded_flag']);
	        unset($this->request->data[$this->model]['coder2_id']);
            }

            $success = $this->modelInstance->save($this->request->data, 
	        array('fieldList' => array('coder1_id', 'coder2_id', 'status',
		                           'assigned_timestamp', 
                                           'double_coded_flag', 'patient_id')));

	    if (empty($success)) {
	        $this->undoAssignments($assignments, $patientId);
	        break;
            }
        }

	return !empty($success);
    }

    /**
     * Do the actual staff-coding assignments
     * @param patients Patients to be assigned
     * @param staffs Array of staff who can do assignments
     * @param percents Percentage of coding assignments a staff member should
     *    get
     * @return an array of (staff names => # of assignments), or null if
     *    the process failed
     */
    private function assignStaffsToCode($patients, $staffs, $percents) {
        // figure out number of coding assignments to do
	$doubleCodedPatients = $this->assignDoubleCoding($patients);

	$totalAssignments = count($patients) + count($doubleCodedPatients);

	/* determine the number of eligible patients the number of files
	   to code, and how difficult it will be to find eligible patients 
	   for each staff member */
        if (count($percents) == 1) { 
            /* if only one coder, we assume we will want to add a second
               coder later, but don't know who */
	    $staffInfo = $this->getStaffInfoOneCoder($patients, $percents, 
	                                             $totalAssignments);
        } else {
	    $staffInfo = $this->getStaffInfo($patients, $percents, 
	                                     $totalAssignments);
        }

        $assignments = 
	    $this->makeAssignments($staffInfo, $patients, $doubleCodedPatients);

        if (empty($assignments)) {	// FAILURE
	    return null;
	}

	$result = $this->assignInDatabase($assignments);
	
	if (empty($result)) { // db failure
	    return null;
	}

        return $staffInfo;
    }

    /**
     * Create a flash message with details about coding assignment
     * @param staffInfo Info on staff assignments, as generated by getStaffInfo
     * @param staffs Basic info on staff members
     * @return Message
     */
    private function codeAssignMessage($staffInfo, $staffs) {
        $message = 'Assignment successful.';

	foreach ($staffInfo as $info) {
            if ($info['staffId'] == UNKNOWN_CODER_ID) {
	        $message .= "  {$info['filesToCode']} file(s) to be assigned.";
            } else {
	        $message .= "  {$staffs[$info['staffId']]}: 
	                       {$info['filesToCode']} file(s).";
            } 
        }

	return $message;
    }
 
    /**
     * Trim the toBeAssigned array to a particular size by removing 
     * random entries
     */
    private function trimAssigned(&$toBeAssigned, $numberToAssign) {
        while (count($toBeAssigned) > $numberToAssign) {
	    unset($toBeAssigned[mt_rand(0,count($toBeAssigned))]);
            $toBeAssigned = array_values($toBeAssigned);
        }
    }

    /**
     * Show the form for assigning coders, and do the assignment
     */
    function assignCoders($siteId = null) {
        $siteId = intval($siteId);
        $staffs = $this->getStaffArray($siteId);
	$toBeAssigned = $this->modelInstance->getToBeAssigned($siteId);

	if (count($toBeAssigned) == 0) {
            $this->Session->setFlash('No files to assign');
            $this->redirect(array('action' => 'viewAll'));
        }

        if (!empty($this->request->data[$this->model]) && 
	    !empty($this->request->data[$this->model]['percent'])) 
        {
            $numberToAssign = 
                intval($this->request->data[$this->model]['numberToAssign']);

	    $percents = $this->request->data[$this->model]['percent'];
	    $total = 0;

	    // clean up array and get total
	    foreach (array_keys($percents) as $staffId) {
	        $percents[$staffId] = intval($percents[$staffId]);

		if ($percents[$staffId] < 1 || 
		    !array_key_exists($staffId, $staffs)) 
		{
		    unset($percents[$staffId]);
                } else {
                    $total += $percents[$staffId];
		}
	    }

            if ($numberToAssign < 1 || $numberToAssign > count($toBeAssigned)) {
	        $this->Session->setFlash(
		    "Bad value for total to assign ($numberToAssign)");
            } else {
                $this->trimAssigned($toBeAssigned, $numberToAssign);

                if ($total == 100) {
	            $success = false;

		    for ($attempts = 0; $attempts < 10 && !$success; 
                         $attempts++) 
                    {
	                $staffInfo = $this->assignStaffsToCode($toBeAssigned,
		             $staffs, $percents); 
		        $success = !empty($staffInfo);
                    }
                   
                    if ($success) {
                        $this->Session->setFlash(
		            $this->codeAssignMessage($staffInfo, $staffs));
                        $this->redirect(array('action' => 'viewAll'));
                    } else {
	                $this->Session->setFlash(
		            'Code assignment failed after 10 attempts.  Adjust
			     percentages and try again.');
                    }
                } else {
	            $this->Session->setFlash(
		        "Total ($total) does not add up to 100");
                }
	    }
        }

        $this->set('siteId', $siteId);
        $this->set('toBeAssigned', $toBeAssigned);
        $this->set('staffs', $staffs);
    }
}
?>
