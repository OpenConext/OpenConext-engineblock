<?php

/**
 * Route all /social/ URLs to the Social module with the Rest controller
 */
class EngineBlock_Router_OpenSocial extends EngineBlock_Router_Abstract
{
    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        if ($urlParts[0] !== 'social') {
            return false;
        }
        $this->_moduleName      = 'social';

        if (count($urlParts)===1) {
            $this->_controllerName = 'index';
            $this->_actionName     = 'index';
        }
        else {
            $this->_controllerName  = 'rest';
            $this->_actionName      = 'index';
            $this->_actionArguments = array(
                implode('/', array_slice($urlParts, 1))
            );
        }
        return true;
    }
}
