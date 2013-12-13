<?
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class InstanceSpecificsHelper extends Helper {


    /**
     * Use this from the view eg 
            $this->InstanceSpecifics->echo_instance_specific_elem('blah');
     * (instead of eg $this->element('blah'); )
     * If views/elements/blah_<INSTANCE_ID>.ctp exists, that will be echoed.
     * elseif views/elements/blah.ctp exists, that will be echoed.
     * else do nothing
     * @param $elem_name the base name of the element, eg 'intro'
     */
    function echo_instance_specific_elem($elem_name, $vars = array()){

        if (file_exists(
                APP . 'View' . DS . 'Elements' . DS . $elem_name . '_' . INSTANCE_ID . '.ctp')){
            // $this->log("echo_instance_specific_elem($elem_name); instance-specific file exists: " . APP . 'View' . DS . 'Elements' . DS . $elem_name . '_' . INSTANCE_ID . '.ctp', LOG_DEBUG);
            echo $this->_View->element($elem_name . '_' . INSTANCE_ID, $vars);
        }
        elseif (file_exists(APP . 'View' . DS . 'Elements' . DS . $elem_name . '.ctp')){
            //$this->log("echo_instance_specific_elem($elem_name); general file exists: " . APP . 'View' . DS . 'Elements' . DS . $elem_name . '.ctp', LOG_DEBUG);
            echo $this->_View->element($elem_name, $vars);
        }
        else {
            //$this->log("echo_instance_specific_elem($elem_name); didn't find any matches in " . APP . 'View' . DS . 'Elements' . DS, LOG_DEBUG);
        }

    }// function echo_instance_specific_elem($elem_name){


}
