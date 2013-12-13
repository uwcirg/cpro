<?php 

class CakeTasterView extends View 
{
    function __construct(&$controller)
    {
        parent::__construct($controller);        
    }
    
    function _getViewFileName($action)
    {
        return str_replace(VIEWS, CAKE_TASTER_VIEWS, parent::_getViewFileName($action));                
    }
    
    function _getLayoutFileName()
    {
        return str_replace(ROOT.DS.LIBS.'view'.DS.'templates'.DS, CAKE_TASTER_VIEWS, parent::_getLayoutFileName());
    }
}

?>