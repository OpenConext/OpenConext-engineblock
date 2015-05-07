<?php

use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use Psr\Log\LoggerInterface;

interface EngineBlock_Log_LoggerFactory
{
    /**
     * @param array $config
     * @param bool  $debug
     * @return LoggerInterface
     * @throws InvalidConfigurationException
     */
    public static function factory(array $config, $debug);
}
