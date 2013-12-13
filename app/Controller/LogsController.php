<?php
/**
    * Logs Controller
    *
    * Shows history of logged actions
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
 */
class LogsController extends AppController {

	var $name = 'Logs';
	var $uses = array("User", "Clinic");
    var $paginate = array(
        'limit' => 100,
        'order' => array(
            'Log.time' => 'asc'
        )
    );

	
    /**
     *
     */
	function index($log_user_id = null, $limit = 1000)
	{
        $this->initPatientsTabNav();
        //TODO impl setting of "limit" - easiest via session var, 
        //  because passedArgs are clashing w/ paginator->sort
        //      and paginator->sort doesn't work w/ 2nd params well... 
        $authdUserId = $this->Auth->user('id');
        if (! $this->DhairAuth->centralSupport($authdUserId)){
            if ($log_user_id == null){
                $this->Session->setFlash('Must specify patient id!');
                $this->redirect($this->referer());
            }
            if (! $this->DhairAuth->validPatientId($log_user_id, $authdUserId)){
                $this->Session->setFlash('Not a valid patient id!');
                    $this->redirect($this->referer());
            }      
            $this->set("centralSupport", false);  
        }
        else $this->set("centralSupport", true);

        $params = array();
        if ($log_user_id) {
	        $params["user_id"] = $log_user_id;
	    }

        $conditions = array();
        foreach($params as $param => $value) {
            if ($value != 0) { 
                $conditions["Log." . $param . " = "] = $value; 
            } 
        }

       $this->paginate["limit"] = $limit;

        // unbind the join for performance reasons
        $this->Log->unbindModel(array("belongsTo" => array("User")), false);
        $logs = $this->paginate("Log", $conditions);
        //$this->log("logs = " . print_r($logs, true), LOG_DEBUG); 
 
	    $this->set("logs", $logs);
	    $this->set("users", $this->User->find('all', array('recursive' => -1)));
	  
        $this->set("cols", $this->Log->displayColumns);

        if (isset($params["user_id"])){
            $this->set("log_user_id", $params["user_id"]);
        }
        else $this->set("log_user_id", null);

        $this->TabFilter->show_normal_tabs();
        $this->jsAddnsToLayout = array_merge($this->jsAddnsToLayout,
					array(
						'jquery.dataTables.js',
						'cpro.datatables.js'
					));
    } // function index(...)


    function add(){
        if (!$this->request->isAjax()) return;

        $result = array(
            'ok' => false,
            'message' => 'error saving log entry',
            // 'debug' => $this->data,
        );
        $this->viewVars = &$result;
        $this->set(array('_serialize' => array_keys($result)));

        $data = $this->data;
        unset($data['id']);

        if ($this->Log->save($data)){
            $result['ok'] = true;
            $result['message'] = 'log entry saved successfully';
        }
    }
}

?>
