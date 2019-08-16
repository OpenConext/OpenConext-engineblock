<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
