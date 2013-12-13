<?php
/**
 *
 */
class CProUtils extends Object 
{

    /**
     */
    static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


    /**
     * Use this when their might be an overridden version of the view file:
     *   $this->render($this->getInstanceSpecificViewName());
     * If views/<controller name>/<action>_<INSTANCE_ID>.ctp exists, a string for that action will be returned.
     * else simply return the action name 
     * @param $controller - the controller name
     * @param $action - the action name
     */
    static function getInstanceSpecificViewName($controller, $action){

        $returnVal = null;

        if (file_exists(APP . 'View' . DS . $controller . DS
                            . $action . '_' . INSTANCE_ID . '.ctp')){
            $returnVal = $action . '_' . INSTANCE_ID;
        }
        else{
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "($action), the file " . APP . 'View' . DS . $controller . DS . $action . '_' . INSTANCE_ID . '.ctp' ." does not exist.", LOG_DEBUG);
            $returnVal = $action;
        }
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "($action), returning $returnVal", LOG_DEBUG);
        return $returnVal;
    }// static function get_instance_specific_view_name($action = null){

    static function getInstanceSpecificCSSName(){
        if (file_exists(CSS . 'cpro.' . INSTANCE_ID . '.css'))
            return 'cpro.' . INSTANCE_ID;
    }

    /**
     *
     */
    static function getInstanceSpecificEmailName($emailTemplateName, $preferredFormat='html'){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($emailTemplateName, $preferredFormat), called", LOG_DEBUG);

        $dir = APP . 'View' . DS . 'Emails' . DS . $preferredFormat;

        // Try finding instance-specific email template
        if (file_exists(
            $dir . DS . $emailTemplateName . '_' . INSTANCE_ID . '.ctp'
        ))
            return $emailTemplateName . '_' . INSTANCE_ID;

        // Try finding generic email template
        else if (file_exists(
            $dir . DS . $emailTemplateName . '.ctp'
        ))
            return $emailTemplateName;

        return null;
    }


    /**
     * Usage: 
        $this->get_instance_specific_js_name('check.browser.compat') 
     * @param $jsfile filename w/out .js extension, eg 'check.browser.compat'
     * @return js filename w/out .js extension, eg 'check.browser.compat_p3p'
     */
    static function get_instance_specific_js_name($jsfile){

        $returnVal = '';
        if (file_exists(JS . $jsfile . '_' . INSTANCE_ID . '.js')){
            $returnVal = $jsfile . '_' . INSTANCE_ID;
        }
        elseif (file_exists(JS . $jsfile . '.' . INSTANCE_ID . '.js')){
            $returnVal = $jsfile . '.' . INSTANCE_ID;
        }
        elseif (file_exists(JS .  $jsfile . '.js')){
            $returnVal = $jsfile;
        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "($jsfile) returning $returnVal. Here's JS:" . JS, LOG_DEBUG);

        return $returnVal;

    }// static function get_instance_specific_js_name($jsfile){



}
