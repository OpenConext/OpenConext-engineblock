<?php

abstract class EngineBlock_Router_Abstract implements EngineBlock_Router_Interface
{
    protected $_controllerName;
    protected $_moduleName;
    protected $_actionName;
    protected $_actionArguments = array();

    /**
     * @static
     * @return EngineBlock_Router_Abstract
     */
    public static function create()
    {
        return new static();
    }

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
        $decodedArguments = array();
        foreach ($arguments as $argument) {
            $decodedArguments[] = rawurldecode($argument);
        }

        $this->_actionArguments = $decodedArguments;
        return $this;
    }

    /**
     * Convert a-hyphenated-string to AHyphenatedString
     *
     * @param string $name
     * @return string
     */
    protected function _convertHyphenatedToCamelCase($name)
    {
        return implode('', array_map('ucfirst', explode('-', $name)));
    }
}
