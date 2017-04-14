<?php

namespace OpenConext\EngineBlockBundle\Http\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApiNotFoundHttpException extends NotFoundHttpException implements ApiHttpException
{
}
