<?php

namespace OpenConext\EngineBlock\Assert;

use Assert\Assertion as BaseAssertion;

/**
 * @method static void nullOrNonEmptyString($value, $message = null, $propertyPath = null)
 * @method static void allNonEmptyString($value, $message = null, $propertyPath = null)
 */
class Assertion extends BaseAssertion
{
    const INVALID_NON_EMPTY_STRING = 501;

    protected static $exceptionClass = 'OpenConext\EngineBlock\Exception\InvalidArgumentException';

    /**
     * @param string $value
     * @param string $propertyPath
     * @return void
     */
    public static function nonEmptyString($value, $propertyPath)
    {
        if (!is_string($value) || trim($value) === '') {
            $message = 'Expected non-empty string for "%s", "%s" given';

            throw static::createException(
                $value,
                sprintf($message, $propertyPath, static::stringify($value)),
                static::INVALID_NON_EMPTY_STRING,
                $propertyPath
            );
        }
    }
}
