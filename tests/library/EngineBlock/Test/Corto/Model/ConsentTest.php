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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\ConsentHashServiceInterface;
use PHPUnit\Framework\TestCase;

class EngineBlock_Corto_Model_Consent_Test extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $consentDisabled;
    private $consent;
    private $consentService;

    public function setup()
    {
        $mockedResponse = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');

        $this->consentService = Mockery::mock(ConsentHashServiceInterface::class);

        $this->consentDisabled = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            false,
            false,
            $this->consentService
        );

        $this->consent = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            false,
            true,
            $this->consentService
        );
    }

    public function testConsentDisabledDoesNotWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");

        $this->consentService->shouldReceive('getUnstableAttributesHash');
        $this->consentService->shouldNotReceive('storeConsentHash');
        $this->consentDisabled->explicitConsentWasGivenFor($serviceProvider);
        $this->consentDisabled->implicitConsentWasGivenFor($serviceProvider);
        $this->consentDisabled->giveExplicitConsentFor($serviceProvider);
        $this->consentDisabled->giveImplicitConsentFor($serviceProvider);
    }

    public function testConsentWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");

        $this->consentService->shouldReceive('getUnstableAttributesHash')->andReturn(sha1('unstable'));
        $this->consentService->shouldReceive('retrieveConsentHash')->andReturn(sha1('unstable'));
        $this->consent->explicitConsentWasGivenFor($serviceProvider);

        $this->consentService->shouldReceive('getStableAttributesHash')->andReturn(sha1('stable'));
        $this->consent->implicitConsentWasGivenFor($serviceProvider);

        $this->consentService->shouldReceive('storeConsentHash')->andReturn(true);
        $this->consent->giveExplicitConsentFor($serviceProvider);
        $this->consent->giveImplicitConsentFor($serviceProvider);
    }
}
