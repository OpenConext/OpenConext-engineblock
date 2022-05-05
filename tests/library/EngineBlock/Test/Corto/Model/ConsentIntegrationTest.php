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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Consent\ConsentHashService;
use OpenConext\EngineBlockBundle\Authentication\Repository\DbalConsentRepository;
use PHPUnit\Framework\TestCase;

class EngineBlock_Corto_Model_Consent_Integration_Test extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $consent;
    /**
     * @var ConsentHashService
     */
    private $consentService;
    /**
     * @var DbalConsentRepository
     */
    private $consentRepository;
    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator|MockInterface
     */
    private $response;

    public function setup()
    {
        $this->response = Mockery::mock(EngineBlock_Saml2_ResponseAnnotationDecorator::class);
        $this->consentRepository = Mockery::mock(ConsentRepository::class);
        $this->consentService = new ConsentHashService($this->consentRepository);

        $this->consent = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $this->response,
            [],
            false,
            true,
            $this->consentService
        );
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_no_previous_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // No consent is given previously
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->once()
            ->andReturn(ConsentVersion::notGiven());
        switch ($consentType) {
            case ConsentType::TYPE_EXPLICIT:
                $this->assertFalse($this->consent->explicitConsentWasGivenFor($serviceProvider));
                break;
            case ConsentType::TYPE_IMPLICIT:
                $this->assertFalse($this->consent->implicitConsentWasGivenFor($serviceProvider));
                break;
        }
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_unstable_previous_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Stable consent is not yet stored
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->with(['0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', '8739602554c7f3241958e3cc9b57fdecb474d508', '8739602554c7f3241958e3cc9b57fdecb474d508', $consentType])
            ->once()
            ->andReturn(ConsentVersion::unstable());

        switch ($consentType) {
            case ConsentType::TYPE_EXPLICIT:
                $this->assertTrue($this->consent->explicitConsentWasGivenFor($serviceProvider));
                break;
            case ConsentType::TYPE_IMPLICIT:
                $this->assertTrue($this->consent->implicitConsentWasGivenFor($serviceProvider));
                break;
        }
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_stable_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Stable consent is not yet stored
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->with(['0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', '8739602554c7f3241958e3cc9b57fdecb474d508', '8739602554c7f3241958e3cc9b57fdecb474d508', $consentType])
            ->once()
            ->andReturn(ConsentVersion::stable());

        switch ($consentType) {
            case ConsentType::TYPE_EXPLICIT:
                $this->assertTrue($this->consent->explicitConsentWasGivenFor($serviceProvider));
                break;
            case ConsentType::TYPE_IMPLICIT:
                $this->assertTrue($this->consent->implicitConsentWasGivenFor($serviceProvider));
                break;
        }
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_give_consent_no_unstable_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Now assert that the new stable consent hash is going to be set
        $this->consentRepository
            ->shouldReceive('storeConsentHash')
            ->once()
            ->with(['0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', '8739602554c7f3241958e3cc9b57fdecb474d508', $consentType])
            ->andReturn(true);

        switch ($consentType) {
            case ConsentType::TYPE_EXPLICIT:
                $this->assertTrue($this->consent->giveExplicitConsentFor($serviceProvider));
                break;
            case ConsentType::TYPE_IMPLICIT:
                $this->assertTrue($this->consent->giveImplicitConsentFor($serviceProvider));
                break;
        }
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_give_consent_unstable_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Now assert that the new stable consent hash is going to be set
        $this->consentRepository
            ->shouldReceive('storeConsentHash')
            ->once()
            ->with(['0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', '8739602554c7f3241958e3cc9b57fdecb474d508', $consentType])
            ->andReturn(true);

        switch ($consentType) {
            case ConsentType::TYPE_EXPLICIT:
                $this->assertTrue($this->consent->giveExplicitConsentFor($serviceProvider));
                break;
            case ConsentType::TYPE_IMPLICIT:
                $this->assertTrue($this->consent->giveImplicitConsentFor($serviceProvider));
                break;
        }
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_upgrade_to_stable_consent($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->twice()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Old-style (unstable) consent was given previously
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->with(['0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', '8739602554c7f3241958e3cc9b57fdecb474d508', '8739602554c7f3241958e3cc9b57fdecb474d508', $consentType])
            ->once()
            ->andReturn(ConsentVersion::unstable());
        // Now assert that the new stable consent hash is going to be set
        $this->consentRepository
            ->shouldReceive('updateConsentHash')
            ->once()
            ->with(['8739602554c7f3241958e3cc9b57fdecb474d508', '8739602554c7f3241958e3cc9b57fdecb474d508', '0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', $consentType])
            ->andReturn(true);

        $this->assertNull($this->consent->upgradeAttributeHashFor($serviceProvider, $consentType));
    }

    /**
     * @dataProvider consentTypeProvider
     */
    public function test_upgrade_to_stable_consent_not_applied_when_stable($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Stable consent is stored
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->with(['0e54805079c56c2b1c1197a760af86ac337b7bac', 'service-provider-entity-id', '8739602554c7f3241958e3cc9b57fdecb474d508', '8739602554c7f3241958e3cc9b57fdecb474d508', $consentType])
            ->once()
            ->andReturn(ConsentVersion::stable());
        // Now assert that the new stable consent hash is NOT going to be set
        $this->consentRepository
            ->shouldNotReceive('storeConsentHash');
        $this->assertNull($this->consent->upgradeAttributeHashFor($serviceProvider, $consentType));
    }

    public function consentTypeProvider()
    {
        yield [ConsentType::TYPE_IMPLICIT];
        yield [ConsentType::TYPE_EXPLICIT];
    }
}
