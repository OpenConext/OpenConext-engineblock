<?php

namespace OpenConext\EngineBlock\ApiBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadApiRequestHttpException extends BadRequestHttpException implements ApiHttpException
{
}
