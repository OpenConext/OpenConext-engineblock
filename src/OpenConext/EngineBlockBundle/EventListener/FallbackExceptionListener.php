<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Exception;
use OpenConext\EngineBlockBridge\ErrorReporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Twig_Environment;

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
     * @var Twig_Environment
     */
    private $twig;
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
     * @param Twig_Environment $twig
     * @param LoggerInterface $logger
     * @param ErrorReporter $errorReporter
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        Twig_Environment $twig,
        LoggerInterface $logger,
        ErrorReporter $errorReporter
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->errorReporter = $errorReporter;
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

        $context = [
            'wide' => true,
        ];
        if ($this->engineBlockApplicationSingleton->getDiContainer()->isDebug()) {
            $context['exception'] = $exception;
        }

        $response = new Response(
            $this->twig->render(
                '@theme/Default/View/Error/display.html.twig',
                $context
            ),
            500
        );

        $event->setResponse($response);

        // as fallback, we need to handle everything.
        $event->stopPropagation();
    }
}
