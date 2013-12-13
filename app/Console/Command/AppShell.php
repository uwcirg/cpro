<?php
/**
 * AppShell file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Shell', 'Console');

/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 * @package       app.Console.Command
 */
App::uses('AppController', 'Controller');
class AppShell extends Shell {


    // Initialze an AppController so we have access to instanceModelOverrides through it
    function startup(){
        parent::startup();
        if ($this->uses){
            $this->Controller = new AppController();
            $this->Controller->constructClasses();
        }

        foreach($this->uses as $modelName)
            if ($this->Controller->{$modelName})
                $this->$modelName = $this->Controller->{$modelName};
    }

    function main() {
        $logName = Inflector::underscore(str_replace('Shell', '', get_class($this))) . '.log';

        $this->out('Output written to ' . LOGS . $logName . PHP_EOL);
        $log = fopen(LOGS . $logName, 'a');
        $this->log = $log;

        if (!$log)
            die("Failed to open file $logName");
    }


    /*
    * Returns a project's URL
    * Useful for sending links via email
    */
    function getAppURL(){
        // Determine URL to send in messages
        $prodUrlRegex = '/\/(.[^\/]+washington.edu)\/htdocs\/?(.*?)\/?app/';
        $matches = Array();
        $url = APP;
        // This will only work on production systems without symlinks
        $match = preg_match($prodUrlRegex, APP, $matches);

        if ($match)
            $url = join('/', array_filter(array_slice($matches, 1)));
        else if (!Configure::read('isProduction')) {
         // Try to find the corresponding symlink on the server, from the dev instance
            $command = "find -L /srv/www -samefile '" . APP . "' 2> /dev/null -maxdepth 5 -type d -print -quit";
            $results = Array();
            exec($command, $results);
            if (count($results) == 1){
                // $devUrlRegex = '/\/(.[^\/]+washington.edu)\/.*htdocs\/([^\/]+)/';
                $devUrlRegex = '/\/(.[^\/]+washington.edu)\/htdocs\/?(.*?)\/?app/';
                $match = preg_match($devUrlRegex, $results[0], $matches);
                if (count($matches) == 3)
                    $url = 'https://' . $matches[1] . '/' . $matches[2] . '/';
            }
        }

        // Trim trailing slash
        if ($url[strlen($url)-1] == '/')
            $url = substr($url, 0, -1);

        // Remove beginning slash
        if ($url[0] == '/')
            $url = substr($url, 1);
        return $url;

    }
}
