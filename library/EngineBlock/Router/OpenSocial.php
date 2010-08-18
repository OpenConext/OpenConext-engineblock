<?php
 
class EngineBlock_Router_OpenSocial extends EngineBlock_Router_Abstract
{
    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        // Homepage (URI: /)
        if (!isset($urlParts[0])) {
            $this->_moduleName     = 'Social';
            $this->_controllerName = 'Index';
            $this->_actionName     = 'index';
            return true;
        }

        if ($urlParts[0] !== 'social') {
            return false;
        }

        $this->_moduleName      = 'Social';
        $this->_controllerName  = 'Rest';
        $this->_actionName      = 'index';
        $this->_actionArguments = array(
            implode('/', array_slice($urlParts, 1))
        );
        
        return true;
    }
}
