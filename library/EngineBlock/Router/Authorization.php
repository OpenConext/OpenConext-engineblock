<?php
 
class EngineBlock_Router_Authorization extends EngineBlock_Router_Default
{
    protected $DEFAULT_MODULE_NAME = 'Authentication';

    protected $_controllerMapping = array(
        'idp'   =>'IdentityProvider',
        'sp'    =>'ServiceProvider',
    );

    public function getControllerName()
    {
        if (isset($this->_controllerMapping[$this->_controllerName])) {
            return $this->_controllerMapping[$this->_controllerName];
        }

        return $this->_controllerName;
    }
}
