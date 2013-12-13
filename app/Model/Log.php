<?php
/** 
    * Log class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *   
*/
class Log extends AppModel
{
  var $name = "Log";
  var $useTable = 'logs';
  
  function findWithParams($params) 
  {
    $conditions = array();
    foreach($params as $param => $value) {
      if ($value != 0) {
        $conditions["Log." . $param . "="] = $value;
      }
    }
    return $this->paginate("Log", $conditions);
  }
  
  var $belongsTo = array('User' =>
                          array('className'    => 'User',
                                'conditions'   => '',
                                'order'        => '',
                                'dependent'    =>  true,
                                'foreignKey'   => 'user_id'
                          )
                    );
  
  var $displayColumns = array(
                          "User"     => "user_id",
                          "Date and Time"   => "time",
                          "Controller"      => "controller",
                          "Action"          => "action",
                          "Additional Data" => "params",
                          "IP Address" => "ip_address",
                          "Browser Information" => "user_agent"
                        );
  

    /**
     *
     */
    function beforeSave($options = Array()) {
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), this->data " . print_r($this->data, true), LOG_DEBUG);

        if (defined('IP_ADDRESS_OBFUSCATOR')){

            if (!empty($this->data['Log']['ip_address'])) {
//                $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), IP_ADDRESS_OBFUSCATOR", LOG_DEBUG);

                $command = "echo " . $this->data['Log']['ip_address']
                            . " | " . IP_ADDRESS_OBFUSCATOR;
                exec($command, $ip_obfuscated, $err); 
                if (!empty($err)){ 
                    $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), IP_ADDRESS_OBFUSCATOR", LOG_ERROR);
                }
                else {
                    $this->data['Log']['ip_address'] 
                        = $ip_obfuscated[0];
                }
            }
        }

        return true;
    }
}
