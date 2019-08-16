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

namespace OpenConext\EngineBlockBundle\Authentication;

use OpenConext\Value\Saml\Entity;

interface AuthenticationStateInterface
{
    /**
     * @param string $requestId
     * @param Entity $serviceProvider
     * @return void
     */
    public function startAuthenticationOnBehalfOf($requestId, Entity $serviceProvider);

    /**
     * @param string $requestId
     * @param Entity $identityProvider
     * @return void
     */
    public function authenticatedAt($requestId, Entity $identityProvider);

    /**
     * @param $requestId
     * @return void
     */
    public function completeCurrentProcedure($requestId);
}
