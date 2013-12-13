<?php
/** 
    * Coded Item (behavior for audio file and chart)
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class CodedItemBehavior extends ModelBehavior {
    /** Name of the model using this behavior */
    private $name;
    /** Name of the table the model uses */
    private $useTable;

    function setup(&$model, $config = array()) {
        parent::setup($model, $config);
        $this->name = $model->name;
        $this->useTable = $model->useTable;
    }

    /**
     * Find all staff that should be working with coded items
     * @param siteId If not null, return only staff from the given site
     * @param audio_coding_adjunct 'adjunct' or 'nonadjunct' or null 
     * @return Staff described, in the usual $this->data format
     */
    function getStaff(&$model, $siteId, $audio_coding_adjunct = false) {
        $siteId = intval($siteId);

        /* users, user_acl_leafs represent the staff; 
         */
        $queryString = "SELECT users.id, users.username from users 
            JOIN user_acl_leafs, clinics
            WHERE users.id = user_acl_leafs.user_id
            AND users.clinic_id = clinics.id
            AND (user_acl_leafs.acl_alias = 'aclCentralSupport' 
                 OR user_acl_leafs.acl_alias = 'aclAdmin'
                 OR user_acl_leafs.acl_alias = 'aclResearchStaff')";

        if (!empty($siteId)) {
            $queryString .= " AND clinics.site_id = $siteId";
        }
       
        switch ($audio_coding_adjunct) { 
            case 'adjunct':
                $queryString .= " AND users.audio_coding_adjunct <> 0";
            break; 
            case 'nonadjunct':
                $queryString .= " AND users.audio_coding_adjunct = 0";
            break; 
        }

        return $model->query($queryString);
    }

    /**
     * Callback function to get a status name from a row of a db query
     * @param row Array containing the row
     * @return The status field from the row
     */
    private function getStatusName($row) {
        return $row[$this->useTable]['status'];
    }

    /**
     * Callback function to get a status count from a row of a db query
     * @param row Array containing the row
     * @return The count field from the row
     */
    private function getStatusCount($row) {
        return $row[0]['count'];
    }

    /**
     * Create an array of status/status counts pairs 
     * @return array of status/status counts pairs 
     */
    function countStatuses(&$model) {
        $query = 
            "SELECT status, count(DISTINCT(patients.id)) as count
	     from {$this->useTable}, patients
	     WHERE patients.id = {$this->useTable}.patient_id
	           AND patients.test_flag <> 1
                   AND (patients.off_study_status <> '" . 
                            Patient::OSINELIGIBLE . "'
                        OR patients.off_study_status IS NULL)
	          GROUP BY status";
        $counts = $model->query($query);
        
	if (empty($counts)) {
	    $result = array();
        } else {
            $result = array_combine(
                array_map(array('CodedItemBehavior', 'getStatusName'), $counts),
                array_map(array('CodedItemBehavior', 'getStatusCount'), $counts));
        }
	
	// fill in the blank statuses with zeroes
	foreach ($model->getStatuses() as $status) {
	    if (empty($result[$status])) {
	        $result[$status] = 0;
            }
	}

	// remove any null statuses 
	unset($result[null]);

	return $result;
    }

    /**
     * Get a list of relevant participants who have coded items in a
     * particular state/status
     * @param id Id of the authorized user
     * @param adminUser True if they are an administrator
     * @param researchStaff True if they are research staff
     * @param status Current status of the file (but not inclusive, as follows:
     *   RAW implies RAW or DOWNLOADED_SCRUB, 
     *   ASSIGNED_CODING implies ASSIGNED_CODING, CODER1_DONE or CODER2_DONE,
     *   CODING_DONE implies CODING_DONE, TO_BE_RECODED, CODER1_DONE or 
     *                       CODER2_DONE)
     * @param audio_coding_adjunct false to ignore, 'adjunct', or 'nonadjunct' 
     * @return a list of accessible participants who have coded items in
     *    the proper state
     */
    function getAll(&$model, $id, $adminUser, $researchStaff, 
                    $status, $audio_coding_adjunct = false) {
        $query = $model->Patient->getAccessiblePatientsQuery3($id, $adminUser, 
            $researchStaff, true, false, $audio_coding_adjunct);

        $select = ", {$this->name}.patient_id, {$this->name}.status,
                   {$this->name}.coder1_id, {$this->name}.coder2_id, 
                   {$this->name}.assigned_timestamp, 
                   {$this->name}.double_coded_flag, 
                   {$this->name}.agreement, 
                   {$this->name}.coder1_timestamp,
                   {$this->name}.coder2_timestamp";
	$join = ", {$this->useTable} as {$this->name}";
        $where = " AND {$this->name}.patient_id = Patient.id 
                   AND (Patient.off_study_status <> '" .
                            Patient::OSINELIGIBLE . "'
                        OR Patient.off_study_status IS NULL)";

        $orderBy = " ORDER BY User.last_name";

        switch ($status) {
            case AppModel::SCRUBBED:
            case AppModel::TO_BE_RECODED:
            // for these 2 cases, just search for the appropriate status
                $where .= " AND {$this->name}.status = '$status'";
                break;
            case AppModel::RAW: 
            // patients whose file needs to be scrubbed
                $where .= 
                    " AND ({$this->name}.status = '" . AppModel::RAW . "'
                      OR {$this->name}.status = '" . AppModel::DOWNLOADED_SCRUB . 
                    "')";
                break;
            case AppModel::ASSIGNED_CODING:
            // patients whose files need initial coding done
                $where .= " AND 
                    ({$this->name}.status = '" . AppModel::ASSIGNED_CODING . "'
                     OR {$this->name}.status = '" . AppModel::CODER1_DONE . "'
                     OR {$this->name}.status = '" . AppModel::CODER2_DONE . "')";
                break;
            case AppModel::CODING_DONE:
            // patients whose files have had at least one coding done
                $where .= " AND 
                    ({$this->name}.status = '" . AppModel::CODING_DONE . "'
                     OR {$this->name}.status = '" . AppModel::TO_BE_RECODED . "'
                     OR {$this->name}.status = '" . AppModel::CODER1_DONE . "'
                     OR {$this->name}.status = '" . AppModel::CODER2_DONE . "')";
                break;
            default:	// other values are invalid
                $this->log("Bad case in switch $status");
                return null;
                break;
        }

        return $model->query($query[0] . $select . $query[1] . $join . 
                             $query[2] . $where . $orderBy);
    }

    /**
     * Get a list of coded items assigned to a particular user for coding
     * or recoding
     * @param id Id of the authorized user
     * @param status ASSIGNED_CODING, TO_BE_RECODED or CODING_DONE depending on
     *    whether we want those assigned for initial coding, recoding or
     *    those whose coding the user has already completed
     * @return a list of coded items assigned to the user
     */
    private function getAssigned(&$model, $id, $status) {
        if ($status == AppModel::TO_BE_RECODED) {
            $conditions = 
                   "(({$this->name}.coder1_id = $id OR
                      {$this->name}.coder2_id = $id) 
                     AND {$this->name}.status = '" . 
                         AppModel::TO_BE_RECODED . "')";
        } else if ($status == AppModel::CODING_DONE) {
            $conditions = 
                   "(({$this->name}.coder1_id = $id 
                     AND {$this->name}.status IN ('" . AppModel::CODER1_DONE . 
                         "', '" .  AppModel::CODING_DONE . 
                         "', '" .  AppModel::TO_BE_RECODED . "'))
                 OR ({$this->name}.coder2_id = $id 
                     AND {$this->name}.status IN ('" . AppModel::CODER2_DONE . 
                         "', '" .  AppModel::CODING_DONE . 
                         "', '" .  AppModel::TO_BE_RECODED . "')))";
        } else {
            $conditions = 
                   "(({$this->name}.coder1_id = $id 
                     AND {$this->name}.status IN ('" . AppModel::CODER2_DONE . 
                         "', '" .  AppModel::ASSIGNED_CODING . "'))
                 OR ({$this->name}.coder2_id = $id 
                     AND {$this->name}.status IN ('" . AppModel::CODER1_DONE . 
                         "', '" .  AppModel::ASSIGNED_CODING . "')))";
        }

        $conditions .= " AND Patient.test_flag <> 1
                         AND (Patient.off_study_status <> '" . 
                                 Patient::OSINELIGIBLE . "'
                              OR Patient.off_study_status IS NULL)";

        return $model->find('all', 
            array('conditions' => $conditions,
                  'order' => "{$this->name}.patient_id"));
    }

    /**
     * Get a list of participants who have coded items that were already coded by a particular user
     * @param id Id of the authorized user
     * @param adminUser True if they are an administrator
     * @return a list of participants who have coded items that were already 
     *    coded
     */
    function getAlreadyCoded(&$model, $id, $adminUser) {
        if ($adminUser) {	// just get them all
            return $this->getAll($model, $id, true, true, 
                                    AppModel::CODING_DONE);
        } else {
            return $this->getAssigned($model, $id, AppModel::CODING_DONE);
        }
    }

    /**
     * Get a list of participants who have coded items that need to be coded by a particular user
     * @param id Id of the authorized user
     * @param adminUser True if they are an administrator
     * @return a list of participants who have coded items that need
     *    to be coded
     */
    function getToBeCoded(&$model, $id, $adminUser) {
        if ($adminUser) {	// just get them all
            return $this->getAll($model, $id, true, true, 
                                    AppModel::ASSIGNED_CODING);
        } else {
            return $this->getAssigned($model, $id, AppModel::ASSIGNED_CODING);
        }
    }

    /**
     * Get a list of participants who have coded items that need to be recoded
     * @param id Id of the authorized user
     * @param adminUser True if they are an administrator
     * @return a list of participants who have coded items that need
     *    to be recoded
     */
    function getToBeRecoded(&$model, $id, $adminUser) {
        if ($adminUser) {	// just get them all
            return $this->getAll($model, $id, true, true, 
                                    AppModel::TO_BE_RECODED);
        } else {
            return $this->getAssigned($model, $id, AppModel::TO_BE_RECODED);
        }
    }

    /**
     * Get the number of answers for a particular set of fields in 
     * a category row
     * @param category Category in question
     * @param fields Fields to test
     * @param blank If true, count blank answers
     * @return the number of non-empty answers in the category for the 
     *    given fields
     */
    private function answers($category, $fields, $blank = true) {
        $total = 0;

        foreach ($fields as $field) {
            if ($blank || !empty($category[$field])) {
                $total++;
            }
        }

        return $total;
    }

    /**
     * Compute and return the % agreement between the two coders for a patient
     * @param patient The patient
     */
    private function computeAgreement($model, $patient) {
        $codingName = $model->codingName();
        $codingObject =& ClassRegistry::init($codingName);
        $codingCategoryName = $codingName . 'Category';
        $coding = $codingObject->findAllByPatientId(
            $patient['Patient']['id']);
        $codingIdName = $codingObject->$codingCategoryName->codingIdName();
        $agreementFieldNames = 
            $codingObject->$codingCategoryName->agreementFieldNames();
 
        $categories1 = $coding[0][$codingCategoryName];
        $categories2 = $coding[1][$codingCategoryName];

        $sortedCategories1 = Set::sort($categories1, '{n}.category_id', 'asc');
        $sortedCategories2 = Set::sort($categories2, '{n}.category_id', 'asc');

        $compared = 0;
        $numberAgreed = 0;
        $i = 0;   // index into sortedCategories2

        foreach($sortedCategories1 as $category1) {
            $category1Id = $category1['category_id'];

            while ($i < count($sortedCategories2) &&
                   $sortedCategories2[$i]['category_id'] < $category1Id)
            {
                $compared += $this->answers($sortedCategories2[$i],
                                            $agreementFieldNames);
                $i++;
            }

            if ($sortedCategories2[$i]['category_id'] != $category1Id) {
                $compared += $this->answers($category1, 
                                            $agreementFieldNames);
            } else {
                $category2 = $sortedCategories2[$i];

                foreach($agreementFieldNames as $fieldName) {
                    $answer1 = $category1[$fieldName];
                    $answer2 = $category2[$fieldName];
                    $compared++;

                    if ($answer1 === $answer2) {
                        $numberAgreed++;
                    }
                }

		$i++;
            }
        }

        // don't forget any leftover categories in the 2nd array
        while ($i < count($sortedCategories2)) {
            $compared += $this->answers($sortedCategories2[$i],
                                        $agreementFieldNames);
            $i++;
        }

        if ($compared == 0) {
            return 100;	// total agreement
        } else {
            return ($numberAgreed * 100) / $compared;
        }
    }

    /**
     * check if there was a coding mismatch for the patient.  This should
     * only be called when the last coder is done coding (so, not after the
     * first of two double coders is done).
     * @param patient The patient
     * @return true if there was a coding mismatch
     */
    private function codingMismatch($model, &$patient) {
        if (!$patient[$this->name]['double_coded_flag']) {
        // no mismatch if only one coder
            unset($patient[$this->name]['agreement']);
            return false;
        } else {
            $agreement = $this->computeAgreement($model, $patient);
            $patient[$this->name]['agreement'] = $agreement / 100;
            return $agreement < AGREEMENT_THRESHOLD;
        }
    }

function recomputeAgreement($model, $patient) {
    if ($patient[$this->name]['double_coded_flag'] && 
        in_array($patient[$this->name]['status'], 
                 array(AppModel::TO_BE_RECODED, AppModel::CODING_DONE))) 
    {
        return $this->computeAgreement($model, $patient);
    } else {
        return null;
    }
}

    /**
     * update the item for a patient after a coding is done
     * @param patient Patient data
     * @param authUserId Id of the user who did the coding
     */
    function updateAfterCoding(&$model, $patient, $authUserId) {
        // is this coder 1 or coder 2?
        $coder = ($patient[$this->name]['coder1_id'] == $authUserId) ? 1 : 2;

        // get the name of the timestamp field to update, and the new status
        $timestampField = "coder{$coder}_timestamp";
        $fieldsToUpdate = array('status', $timestampField);

        if ($patient[$this->name]['status'] == AppModel::TO_BE_RECODED) {
            // definitely done
            $timestampField = 'recoding_timestamp';
            $patient[$this->name]['status'] = AppModel::CODING_DONE;
        } else if ($patient[$this->name]['double_coded_flag'] &&
                   $patient[$this->name]['status'] == AppModel::ASSIGNED_CODING)
        {   // definitely not done
            $patient[$this->name]['status'] =
                $coder == 1 ? AppModel::CODER1_DONE :
                              AppModel::CODER2_DONE;
        } else {   // could be done
            $fieldsToUpdate[] = 'agreement';

            if ($this->codingMismatch($model, $patient)) {
                $patient[$this->name]['status'] = AppModel::TO_BE_RECODED;
            } else {
                $patient[$this->name]['status'] = AppModel::CODING_DONE;
            }
        }

        $patient[$this->name][$timestampField] = $model->currentGmt();
        $model->save($patient, true, $fieldsToUpdate);
    }

    /**
     * Is an item to be coded by a particular staff user?
     * @param item Coded item
     * @param authUserId Id of the staff user
     * @param recode Is the item to be recoded?
     * @return Whether the item is to be coded by the staff user.
     */
    function toBeCoded(&$model, $item, $authUserId, $recode) {
        $status = $item[$this->name]['status'];
        $coder1 = $item[$this->name]['coder1_id'];
        $coder2 = $item[$this->name]['coder2_id'];

        if ($recode && ($status != AppModel::TO_BE_RECODED)) {
            return false;
        } else if (!$recode && ($status == AppModel::TO_BE_RECODED)) {
            return false;
        }

        if ($status == AppModel::ASSIGNED_CODING ||
            $status == AppModel::TO_BE_RECODED)
        {
            return $coder1 == $authUserId || $coder2 == $authUserId;
        } else if ($status == AppModel::CODER1_DONE) {
            return $coder2 == $authUserId;
        } else if ($status == AppModel::CODER2_DONE) {
            return $coder1 == $authUserId;
        } else {
            return false;
        }
    }

    /**
     * Can a staff user review a particular coding?
     * @param item Coded item
     * @param codingNumber 1, 2 or 3 to indicate which coding (3 = recoding)
     * @param authUserId Id of the staff user
     * @param adminUser Is the staff user an administrator?
     * @return Whether the item can be reviewed by the staff user.
     */
    function canReviewCoding(&$model, $item, $codingNumber, $authUserId, 
                             $adminUser) 
    {
        $status = $item[$this->name]['status'];
        $coder1 = $item[$this->name]['coder1_id'];
        $coder2 = $item[$this->name]['coder2_id'];
        $doubleCoded = $item[$this->name]['double_coded_flag'];

        if ($codingNumber == 1) {
            return in_array($status, array(AppModel::CODER1_DONE, 
                                           AppModel::CODING_DONE, 
                                           AppModel::TO_BE_RECODED)) &&
                   ($adminUser || $coder1 == $authUserId);
        } else if ($codingNumber == 2) {
            return in_array($status, array(AppModel::CODER2_DONE, 
                                           AppModel::CODING_DONE, 
                                           AppModel::TO_BE_RECODED)) &&
                   $doubleCoded &&
                   ($adminUser || $coder2 == $authUserId);
        } else {
            return in_array($status, array(AppModel::CODING_DONE)) &&
                   $doubleCoded &&
                   ($adminUser || $coder1 == $authUserId || 
                    $coder2 == $authUserId);
        }
    }
}
