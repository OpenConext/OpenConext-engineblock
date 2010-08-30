<?php

/**
 * Routes /authorization/ urls
 */
class EngineBlock_Router_Authentication extends EngineBlock_Router_Default
{
    protected $DEFAULT_MODULE_NAME = 'Authentication';

    protected $_controllerMapping = array(
        'idp'   =>'IdentityProvider',
        'sp'    =>'ServiceProvider',
    );

    public function route($uri)
    {
        parent::route($uri);
        // Only route /authentication/ urls
        return (strtolower($this->_moduleName)===strtolower($this->DEFAULT_MODULE_NAME));
    }

    public function getControllerName()
    {
        if (isset($this->_controllerMapping[$this->_controllerName])) {
            return $this->_controllerMapping[$this->_controllerName];
        }

        return ucfirst($this->_controllerName);
    }
}
