<?php

namespace OpenConext\EngineBlockBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadApiRequestHttpException extends BadRequestHttpException implements ApiHttpException
{
}
