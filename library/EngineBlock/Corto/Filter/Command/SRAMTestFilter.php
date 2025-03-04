<?php

use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Sbs\Dto\Request;

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

class EngineBlock_Corto_Filter_Command_SRAMTestFilter extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    /**
     * {@inheritdoc}
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
        if (!$this->getFeatureConfiguration()->isEnabled('eb.feature_enable_sram_interrupt')) {
            return;
        }

        if ($this->_serviceProvider->getCoins()->collabEnabled() === false) {
            return;
        }

        try {
            $request = $this->buildRequest();
            $interruptResponse = $this->getSbsClient()->requestInterruptDecisionFor($request);

            if ($interruptResponse->msg === 'interrupt') {
                // @TODO Consider if this should be an attribute?
                $this->_response->setSRAMInterruptNonce($interruptResponse->nonce);
            } elseif (!empty($interruptResponse->attributes)) {
                // @TODO make sure this has test coverage
                $this->_responseAttributes = array_merge_recursive(
                    $this->_responseAttributes,
                    $interruptResponse->attributes
                );
            }
        }catch (Throwable $e){
            throw new EngineBlock_Exception_SbsCheckFailed('The SBS server could not be queried: ' . $e->getMessage());
        }
    }

    private function getSbsClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSbsClient();
    }

    private function getFeatureConfiguration(): FeatureConfigurationInterface
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getFeatureConfiguration();
    }

    /**
     * @return Request
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    private function buildRequest(): Request
    {
        $attributes = $this->getResponseAttributes();
        $id = $this->_request->getId();

        // @TODO Check: can it occur this is not set?
        $user_id = $attributes['urn:mace:dir:attribute-def:uid'][0];
        $continue_url = $this->_server->getUrl('SRAMInterruptService', '') . "?ID=$id";
        $service_id = $this->_serviceProvider->entityId;
        $issuer_id = $this->_identityProvider->entityId;

        return Request::create(
            $user_id,
            $continue_url,
            $service_id,
            $issuer_id
        );
    }
}
