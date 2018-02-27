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
            $this->container->get('engineblock.logger.manual_or_error_activation_strategy'),
            $this->container->get('engineblock.request.request_id'),
            $this->container
        );
    }
}
