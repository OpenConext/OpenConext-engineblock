<?php
 
abstract class EngineBlock_Router_Abstract
{
    protected $_controllerName;
    protected $_moduleName;
    protected $_actionName;
    protected $_actionArguments = array();

    abstract public function route($uri);

    public function getControllerName()
    {
        return $this->_controllerName;
    }

    public function getModuleName()
    {
        return $this->_moduleName;
    }

    public function getActionName()
    {
        return $this->_actionName;
    }

    public function getActionArguments()
    {
        return $this->_actionArguments;
    }

    protected function setActionArguments($arguments)
    {
        foreach ($arguments as &$argument) {
            $argument = urldecode($argument);
        }

        $this->_actionArguments = $arguments;
        return $this;
    }
}
