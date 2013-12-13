<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
//App::uses('Sanitize', 'Utility');
class Webkey extends AppModel {

    var $name = "Webkey";
    var $useTable = "webkeys";
    var $primaryKey = "id";

    var $belongsTo = array("User");


    /**
     */
    function beforeValidate($options = array()){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...)", LOG_DEBUG);

        if (empty($this->data["Webkey"]["text"])) {

            $txt = '';
            $clash = 1;
            while ($clash){
                $txt = CProUtils::generateRandomString(); 
                $clash = $this->findByText($txt);
            }

            $this->data["Webkey"]["text"] = $txt;
        }
        return true;
    }

    function beforeSave($options=Array()){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); just entered, heres this->id:" . $this->id, LOG_DEBUG);
        // find login_assist records for this patient where used_on null, and update used_on to some date in the distant future
        if (isset($this->data["Webkey"]['purpose']) 
            && ($this->data["Webkey"]['purpose'] == 'login_assist')){
            $this->updateAll(array('Webkey.used_on'
                                    => "'" . self::DISTANT_FUTURE_DT . "'"), 
                                    //=> Sanitize::escape(self::DISTANT_FUTURE_DT)),// hmm doesn't work 
                            array('Webkey.user_id' 
                                    => $this->data["Webkey"]["user_id"], 
                                    'Webkey.purpose' => 'login_assist', 
                                    'Webkey.used_on' => null), 
                            false // ie don't cascade
            );    
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(...), just did updateAll used_on to DISTANT_FUTURE_DT", LOG_DEBUG);
        }

        // Automatically set the sent_on datetime if it's not set
        if (!isset($this->data['Webkey']['sent_on']) or !$this->data['Webkey']['sent_on'])
            $this->data['Webkey']['sent_on'] = gmdate(MYSQL_DATETIME_FORMAT);

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...); returning, heres this->id:" . $this->id, LOG_DEBUG);
        return true;
    }


}
?>
