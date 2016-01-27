<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle;

use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenConextEngineBlockFunctionalTestingBundle extends Bundle
{
}

// HACK Doesn't belong here, should be moved somewhere better
\SAML2_Compat_ContainerSingleton::setContainer(new Container());
