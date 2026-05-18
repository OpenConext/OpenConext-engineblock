<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace OpenConext\EngineBlockBridge\Logger;

use EngineBlock_Saml2_AuthnRequestAnnotationDecorator;
use EngineBlock_Saml2_ResponseAnnotationDecorator;
use EngineBlock_SamlHelper;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;

class LoginLogger
{
    private array $configuredLogAttributes;

    public function __construct(
        private readonly AuthenticationLoggerAdapter $authenticationLogger,
        array $configuredLogAttributes
    ) {
        $this->configuredLogAttributes = $configuredLogAttributes;
    }

    /**
     * Log a successful login.
     *
     * @param string $collabPersonId Resolved collabPersonId (from response or session)
     * @param array $responseAttributes Final response attributes after all filter commands
     */
    public function logLogin(
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider,
        MetadataRepositoryInterface $repository,
        string $collabPersonId,
        array $responseAttributes,
    ): void {
        // Get the Requester chain, which starts at the oldest (farthest away from us SP) and ends with our next hop.
        $requesterChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $serviceProvider,
            $request,
            $repository
        );

        // Remove the SP that is our next hop
        array_pop($requesterChain);

        $logAttributes = [];
        if (!empty($this->configuredLogAttributes)) {
            foreach ($this->configuredLogAttributes as $attributeLabel => $responseAttributeKey) {
                if (array_key_exists((string) $responseAttributeKey, $responseAttributes)) {
                    $attributeValues = implode(',', $responseAttributes[$responseAttributeKey]);
                    $logAttributes[$attributeLabel] = $attributeValues;
                }
            }
        }

        $this->authenticationLogger->logLogin(
            $serviceProvider,
            $identityProvider,
            $collabPersonId,
            $request->getKeyId(),
            $requesterChain,
            $response->getNameIdValue(),
            $response->getAssertion()->getAuthnContextClassRef(),
            $request->getDestination(),
            $request->getIDPList(),
            $logAttributes
        );
    }
}
