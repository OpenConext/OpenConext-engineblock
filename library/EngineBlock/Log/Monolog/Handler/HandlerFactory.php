<?php

use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use Monolog\Handler\HandlerInterface;

interface EngineBlock_Log_Monolog_Handler_HandlerFactory
{
    /**
     * Construct a handler. Receives an array of previously constructed handlers indexed by their name. This allows for
     * composition of handlers.
     *
     * @param array $config
     * @param HandlerInterface[] $handlers
     * @param bool $debug
     * @return HandlerInterface
     * @throws InvalidConfigurationException
     */
    public static function factory(array $config, array $handlers, $debug);
}
