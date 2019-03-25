<?php

namespace OpenConext\EngineBlock\Http\Exception;

use Exception;

class AccessDeniedException extends HttpException
{
    public function __construct($resource, $code = 0, Exception $previous = null)
    {
        $message = sprintf('Access denied to resource "%s": are you properly authorized?', $resource);

        parent::__construct($message, $code, $previous);
    }
}
