<?php

namespace OpenConext\EngineBlock\ApiBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiAccessDeniedHttpException extends AccessDeniedHttpException implements ApiException
{
}
