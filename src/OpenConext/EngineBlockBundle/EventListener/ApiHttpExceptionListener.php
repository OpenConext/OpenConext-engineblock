<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Http\Exception\ApiHttpException;
use OpenConext\EngineBlockBridge\ErrorReporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ApiHttpExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    /**
     * @param LoggerInterface $logger
     * @param ErrorReporter   $errorReporter
     */
    public function __construct(LoggerInterface $logger, ErrorReporter $errorReporter)
    {
        $this->logger = $logger;
        $this->errorReporter = $errorReporter;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof ApiHttpException) {
            return;
        }

        $this->logger->warning($exception->getMessage());
        $this->errorReporter->reportError($exception, '-> responding directly');

        $event->setResponse(new JsonResponse($exception->getMessage(), $exception->getStatusCode()));
    }
}
