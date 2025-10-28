<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;
use OpenConext\EngineBlockBundle\Sbs\Msg;
use OpenConext\EngineBlockBundle\Sbs\SbsAttributeMerger;
use OpenConext\EngineBlockBundle\Sbs\SbsClientInterface;
use Psr\Log\LoggerInterface;

class EngineBlock_Corto_Filter_Command_SRAMInterruptFilter extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{

    public function __construct(
        private readonly SbsClientInterface $sbsClient,
        private readonly FeatureConfigurationInterface $featureConfiguration,
        private readonly SbsAttributeMerger $attributeMerger,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @return array|null
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function execute(): void
    {
        if (!$this->featureConfiguration->isEnabled('eb.feature_enable_sram_interrupt')) {
            return;
        }

        $serviceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository(),
            $this->logger,
        );

        if ($serviceProvider === null) {
            $serviceProvider = $this->_serviceProvider;
        }

        if ($serviceProvider->getCoins()->collabEnabled() === false) {
            $this->logger->notice("No SBS interrupt for serviceProvider: " . $serviceProvider->entityId);

            return;
        }

        $this->logger->notice("SBS interrupt for serviceProvider: " . $serviceProvider->entityId);

        try {
            $request = $this->buildRequest($serviceProvider);

            $interruptResponse = $this->sbsClient->authz($request);

            if ($interruptResponse->msg === Msg::Interrupt) {
                $this->logger->info("SBS interrupt reason: " . $interruptResponse->message);
                $this->_response->setSRAMInterruptNonce($interruptResponse->nonce);

                return;
            }

            if ($interruptResponse->msg === Msg::Authorized && !empty($interruptResponse->attributes)) {
                // @TODO JOHAN hier ook types? Nee?
                $this->_responseAttributes = $this->attributeMerger->mergeAttributes($this->_responseAttributes, $interruptResponse->attributes);

                return;
            }

            $this->logger->error(sprintf('SBS Authz returned error: %s', $interruptResponse->message));

            throw new InvalidSbsResponseException('SBS Authz returned error.');
        } catch (Throwable $e){
            throw new EngineBlock_Exception_SbsCheckFailed('The SBS server could not be queried: ' . $e->getMessage());
        }
    }

    private function buildRequest(ServiceProvider $serviceProvider): AuthzRequest
    {
        $attributes = $this->getResponseAttributes();
        $id = $this->_request->getId();

        $user_id = $this->_collabPersonId ?? "";
        $eppn = $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'][0] ?? "";
        $continue_url = $this->_server->getUrl('SRAMInterruptService', '') . "?ID=$id";
        $service_id = $serviceProvider->entityId;
        $issuer_id = $this->_identityProvider->entityId;


        return new AuthzRequest(
            $user_id,
            $eppn,
            $continue_url,
            $service_id,
            $issuer_id
        );
    }
}
