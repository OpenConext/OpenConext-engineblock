<?php

namespace OpenConext\EngineBlockBundle;

use EngineBlock_ApplicationSingleton;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenConextEngineBlockBundle extends Bundle
{
    public function boot()
    {
        $engineBlockApplicationSingleton = EngineBlock_ApplicationSingleton::getInstance();
        $engineBlockApplicationSingleton->bootstrap(
            $this->container->get('logger'),
            $this->container->get('engineblock.bridge.log.manual_or_error_activation_strategy'),
            uniqid()
        );

        // set the configured layout on the application singleton
        $engineBlockApplicationSingleton->setLayout($this->container->get('engineblock.compat.layout'));
    }
}
