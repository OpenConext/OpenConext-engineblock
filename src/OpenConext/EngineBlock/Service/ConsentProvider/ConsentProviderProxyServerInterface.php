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

namespace OpenConext\EngineBlock\Service\ConsentProvider;

use EngineBlock_Corto_Module_Bindings;
use EngineBlock_Corto_Module_Services_SessionLostException;
use EngineBlock_Corto_ProxyServer_Exception;
use EngineBlock_Exception;
use EngineBlock_Saml2_AuthnRequestAnnotationDecorator;
use EngineBlock_Saml2_ResponseAnnotationDecorator;

interface ConsentProviderProxyServerInterface
{
    /**
     * @return EngineBlock_Corto_Module_Bindings
     */
    public function getBindingsModule();

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function getReceivedRequestFromResponse(EngineBlock_Saml2_ResponseAnnotationDecorator $response);

    /**
     * @return \OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface
     */
    public function getRepository();

    /**
     * @param string $serviceName
     * @param string $remoteEntityId
     * @return string
     */
    public function getUrl($serviceName = "", $remoteEntityId = "");

    /**
     * @param $rawOutput
     * @return void
     */
    public function sendOutput($rawOutput);
}
