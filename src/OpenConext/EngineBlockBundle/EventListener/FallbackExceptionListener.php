<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Exception;
use EngineBlock_View;
use OpenConext\EngineBlockBridge\ErrorReporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * The Dispatcher in the old code wrapped everything in a try/catch to allow for graceful recovery.
 * This listener mimics that behaviour. When phasing out corto, this listener should be replaced by
 * Symfony style custom error pages
 * @see https://www.pivotaltracker.com/story/show/107565968
 */
class FallbackExceptionListener
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;
    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param EngineBlock_View                 $engineBlockView
     * @param LoggerInterface                  $logger
     * @param ErrorReporter                    $errorReporter
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView,
        LoggerInterface $logger,
        ErrorReporter $errorReporter
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView                 = $engineBlockView;
        $this->logger                          = $logger;
        $this->errorReporter                   = $errorReporter;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof EngineBlock_Exception) {
            $this->errorReporter->reportError($exception, 'Caught Unhandled EngineBlock_Exception');
        } else {
            $this->errorReporter->reportError(
                new EngineBlock_Exception($exception->getMessage(), EngineBlock_Exception::CODE_ERROR, $exception),
                'Caught Unhandled generic exception'
            );
        }

        $viewData = [];
        if ($this->engineBlockApplicationSingleton->getDiContainer()->isDebug()) {
            $viewData['exception'] = $exception;
        }

        $response = new Response(
            $this->engineBlockView->setData($viewData)->render('Default/View/Error/Display.phtml'),
            500
        );

        $event->setResponse($response);

        // as fallback, we need to handle everything.
        $event->stopPropagation();
    }
}
