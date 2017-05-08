<?php

namespace OpenConext\EngineBlockBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

final class ApiMethodNotAllowedHttpException extends MethodNotAllowedHttpException implements ApiHttpException
{
    public static function methodNotAllowed($actualMethod, array $allowedMethods)
    {
        return new self(
            $allowedMethods,
            sprintf(
                'Method "%s" is not allowed, allowed methods are: %s',
                $actualMethod,
                join(', ', $allowedMethods)
            )
        );
    }
}
