<?php

class EngineBlock_Application_SuperGlobalManager
{
    const DIR = '/tmp/eb-fixtures/';
    const SERVER_FILENAME = 'superglobal.server.overrides.json';

    /**
     * @var EngineBlock_Log
     */
    private $_logger;

    public function __construct()
    {
        $this->_logger = EngineBlock_ApplicationSingleton::getLog();
    }

    public function injectOverrides()
    {
        $filePath = self::DIR . self::SERVER_FILENAME;
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }

        $overridesData = file_get_contents($filePath);
        if (!$overridesData) {
            return false;
        }

        $overrides = json_decode($overridesData, true);
        if (!$overrides || empty($overrides)) {
            return false;
        }

        foreach ($overrides as $name => $value) {
            $this->_logger->log('Overwriting $_SERVER[' . $name . ']', EngineBlock_Log::NOTICE);
            $this->_logger->attach($_SERVER[$name], 'FROM');
            $this->_logger->attach($value, 'TO');

            $_SERVER[$name] = $value;
        }
        return true;
    }
}
