<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlockBridge\ErrorReporter;
use OpenConext\EngineBlockBundle\Exception\AddExecutionTimePadding;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ErrorResponseTimePaddingListener
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    /**
     * @var integer
     */
    private $paddedResponseTime;

    public function __construct(
        Stopwatch $stopwatch,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger,
        ErrorReporter $errorReporter,
        $paddedResponseTime
    ) {
        Assertion::integer($paddedResponseTime);

        $this->stopwatch          = $stopwatch;
        $this->urlGenerator       = $urlGenerator;
        $this->logger             = $logger;
        $this->errorReporter      = $errorReporter;
        $this->paddedResponseTime = $paddedResponseTime;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof PadResponseTimeMarkerInterface) {
            return;
        }

        $responseTimeInSeconds        = $this->stopwatch->stop('response')->getDuration() / 1000;
        $responseTimePaddingInSeconds = $this->paddedResponseTime - $responseTimeInSeconds;

        if ($responseTimePaddingInSeconds > 0) {
            $this->logger->warning(sprintf(
                'Caught Exception "%s":"%s", padding response time with %f seconds',
                get_class($exception),
                $exception->getMessage(),
                $responseTimePaddingInSeconds
            ));

            usleep($responseTimePaddingInSeconds * 1000000);
        } else {
            $this->logger->warning(sprintf(
                'Caught Exception "%s":"%s", not padding response time: '
                . 'it exceeds the configured padded response time (%d seconds) by %f seconds',
                get_class($exception),
                $exception->getMessage(),
                $this->paddedResponseTime,
                $responseTimePaddingInSeconds
            ));
        }

        $message         = 'Unable to verify message';
        $redirectToRoute = 'authentication_feedback_verification_failed';

        $this->logger->debug(sprintf('Redirecting to route "%s"', $redirectToRoute));
        $this->logger->notice($message);
        $this->errorReporter->reportError($exception, '-> Redirecting to feedback page');

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate($redirectToRoute, [], UrlGeneratorInterface::ABSOLUTE_PATH)
        ));
    }
}
