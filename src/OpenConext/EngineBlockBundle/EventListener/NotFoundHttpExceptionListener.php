<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use EngineBlock_ApplicationSingleton;
use OpenConext\EngineBlockBundle\Http\Exception\ApiHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig_Environment;

/**
 * When there was nothing to dispatch to, the dispatcher invoked a 404 page. This mimics that behaviour. When
 * refactoring phasing out corto, this listener should be converted to use Symfony style custom error pages.
 * @see https://www.pivotaltracker.com/story/show/107565968
 */
class NotFoundHttpExceptionListener
{
    /**
     * @var Twig_Environment
     */
    private $twig;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param Twig_Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        Twig_Environment $twig,
        LoggerInterface $logger
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        if ($exception instanceof ApiHttpException) {
            return;
        }

        // inverted quotes for BC, existing log parsers may rely on this
        $this->logger->notice(sprintf(
            "[404]Unroutable URI: '%s'",
            $this->engineBlockApplicationSingleton->getHttpRequest()->getUri()
        ));

        $response = new Response(
            $this->twig->render('@theme/Default/View/Error/not-found.html.twig'),
            404
        );

        $event->setResponse($response);
        // once we've handled it, we don't want anything else to interfere.
        $event->stopPropagation();
    }
}
