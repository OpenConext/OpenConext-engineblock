<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Stopwatch\Stopwatch;

final class StartResponseListener
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->stopwatch->start('response');
    }
}
