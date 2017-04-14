<?php

namespace OpenConext\EngineBlock\Exception;

use Assert\InvalidArgumentException as InvalidAssertionException;

class InvalidArgumentException extends InvalidAssertionException implements Exception
{
    // according to CS used, propertypath and value should be switched, but that breaks the integration with the library
    // @codingStandardsIgnoreStart
    public function __construct($message, $code, $propertyPath = null, $value, array $constraints = [])
    {
    // @codingStandardsIgnoreEnd
        if ($propertyPath !== null && strpos($message, $propertyPath) === false) {
            $message = sprintf('Invalid argument given for "%s": %s', $propertyPath, $message);
        }

        parent::__construct($message, $code, $propertyPath, $value, $constraints);
    }

    /**
     * @param string $expected description of expected type
     * @param string $parameterName
     * @param mixed $parameter the parameter that is not of the expected type.
     *
     * @return self
     */
    public static function invalidType($expected, $parameterName, $parameter)
    {
        $message = sprintf(
            'Invalid argument type: "%s" expected, "%s" given for "%s"',
            $expected,
            is_object($parameter) ? get_class($parameter) : gettype($parameter),
            $parameterName
        );

        return new self($message);
    }
}
