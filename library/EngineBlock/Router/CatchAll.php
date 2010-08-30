<?php
 
class EngineBlock_Router_CatchAll extends EngineBlock_Router_Abstract
{
    public function __construct($moduleName, $controllerName, $actionName)
    {
        $this->_moduleName      = ucfirst($moduleName);
        $this->_controllerName  = ucfirst($controllerName);
        $this->_actionName      = $actionName;
    }

    public function route($uri)
    {
        return true;
    }
}
