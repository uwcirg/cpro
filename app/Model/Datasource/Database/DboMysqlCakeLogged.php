<?php
// load lib
uses('model/datasources/dbo/dbo_mysql');
/**
* @author RainChen @ Sun Feb 24 17:21:35 CST 2008
* @uses usage
* @link http://cakeexplorer.wordpress.com/2007/10/08/extending-of-dbosource-and-model-with-sql-generator-function/
* @access public
* @param parameter
* @return return
* @version 0.1

* To use this, set 'driver' => 'mysql_cake_logged' in database.php,
*   and Configure::write('Cake.logQuery', true) in the core config
*/
class DboMysqlCakeLogged extends DboMysql
{
    function logQuery($sql)
    {
        $return = parent::logQuery($sql);
        if(Configure::read('Cake.logQuery'))
        {
            // ignore some log-cluttering sql calls
            if (!strstr($sql, 'SHOW FULL COLUMNS FROM')){

                debugger::log("SQL[call#$this->_queriesCnt]:".$sql . "\nHere's the stack for that call: " . Debugger::trace());
            }
        }
        return $return;
    }
}
?>
