<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle;

use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenConextEngineBlockFunctionalTestingBundle extends Bundle
{
    public function boot()
    {
        \SAML2_Compat_ContainerSingleton::setContainer(new Container());
    }
}
