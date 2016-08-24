<?php

namespace OpenConext\EngineBlock\Http\Exception;

use Exception;

class RequestException extends HttpException
{
    public function __construct($message, Exception $previous = null)
    {
        return parent::__construct($message, 0, $previous);
    }
}
