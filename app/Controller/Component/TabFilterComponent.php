<?php
/** 
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * Named "TabFilter" to distinguish it from the pre-existing "Tab" Helper
    *
*/
// TODO rename this to xomething like "TabComponent" or "TabNavComponent"
class TabFilterComponent extends Component
{
    var $uses = array('User');

    //var $components = array("DhairAuth");

    var $tabs_for_layout = array(); // 
    var $tabsToDisable = array(); // labels of tabs to disable  


    function __construct(ComponentCollection $collection, $settings = array()) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...)", LOG_DEBUG);

        parent::__construct($collection, $settings);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...), done", LOG_DEBUG);
    }


    //called before Controller::beforeFilter()
    function initialize(Controller $controller) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...)", LOG_DEBUG);

        //$this->log("TabFilterComponent.initialize(...).", LOG_DEBUG);
        // saving the controller reference for later use
        $this->controller = $controller;
        //$this->enabled = true;

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(...), done", LOG_DEBUG);
    }

 
    //called after Controller::beforeFilter()
    function startup(Controller $controller) {
        //$this->log("TabFilterComponent.startup(...).", LOG_DEBUG);
    }


    /** 
     *
     * To set the tabs to display: call this from the  
     * controller. Or call $this->show_normal_tabs to show the project 
     * default tabs.
     * The last call to show_tabs
     * or show_normal_tabs executed during an action will set the value used 
     * in the layout.
     *
     * To set the "selected" tab (the one that appears with a different style 
     * that appears
     * attached to the content below, use $this->selected_tab('Some tab'); 
     * This again sets
     * a variable used in the layout and sent to the tab helper. 
     * 
     * The TabHtml helper is called from the layout with the variable "tabs_for_layout," an 
     * array of strings with the internal names of the tabs that should be 
     * shown.
     *
     * @param $tabs an array of tab labels eg('One tab', 'Another tab')
     *
     */
    function show_tabs($tabs) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for " . $this->controller->request->params['controller'] . '/' . $this->controller->request->params['action'], LOG_DEBUG);

        if (!is_array($tabs)){
            $args = func_get_args();
        }
        else $args = $tabs;

        foreach($args as $tabID){
            $tabMap = Configure::read('tabControllerActionMap');
            $controller = $tabMap[$tabID]['controller'];
            $action = $tabMap[$tabID]['action'];
            $isAuthorized = $this->controller->DhairAuth->isAuthorizedForUrl($controller,$action); 

            if (!$isAuthorized)
            {
                unset($args[array_search($tabID, $args)]);
            }
            elseif ($controller == 'surveys' && $action == 'index'
                    && empty($this->controller->session_link)){
                unset($args[array_search($tabID, $args)]);
            }

        }

        $this->tabs_for_layout = $args;
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(...) tabs_for_layout after auth filter: " . print_r($this->tabs_for_layout, true) /**. ", here's the stack: " . Debugger::trace() */, LOG_DEBUG);

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(...), next: calculateTabsToRemoveOrDisable()" /*. Debugger::trace()*/ , LOG_DEBUG);
        $this->calculateTabsToRemoveOrDisable();
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(...), just did calculateTabsToRemoveOrDisable()" /*. Debugger::trace()*/ , LOG_DEBUG);
        $this->controller->set('tabsToDisable', $this->tabsToDisable);

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(...), here's tabs_for_layout: " . print_r($this->tabs_for_layout, true) /*. Debugger::trace()*/ , LOG_DEBUG);
        $this->controller->set('tabs_for_layout', $this->tabs_for_layout);
    }// function show_tabs($tabs) {


    /**
    * @param $labelOrTarget Can be either the tab label (eg "Home") 
    *    or the target (cake's std link target array, eg ('controller' => 'users', 'action' => 'index')
    */
    function selected_tab($labelOrTarget)
    {
        $label;
        $target;
        $tabMap = Configure::read('tabControllerActionMap');

        if (is_array($labelOrTarget)){
            $target = $labelOrTarget;
            // see if $labelOrTarget is a target
            $label = array_search($labelOrTarget, $tabMap);
        }
        else {
            $label = $labelOrTarget;
            $target = $tabMap[$label];
        }
        //$this->log("selected_tab(tab) top, here's tab: $label", LOG_DEBUG);
        $this->controller->set('selected_tab', $label);
    }



    /** call this function to show the normal tabs 
     *  the "normal" tabs are everything but special tabs like "
     */
    function show_normal_tabs() {
        //$this->log("show_normal_tabs() top", LOG_DEBUG);
        $this->show_tabs(Configure::read('tabs_order_default'));
    }


    /**
     *
     */
    function calculateTabsToRemoveOrDisable(){
        //$this->log("calculateTabsToDisable() from TabFilterComponent. ", LOG_DEBUG);
        return; 
    }

}
