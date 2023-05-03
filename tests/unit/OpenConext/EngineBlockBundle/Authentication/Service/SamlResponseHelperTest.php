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

namespace OpenConext\EngineBlockBundle\Tests;

use EngineBlock_Saml2_ResponseAnnotationDecorator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Authentication\Service\SamlResponseHelper;
use OpenConext\EngineBlockBundle\Exception\LogicException;
use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\XML\saml\Issuer;
use SimpleXMLElement;
use Symfony\Component\Form\Tests\Extension\Validator\ViolationMapper\Fixtures\Issue;

class SamlResponseHelperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var SamlResponseHelper $helper */
    private $helper;

    /** @var MetadataRepositoryInterface&m\MockInterface  */
    private $repo;

    protected function setUp(): void
    {
        $this->repo = m::mock(MetadataRepositoryInterface::class);
        $this->helper = new SamlResponseHelper($this->repo);
    }

    public function test_create_authn_failed_response()
    {
        $spEntityId = 'https://sp.example.org/metadata';
        $idpEntityId = 'https://idp.example.org/metadata';
        $requestId = 'arbitrary-request-id';

        $mockSp = m::mock(ServiceProvider::class);
        $mockSp->assertionConsumerServices = [
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_HTTP_POST, 0)
        ];
        $mockSp->shouldReceive('getCoins->isTransparentIssuer')
            ->andReturn(false);

        $this->repo
            ->shouldReceive('findServiceProviderByEntityId')
            ->with($spEntityId)
            ->andReturn($mockSp);

        $issuer = new Issuer();
        $issuer->setValue($spEntityId);
        $originalResponse = m::mock(EngineBlock_Saml2_ResponseAnnotationDecorator::class);

        $originalResponse
            ->shouldReceive('getIssuer')
            ->andReturn($issuer);

        $originalResponse
            ->shouldReceive('getStatus')
            ->andReturn(
                ['Code' => 'testCode', 'SubCode' => 'testSubCode']
            );

        $response = $this->helper->createAuthnFailedResponse(
            $spEntityId,
            $idpEntityId,
            $requestId,
            'message',
            $originalResponse
        );

        // Parse the SAML response into a SimpleXMLElement
        $response = base64_decode($response);
        $responseXml = new SimpleXMLElement($response);

        /**
         * Test if the issuer was set correctly
         * @var SimpleXMLElement $issuer
         */
        $responseResponse = array_pop($responseXml->xpath('/samlp:Response'));
        self::assertEquals($responseResponse['InResponseTo'], $requestId);
        self::assertEquals($responseResponse['Destination'], 'https://sp.example.org/assertion/consumer');

        /**
         * Test if the issuer was set correctly
         * @var SimpleXMLElement $issuer
         */
        $responseIssuer = array_pop($responseXml->xpath('/samlp:Response/saml:Issuer'));
        self::assertEquals($responseIssuer, $issuer->getValue());

        /**
         * Test if the codes are correctly copied from the original saml failed response
         * @var SimpleXMLElement $code
         */
        $responseCode = array_pop($responseXml->xpath('/samlp:Response/samlp:Status/samlp:StatusCode'));
        $responseSubCode = array_pop($responseXml->xpath('/samlp:Response/samlp:Status/samlp:StatusCode/samlp:StatusCode'));
        self::assertEquals($responseCode['Value'], 'testCode');
        self::assertEquals($responseSubCode['Value'], 'testSubCode');
    }

    public function test_create_authn_failed_response_transparent()
    {
        $spEntityId = 'https://sp.example.org/metadata';
        $originalSpEntityId = 'https://original-sp.example.org/metadata';
        $idpEntityId = 'https://idp.example.org/metadata';
        $requestId = 'arbitrary-request-id';

        $mockSp = m::mock(ServiceProvider::class);
        $mockSp->assertionConsumerServices = [
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_HTTP_POST, 0)
        ];
        $mockSp->shouldReceive('getCoins->isTransparentIssuer')
            ->andReturn(true);

        $this->repo
            ->shouldReceive('findServiceProviderByEntityId')
            ->with($spEntityId)
            ->andReturn($mockSp);

        $issuer = new Issuer();
        $issuer->setValue($spEntityId);

        $originalResponse = m::mock(EngineBlock_Saml2_ResponseAnnotationDecorator::class);

        $originalResponse
            ->shouldReceive('getIssuer')
            ->andReturn($issuer);
        $originalResponse
            ->shouldReceive('getOriginalIssuer')
            ->andReturn($originalSpEntityId);

        $originalResponse
            ->shouldReceive('getStatus')
            ->andReturn(
                ['Code' => 'testCode', 'SubCode' => 'testSubCode']
            );

        $response = $this->helper->createAuthnFailedResponse(
            $spEntityId,
            $idpEntityId,
            $requestId,
            'message',
            $originalResponse
        );

        // Parse the SAML response into a SimpleXMLElement
        $response = base64_decode($response);
        $responseXml = new SimpleXMLElement($response);

        /**
         * Test if the issuer was set correctly
         * @var SimpleXMLElement $issuer
         */
        $responseResponse = array_pop($responseXml->xpath('/samlp:Response'));
        self::assertEquals($responseResponse['InResponseTo'], $requestId);
        self::assertEquals($responseResponse['Destination'], 'https://sp.example.org/assertion/consumer');

        /**
         * Test if the issuer was set correctly
         * @var SimpleXMLElement $issuer
         */
        $responseIssuer = array_pop($responseXml->xpath('/samlp:Response/saml:Issuer'));
        self::assertEquals($responseIssuer, $originalSpEntityId);

        /**
         * Test if the codes are correctly copied from the original saml failed response
         * @var SimpleXMLElement $code
         */
        $responseCode = array_pop($responseXml->xpath('/samlp:Response/samlp:Status/samlp:StatusCode'));
        $responseSubCode = array_pop($responseXml->xpath('/samlp:Response/samlp:Status/samlp:StatusCode/samlp:StatusCode'));
        self::assertEquals($responseCode['Value'], 'testCode');
        self::assertEquals($responseSubCode['Value'], 'testSubCode');
    }

    /**
     * @dataProvider provideAcuData
     */
    public function test_get_acu(array $inputAcus, string $expectedAcu): void
    {
        $spEntityId = 'https://existing.example.org';
        $mockSp = m::mock(ServiceProvider::class);
        $mockSp->assertionConsumerServices = $inputAcus;
        $this->repo
            ->shouldReceive('findServiceProviderByEntityId')
            ->with($spEntityId)
            ->andReturn($mockSp);
        self::assertEquals($expectedAcu, $this->helper->getAcu($spEntityId));
    }

    public function provideAcuData(): array
    {
        $acuLocation0 = 'https://sp.example.org/assertion/consumer';
        $acuLocation1 = 'https://sp.example.org/assertion/consumer-1';
        $acuLocation2 = 'https://sp.example.org/assertion/consumer-2';
        $acuLocation3 = 'https://sp.example.org/assertion/consumer-3';
        return [
            'one post acs location' => [
                [
                    new IndexedService($acuLocation0, Constants::BINDING_HTTP_POST, 0)
                ],
                'https://sp.example.org/assertion/consumer'
            ],
            'three post acs location yields first' => [
                [
                    new IndexedService($acuLocation0, Constants::BINDING_HTTP_POST, 0),
                    new IndexedService($acuLocation1, Constants::BINDING_HTTP_POST, 1),
                    new IndexedService($acuLocation2, Constants::BINDING_HTTP_POST, 2),
                ],
                'https://sp.example.org/assertion/consumer'
            ],
            'mixed bindings, only post is returned' => [
                [
                    new IndexedService($acuLocation0, Constants::BINDING_HOK_SSO, 0),
                    new IndexedService($acuLocation1, Constants::BINDING_HTTP_POST, 1),
                    new IndexedService($acuLocation2, Constants::BINDING_HTTP_ARTIFACT, 2),
                ],
                'https://sp.example.org/assertion/consumer-1'
            ],
            'mixed bindings, only first post is returned' => [
                [
                    new IndexedService($acuLocation0, Constants::BINDING_HOK_SSO, 0),
                    new IndexedService($acuLocation1, Constants::BINDING_HTTP_ARTIFACT, 1),
                    new IndexedService($acuLocation2, Constants::BINDING_HTTP_POST, 2),
                    new IndexedService($acuLocation3, Constants::BINDING_HTTP_POST, 3),
                ],
                'https://sp.example.org/assertion/consumer-2'
            ]
        ];
    }

    public function test_get_acu_http_post_must_be_present(): void
    {
        $spEntityId = 'https://existing.example.org';
        $mockSp = m::mock(ServiceProvider::class);
        $mockSp->assertionConsumerServices = [
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_HOK_SSO, 0),
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_HTTP_ARTIFACT, 1),
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_PAOS, 1),
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_SOAP, 1),
        ];
        $this->repo
            ->shouldReceive('findServiceProviderByEntityId')
            ->with($spEntityId)
            ->andReturn($mockSp);

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('No suitable ACS location could be find, no HTTP-POST binding available');
        $this->helper->getAcu($spEntityId);
    }

    public function test_get_acu_of_non_existing_sp(): void
    {
        $spEntityId = 'https://404.example.org';
        $mockSp = m::mock(ServiceProvider::class);
        $mockSp->assertionConsumerServices = [
            new IndexedService('https://sp.example.org/assertion/consumer', Constants::BINDING_HTTP_POST, 0),
        ];
        $this->repo
            ->shouldReceive('findServiceProviderByEntityId')
            ->with($spEntityId)
            ->andReturnNull();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('The SP with entity id "https://404.example.org" could not be found while building the error response');
        $this->helper->getAcu($spEntityId);
    }
}
