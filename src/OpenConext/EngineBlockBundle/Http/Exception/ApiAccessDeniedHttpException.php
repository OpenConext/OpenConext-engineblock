<?php

namespace OpenConext\EngineBlockBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiAccessDeniedHttpException extends AccessDeniedHttpException implements ApiHttpException
{
}
