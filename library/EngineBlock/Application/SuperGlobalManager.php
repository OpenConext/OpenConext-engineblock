<?php

class EngineBlock_Application_SuperGlobalManager
{
    /**
     * File where.
     */
    const FILE = '/tmp/eb-fixtures/superglobals.json';

    /**
     * @var Psr\Log\LoggerInterface
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
                $this->_logger->notice(
                    sprintf('Overwriting $%s[%s]', $superGlobalName, $name),
                    ['super_global' => ['from' => $global[$name], 'to' => $value]]
                );

                $global[$name] = $value;
            }
        }
        return true;
    }
}
