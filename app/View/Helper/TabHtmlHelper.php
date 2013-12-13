<?php
/** 
    * TabHtmlHelper class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * See /app/app_controller.php for:
    *   - information on setting the visible and active tabs for a given action.
    *   - tabControllerActionMap definition
    * See /app/layout/ (various layouts).ctp to change where or how tha tabs are displayed within the page.
    * See below for the functions that return the tabs and the $tabs array that sets
    * the tabs that are available to be shown, their internal and display names, and the link they lead to.
*/

class TabHtmlHelper extends Helper
{
  
  var $helpers = array("Html");
  var $before_tabs = '<!-- start_pdf_no --><div class="navbar" id="mainNav">
        <div class="navbar-inner">
        <button type="button" class="btn btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <div class="nav-collapse collapse">
            <ul class="nav">';
 var $after_tabs = '</ul>
          </div>
        </div>
      </div><!-- end_pdf_no -->';

  var $tabsControllerActionMap;

  /**
   *
   */
  function before_tabs()
  {
    return $this->before_tabs;
  }

  /**
   *
   */
  function after_tabs()
  {
    return $this->after_tabs;
  }

  /**
   * @param $labelOrTarget Can be either the tab label (eg "Home") 
   *    or the target (cake's std link target array, eg ('controller' => 'users', 'action' => 'index')
   */
  function tab($labelOrTarget, $classString = '')
  {
    $label;
    $target;

    if (array_key_exists($labelOrTarget, $this->tabControllerActionMap)) {
        $label = $labelOrTarget;
        $target = $this->tabControllerActionMap[$label];
    }
    else {
        // see if $labelOrTarget is a target
        $label = array_search($labelOrTarget);
        if ($label === false) {
            return "";
        }
        $target = $labelOrTarget;
    }

    $tab_html = "<li $classString>";
    $tab_html .= $this->Html->link(
                                __($label), 
                                $target, array('class' => $target));; 
    $tab_html .= "</li>";
    //$tab_html .= "<li><div style='margin: 10px 6px; color: #333'>></div></li>";

    return $tab_html;
  }
 

  /**
   *
   *
   * @param $tabs array of labels eg [0] => 'My Home', [1] => 'Report My Experiences' 
   * @param @selected_tab label eg 'My Home'
   * @param @tabControllerActionMap array eg 
        ['My Home'] => Array('controller' => 'users', 'action' => 'index'),
        ['Report My Experiences'] => Array('controller' => 'surveys', 'action' => 'index'),

   * @param $tabsToDisable array of labels eg ('Statistics', 'Influential Factors') 
   *
   */
  function display($tabs, $selected_tab, 
                    $tabControllerActionMap, $tabsToDisable = array(), $is_staff)
  {
    //$this->log("TabHtmlHelper.display w/ args tabs:" . print_r($tabs, true) . "; selected_tab:" . $selected_tab . "; tabControllerActionMap:" . print_r($tabControllerActionMap, true) . "tabsToDisable:" . print_r($tabsToDisable, true) . "; " . Debugger::trace(), LOG_DEBUG);

    $this->tabControllerActionMap = $tabControllerActionMap; 

    $tabs_html = $this->before_tabs();
    if ($tabs){
        foreach($tabs as $tab) {
            $classString = "class='";
            if ($tab == $selected_tab) {
                $classString .= "active ";
            }
            elseif (in_array($tab, $tabsToDisable)) {
                $classString .= "disabled ";
            }
            if (defined('USE_CHEVRONS') && USE_CHEVRONS && !$is_staff) {
                $classString .= "chevron-link"; 
            }
            $classString .= "'";
            $tabs_html .= $this->tab($tab, $classString);
        }
    }
    $tabs_html .= $this->after_tabs();
    return $this->output($tabs_html);
  }
    
}
