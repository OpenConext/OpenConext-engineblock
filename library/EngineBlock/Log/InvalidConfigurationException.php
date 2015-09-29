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

    /**
     * @param string $className
     * @param string $interfaceName
     * @throws EngineBlock_Log_InvalidConfigurationException
     */
    public static function assertIsValidFactory($className, $interfaceName)
    {
        if (!class_exists($className)) {
            throw new self(
                sprintf('Factory class "%s" does not exist or is not autoloadable', $className)
            );
        }

        if (!in_array($interfaceName, class_implements($className), true)) {
            throw new self(
                sprintf('Factory "%s" is invalid: it must implement "%s"', $className, $interfaceName)
            );
        }
    }

    /**
     * @param string $message
     * @param string $severity Defaults to EngineBlock_Exception::ALERT.
     * @param Exception|null $previous
     */
    public function __construct($message, $severity = EngineBlock_Exception::CODE_ALERT, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}
