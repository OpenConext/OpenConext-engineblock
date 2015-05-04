<?php

class EngineBlock_Doctrine_ConfigMapper
{
    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param Psr\Log\LoggerInterface $log
     */
    public function __construct(Psr\Log\LoggerInterface $log)
    {
        $this->logger = $log;
    }

    public function map(Zend_Config $applicationConfiguration)
    {
        $databaseConfig = $applicationConfiguration->get('database');
        if (!$databaseConfig || !$databaseConfig instanceof Zend_Config) {
            throw new RuntimeException('No database configuration');
        }

        $masterParams = $this->mapMasterConfig($databaseConfig);

        return array(
            'wrapperClass' => 'Doctrine\DBAL\Connections\MasterSlaveConnection',
            'driver' => $masterParams['driver'],
            'master' => $masterParams,
            'slaves' => $this->mapSlavesConfig($databaseConfig, $masterParams),
        );
    }

    private function getParamsFromConfig(Zend_Config $config)
    {
        $dsn = $config->get('dsn');

        $firstColonPos = strpos($dsn, ':');
        $driver = substr($dsn, 0, $firstColonPos);

        $params = array(
            'driver' => 'pdo_' . $driver,
            'user' => $config->get('user'),
            'password' => $config->get('password'),
        );

        $dsn = substr($dsn, $firstColonPos+1);
        $dsnRawParams = explode(';', $dsn);

        foreach ($dsnRawParams as $dsnRawParam) {
            $firstEqualsPos = strpos($dsnRawParam, '=');
            $params[substr($dsnRawParam, 0, $firstEqualsPos)] = substr($dsnRawParam, $firstEqualsPos + 1);
        }

        return $params;
    }

    /**
     * @param $databaseConfig
     * @return array
     */
    private function mapMasterConfig(Zend_Config $databaseConfig)
    {
        $masters = $databaseConfig->get('masters');
        if (!$masters || !$masters instanceof Zend_Config || $masters->count() === 0) {
            throw new RuntimeException('No master databases configured');
        }

        if (count($masters) > 1) {
            $this->logger->log('More than 1 master detected, using first', EngineBlock_Log::WARN);
        }

        $masterId = $masters->current();
        $masterConfig = $databaseConfig->get($masterId);
        if (!$masterConfig || !$masterConfig instanceof Zend_Config) {
            throw new RuntimeException("Master '{$masterId}' mentioned but not configured");
        }

        return $this->getParamsFromConfig($masterConfig);
    }

    /**
     * @param $databaseConfig
     * @param $masterParams
     * @return array
     */
    private function mapSlavesConfig(Zend_Config $databaseConfig, $masterParams)
    {
        $slaves = $databaseConfig->get('slaves');
        if (!$slaves || !$slaves instanceof Zend_Config || $slaves->count() === 0) {
            $slaves = array();
        }

        $slavesParams = array();
        foreach ($slaves as $slaveId) {
            $slaveConfig = $databaseConfig->get($slaveId);
            if (!$slaveConfig || !$slaveConfig instanceof Zend_Config) {
                $this->logger->log('Slave specified that has no configuration. Skipping.', EngineBlock_Log::WARN);
                continue;
            }

            $slaveParams = $this->getParamsFromConfig($slaveConfig);
            if ($slavesParams['driver'] !== $masterParams['driver']) {
                $this->logger->log(
                    'Slave specified with different driver from master. Unsupported. Skipping.',
                    EngineBlock_Log::WARN
                );
                continue;
            }

            $slavesParams[] = $slaveParams;
        }

        if (empty($slavesParams)) {
            $this->logger->log('No usable slaves configured, using master as slave', EngineBlock_Log::WARN);
            $slavesParams[] = $masterParams;
        }

        return $slavesParams;
    }
}
