<?php

class EngineBlock_Application_SuperGlobalManager
{
    /**
     * File where.
     */
    const FILE = '/tmp/eb-fixtures/superglobals.json';

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
        $fixture = new \OpenConext\Component\EngineBlockFixtures\SuperGlobalsFixture(
            new \OpenConext\Component\EngineBlockFixtures\DataStore\JsonDataStore(
                self::FILE
            )
        );
        $overrides = $fixture->getAll();

        foreach ($overrides as $superGlobalName => $values) {
            $superGlobalName = '_' . $superGlobalName;

            global $$superGlobalName;
            $global = &$$superGlobalName;

            foreach ($values as $name => $value) {
                $this->_logger->log('Overwriting $_' . $superGlobalName . '[' . $name . ']', EngineBlock_Log::NOTICE);
                $this->_logger->attach($_SERVER[$name], 'FROM');
                $this->_logger->attach($value, 'TO');

                $global[$name] = $value;
            }
        }
        return true;
    }
}
