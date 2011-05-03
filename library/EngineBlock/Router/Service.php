<?php

/**
 * Route /service/ requests to the Service module with the Rest controller.
 */
class EngineBlock_Router_Service extends EngineBlock_Router_Abstract
{
    protected $DEFAULT_ACTION_NAME     = "index";

    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        if ($urlParts[0] !== 'service') {
            return false;
        }

        $this->_moduleName      = 'service';
        $this->_controllerName  = 'rest';
        if (isset($urlParts[1]) && !empty($urlParts[1])) {
            $this->_actionName      = $urlParts[1];
        }
        else {
            $this->_actionName      = $this->DEFAULT_ACTION_NAME;
        }
        $this->_actionArguments = array(
            implode('/', array_slice($urlParts, 1))
        );
        
        return true;
    }
}
