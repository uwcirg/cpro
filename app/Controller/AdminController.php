<?php
/**
    * Admin Controller
    *
    * Actions for admins only
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class AdminController extends AppController
{
  var $uses = array("User", "Patient");
  var $components = array('DhairDateTime', 'Password');
  var $useTable = false;
  var $helpers = array("Html", "Form");
  
  // arrays of cross-user acos the current user has some CRUD permissions on
  var $crudableUserAcos = array();

    function beforeRender() {
        $this->TabFilter->show_normal_tabs();
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
            array(
							'jquery.dataTables.js',
							'cpro.datatables.js'
						));
        parent::beforeRender();
    }

  function beforeFilter() {
    parent::beforeFilter();

    $this->initPatientsTabNav();

  }

  function _populateCrudableUserAcos(){

    if ($this->DhairAuth->userAclLeafs) {

        $this->crudableUserAcos['read'] =
                $this->DhairAuth->getCRUDableSubAcos('acoUsers', 'read');
        $this->crudableUserAcos['create'] =
                $this->DhairAuth->getCRUDableSubAcos('acoUsers', 'create');
    }
  }

  /** Index action
   *
   * before filter: loggedIn redirects to Home if logged in
   *
   * otherwise, display main layout with login screen
   */
  function index()
  {

  }

    /**
     *
     */
    function kiosk($setTo = null) {
        if(isset($setTo)) {
            $this->setKiosk($setTo);
        }

        $isKiosk = $this->isKiosk();

        if(isset($setTo)) {
	        $this->Session->setFlash('Kiosk mode is now ' . 
	                             ($isKiosk ? 'on' : 'off'));
	    }

        $this->set('isKiosk', $isKiosk);
    }

  /**
  * This should only be made accessible to aroCentralSupport and aroAdmin
  */
  function createStaff(){

    $this->_populateCrudableUserAcos();

    $creatableUserAcos = $this->crudableUserAcos['create'];
    $this->DhairLogging->logArrayContents(
                            $creatableUserAcos, "creatableUserAcos");
    $this->set('creatableUserAcos', $creatableUserAcos);      
    foreach ($creatableUserAcos as $creatableUserAco){
        $usersInAco = $this->DhairAuth->getUsersInAcoUsers(
                        $creatableUserAco, false); 
        $creatableUserAcosAndMembers[$creatableUserAco] = $usersInAco;
    }
    $this->set('creatableUserAcosAndMembers', 
                $creatableUserAcosAndMembers);
    
    $readableUserAcos = $this->crudableUserAcos['read'];
    $this->DhairLogging->logArrayContents(
                            $readableUserAcos, "readableUserAcos");
    $this->set('readableUserAcos', $readableUserAcos);      
    foreach ($readableUserAcos as $readableUserAco){
        $usersInAco = $this->DhairAuth->getUsersInAcoUsers(
                        $readableUserAco, false); 
        $readableUserAcosAndMembers[$readableUserAco] = $usersInAco;
    }
    $this->set('readableUserAcosAndMembers', 
                $readableUserAcosAndMembers);
  }

    /**
     * Check whether we are using the test database, and abort if not
     */
    private function checkTestInstance() {
        if (!$this->testInstance) {
	    $this->Session->setFlash('This function can only be applied to
	                              the test database');
            $this->redirect($this->referer());
        }
    }

    /** Directory where snapshots are kept */
    const DB_SNAPSHOT_DIR = 'dbsnapshots';

    /** Prefix of db snapshots */
    const SNAPSHOT_PREFIX = TEST_DB_NAME;

    /** User that has access only to test db */
    const DB_USER = 'esrac_test_user';

    /**
     * Get the names of the db snapshots, sorted
     */
    private function getSnapshotNames() {
        $folder = new Folder(WWW_ROOT . self::DB_SNAPSHOT_DIR);
	return $folder->find(self::SNAPSHOT_PREFIX . '.*', true);
    }

    /**
      * Show test database snapshots, if we are using the test database
      */
    function viewDatabaseSnapshots() {
        $this->checkTestInstance();

	$this->set('snapshots', $this->getSnapshotNames());
    }

    /**
     * Dump the test database into a file
     */
    function saveDatabase() {
        $this->checkTestInstance();
	// get date in the default format, with spaces replaced
	$datetime = str_replace(' ', '_', 
	                        date($this->DhairDateTime->getDefaultFormat()));

	$filename = WWW_ROOT . self::DB_SNAPSHOT_DIR . '/' .
	            self::SNAPSHOT_PREFIX . ".$datetime.sql";

        exec('mysqldump -u ' . self::DB_USER . " -p{$this->testPassword} " .
             TEST_DB_NAME . " > $filename");
        $this->redirect($this->referer());
    }

    /**
     * Load the test database from a file
     * @param fileNumber Sanity check:  filename should be the $fileNumber'th
     *    file in the snapshots directory
     */
    function reloadDatabase($fileNumber) {
        $this->checkTestInstance();
        $fileNumber = intval($fileNumber);

        if (empty($this->request->params['url']) || 
	    empty($this->request->params['url']['filename'])) 
        {
	    $this->Session->setFlash('Missing filename');
            $this->redirect($this->referer());
        }

        $filename = $this->request->params['url']['filename'];
	$snapshotNames = $this->getSnapshotNames();

	if ($fileNumber < 0 || $fileNumber >= count($snapshotNames) || 
	    strcmp($filename, $snapshotNames[$fileNumber]) != 0)
        {
	    $this->Session->setFlash("Filename $filename does not match 
	                              number $fileNumber.  Please try again"); 
            $this->redirect($this->referer());
        }

	$fullFilename = WWW_ROOT . self::DB_SNAPSHOT_DIR . '/' . $filename;

        exec('mysql -u ' . self::DB_USER . " -p{$this->testPassword} " .
             TEST_DB_NAME . " < $fullFilename");
	$this->Session->setFlash("Database reloaded from $filename");
        $this->redirect($this->referer());
    }
  
    /**
     * Get a list of all non-admin users
     * @param sortField Field to sort on (default = User.last_name)
     * @param sortDirection Direction (asc/desc, default = asc)
     */
    function viewNonAdminUsers($sortField = 'User.username', 
                               $sortDirection = 'asc') 
    {
        $centralSupport = 
            $this->DhairAuth->centralSupport($this->Auth->user('id'));
        $users = $this->User->getNonAdmin();

        $users = Set::sort($users, '{n}.' . $sortField, $sortDirection);
        $fields = array('User.username', 'UserAclLeaf.acl_alias');
        $sortDirections = array_fill_keys($fields, 'asc');

        if ($sortDirection == 'asc') {
            $sortDirections[$sortField] = 'desc';
        }

        $this->set('canResetPassword', $centralSupport);
        $this->set('users', $users);
        $this->set('sortDirections', $sortDirections);
    }

    /**
     * Check if a user's password can be reset, abort if it cannot
     * @param id Id of the user whose password is to be reset
     */
    private function userPasswordCheck($id) {
        if (empty($id) || $this->DhairAuth->centralSupport($id)) {
	    $this->Session->setFlash('Not a valid user id!');
            $this->redirect($this->referer());
        }
    }

    /**
     * Reset a user's password
     * @param id Id of the user
     */
    function resetPassword($id = null) {
        $this->userPasswordCheck($id);

        $tempPassword = $this->Password->resetPassword($id); 
        $this->Session->setFlash("Password has been changed to $tempPassword.
             User will be prompted to change password on login.");
        $this->redirect('viewNonAdminUsers');
    }
}
?>
