<?php

namespace OpenConext\EngineBlockBundle\Exception;

use InvalidArgumentException as CoreInvalidArgumentException;

class InvalidArgumentException extends CoreInvalidArgumentException
{
    public static function invalidType($expectedType, $propertyPath, $parameter)
    {
        return new self(
            sprintf(
                'Invalid argument "%s": "%s" expected, "%s" given',
                $propertyPath,
                $expectedType,
                is_object($parameter) ? get_class($parameter) : gettype($parameter)
            )
        );
    }
}
