<?php

/**
 * Routes /authorization/ urls
 */
class EngineBlock_Router_Authentication extends EngineBlock_Router_Default
{
    const DEFAULT_MODULE_NAME = 'Authentication';

    protected $_controllerMapping = array(
        'Idp'   =>'IdentityProvider',
        'Sp'    =>'ServiceProvider',
    );

    public function route($uri)
    {
        parent::route($uri);
        // Only route /authentication/ urls
        return ($this->_moduleName === $this->_defaultModuleName);
    }

    public function getControllerName()
    {
        if (isset($this->_controllerMapping[$this->_controllerName])) {
            return $this->_controllerMapping[$this->_controllerName];
        }

        return $this->_controllerName;
    }
}
