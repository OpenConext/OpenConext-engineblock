<?php

final class EngineBlock_Log_InvalidConfigurationException extends EngineBlock_Exception
{
    /**
     * @param string $key
     * @param string $expectedType
     * @return EngineBlock_Log_InvalidConfigurationException
     */
    public static function missing($key, $expectedType)
    {
        return new self(
            sprintf(
                'Missing configuration value, expected configuration key "%s" containing a %s',
                $key,
                $expectedType
            )
        );
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $expectedType
     * @return EngineBlock_Log_InvalidConfigurationException
     */
    public static function invalidType($key, $value, $expectedType)
    {
        return new self(
            sprintf(
                'Invalid configuration value, configuration key "%s" should be a %s, %s given',
                $key,
                $expectedType,
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }
}
