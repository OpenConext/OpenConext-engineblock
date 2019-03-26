<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Service;

use EngineBlock_Corto_Model_Consent;
use EngineBlock_Corto_ProxyServer;
use EngineBlock_Saml2_ResponseAnnotationDecorator;

interface ConsentFactoryInterface
{
    /**
     * Creates a new Consent instance
     *
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @param array $attributes
     * @return EngineBlock_Corto_Model_Consent
     */
    public function create(
        EngineBlock_Corto_ProxyServer $proxyServer,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        array $attributes
    );
}