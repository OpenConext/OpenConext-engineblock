<?php

namespace OpenConext\EngineBlockBundle;

use EngineBlock_ApplicationSingleton;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenConextEngineBlockBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        $eb = EngineBlock_ApplicationSingleton::getInstance();
        $eb->bootstrap($this->container->get('logger'), uniqid());

        // set the configured layout on the application singleton
        $eb->setLayout($this->container->get('engineblock.compat.layout'));
    }
}
