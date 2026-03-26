<?php

/**
 * Copyright 2010 SURFnet B.V.
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

use OpenConext\EngineBlock\Service\Consent\ConsentHashServiceInterface;

/**
 * @todo write a test
 */
class EngineBlock_Corto_Model_Consent_Factory
{

    private ConsentHashServiceInterface $hashService;

    public function __construct(
        ConsentHashServiceInterface $hashService
    ) {
        $this->hashService = $hashService;
    }

    /**
     * @param array $attributes
     */
    public function create(
        EngineBlock_Corto_ProxyServer $proxyServer,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        array $attributes
    ): EngineBlock_Corto_Model_Consent {
        // If attribute manipulation was executed before consent, the NameId must be retrieved from the original response
        // object, in order to ensure correct 'hashed_user_id' generation.
        $featureConfiguration = EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getFeatureConfiguration();

        $amPriorToConsent = $featureConfiguration->isEnabled('eb.run_all_manipulations_prior_to_consent');
        $consentEnabled = $featureConfiguration->isEnabled('eb.feature_enable_consent');

        return new EngineBlock_Corto_Model_Consent(
            $proxyServer->getConfig('ConsentStoreValues', true),
            $response,
            $attributes,
            $amPriorToConsent,
            $consentEnabled,
            $this->hashService
        );
    }
}
