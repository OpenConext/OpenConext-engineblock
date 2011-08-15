<?php

class EngineBlock_Log_Formatter_Mail implements Zend_Log_Formatter_Interface
{
    const REPLACE_WITH = '**SECRET**';

    protected $_simpleFormatter;
    protected $_filterValues = array();
    protected $_applicationConfiguration;

    public function __construct($filterValues)
    {
        $this->_simpleFormatter = new Zend_Log_Formatter_Simple();
        $this->_filterValues = $filterValues;
        $this->_applicationConfiguration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
    }

    public function format($event)
    {
        $output = $this->_simpleFormatter->format($event);
        foreach ($this->_filterValues as $configName) {
            $configValue = $this->_getConfigValue($this->_applicationConfiguration, $configName);
            if (is_null($configValue)) {
                continue;
            }

            if (is_array($configValue)) {
                foreach ($configValue as $configValueElement) {
                    $output = str_replace($configValueElement, self::REPLACE_WITH, $output);
                }
            }
            else {
                $output = str_replace($configValue, self::REPLACE_WITH, $output);
            }
        }
        return $output;
    }

    protected function _getConfigValue($config, $configName)
    {
        $firstDot = strpos($configName, '.');
        if ($firstDot) {
            $firstPart = substr($configName, 0, $firstDot);
            if (isset($config->$firstPart)) {
                return $this->_getConfigValue($config->$firstPart, substr($configName, $firstDot + 1));
            }
        }
        else {
            if (isset($config->$configName)) {
                if (is_object($config->$configName)) {
                    return $config->$configName->toArray();
                }
                else {
                    return $config->$configName;
                }
            }
        }
        return null;
    }
}