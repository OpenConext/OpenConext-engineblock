<?php
 
class EngineBlock_Router_OpenSocial extends EngineBlock_Router_Abstract
{
    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        if (!isset($urlParts[0])) {
            return false;
        }

        if ($urlParts[0] !== 'social') {
            return false;
        }

        $this->_moduleName      = 'social';
        $this->_controllerName  = 'rest';
        $this->_actionName      = 'index';
        $this->_actionArguments = array(
            implode('/', array_slice($urlParts, 1))
        );
    }
}
