<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle;

use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat\Container;
use SAML2\Compat\ContainerSingleton;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenConextEngineBlockFunctionalTestingBundle extends Bundle
{
    public function boot()
    {
        // A container is set with additional functionality for functional testing purposes,
        // such as mocking IdPs and SPs
        ContainerSingleton::setContainer(new Container());
    }
}
