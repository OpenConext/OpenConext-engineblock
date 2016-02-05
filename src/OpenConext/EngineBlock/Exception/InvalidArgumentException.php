<?php

namespace OpenConext\EngineBlock\Exception;

use InvalidArgumentException as CoreInvalidArgumentException;

class InvalidArgumentException extends CoreInvalidArgumentException implements Exception
{
    /**
     * @param string $expected  description of expected type
     * @param string $parameterName
     * @param mixed  $parameter the parameter that is not of the expected type.
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
