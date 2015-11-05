<?php

namespace OpenConext\EngineBlock\ApiBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiInternalServerErrorHttpException extends HttpException implements ApiHttpException
{
    public function __construct($message, \Exception $previous = null, $code = 0)
    {
        parent::__construct(500, $message, $previous, array(), $code);
    }
}
