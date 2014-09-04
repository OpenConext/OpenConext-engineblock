<?php

/**
 * Route all /social/ URLs to the Social module with the Rest controller
 */
class EngineBlock_Router_OpenSocial extends EngineBlock_Router_Default
{
    public function __construct()
    {
        $this->_defaultModuleName = "Social";
        $this->_defaultControllerName = "Rest";
    }

    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        if (!isset($urlParts[0]) || $urlParts[0] !== 'social') {
            return false;
        }

        $this->_moduleName      = 'Social';

        if (count($urlParts)===1) {
            $this->_controllerName = 'Index';
            $this->_actionName     = 'Index';
        }
        else {
            $this->_controllerName  = 'Rest';
            $this->_actionName      = 'Index';
            $this->_actionArguments = array(
                implode('/', array_slice($urlParts, 1))
            );
        }
        return true;
    }
}
