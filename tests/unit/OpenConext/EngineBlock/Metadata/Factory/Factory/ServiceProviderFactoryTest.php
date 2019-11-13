<?php declare(strict_types=1);
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

namespace OpenConext\EngineBlock\Metadata\Factory\Factory;

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use Symfony\Component\Translation\TranslatorInterface;

class ServiceProviderFactoryTest extends AbstractEntityTest
{
    /**
     * @var ServiceProviderFactory
     */
    private $factory;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attributes;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $keyPairFactory;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $configuration;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $translator;

    public function setup()
    {
        $this->attributes = $this->createMock(AttributesMetadata::class);
        $this->keyPairFactory = $this->createMock(KeyPairFactory::class);
        $this->configuration = $this->createMock(EngineBlockConfiguration::class);
        $this->urlProvider = $this->createMock(UrlProvider::class);

        $this->factory = new ServiceProviderFactory($this->attributes, $this->keyPairFactory, $this->configuration, $this->urlProvider);

        $this->translator = $this->createMock(TranslatorInterface::class);
    }


    public function test_create_engineblock_entity_from()
    {
        $entity = new ServiceProvider('entityId');
        $entity = $this->factory->createEngineBlockEntityFrom(
            'entityID',
            'default'
        );

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $entity);
    }

    public function test_create_stepup_entity_from()
    {
        $entity = new ServiceProvider('entityId');
        $entity = $this->factory->createStepupEntityFrom(
            'entityID',
            'default'
        );

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $entity);
    }

    public function test_eb_properties()
    {
        $this->translator->expects($this->exactly(1))
            ->method('trans')
            ->with('suite_name')
            ->willReturn('test-suite');

        $this->configuration = new EngineBlockConfiguration(
            $this->translator,
            'configuredSupportUrl',
            'configuredSupportMail',
            'configuredDescription',
            'configuredLogoUrl',
            1209,
            1009
        );

        $certificateMock = $this->createMock(X509Certificate::class);
        $keyPairMock = $this->createMock(X509KeyPair::class);
        $keyPairMock->method('getCertificate')
            ->willReturn($certificateMock);
        $this->keyPairFactory->method('buildFromIdentifier')
            ->with('initial-key-id')
            ->willReturn($keyPairMock);

        $attributes = [
            new RequestedAttribute(2),
            new RequestedAttribute(3),
            new RequestedAttribute(1),
        ];
        $this->attributes->method('getRequestedAttributes')
            ->willReturn($attributes);


        $this->urlProvider->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                // EntityId: EngineBlockServiceProvider::getEntityId
                ['metadata_sp', false, null, null],
                // ACS: EngineBlockServiceProvider::getAssertionConsumerService
                ['authentication_sp_consume_assertion', false, null, null]
            ) ->willReturnOnConsecutiveCalls(
                // EntityId
                'EbEntityId',
                // ACS
                'proxiedAcsLocation'
            );


        $this->factory = new ServiceProviderFactory($this->attributes, $this->keyPairFactory, $this->configuration, $this->urlProvider);

        $adapter = $this->createServiceProviderAdapter();
        $decorator = $this->factory->createEngineBlockEntityFrom('initial-key-id');



        // Logo we would expect
        $logo = new Logo('configuredLogoUrl');
        $logo->width = 1209;
        $logo->height = 1009;

        // Organization we would expect
        $organization = new Organization('test-suite', 'test-suite', 'configuredSupportUrl');

        // contacts we would expect
        $contactPersons = [
            ContactPerson::from('support', 'test-suite', 'Support', 'configuredSupportMail'),
            ContactPerson::from('technical', 'test-suite', 'Support', 'configuredSupportMail'),
            ContactPerson::from('administrative', 'test-suite', 'Support', 'configuredSupportMail'),
        ];



        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];




        // the actual assertions
        $overrides = [];

        // default values
        $overrides['id'] = null;
        $overrides['displayNameNl'] = '';
        $overrides['displayNameEn'] = '';
        $overrides['keywordsNl'] = '';
        $overrides['keywordsEn'] = '';
        $overrides['workflowState'] = 'prodaccepted';
        $overrides['nameIdFormat'] = null;
        $overrides['singleLogoutService'] = null;
        $overrides['requestsMustBeSigned'] = false;
        $overrides['manipulation'] = null;
        $overrides['coins'] = Coins::createForServiceProvider(
            true,
            false,
            false,
            false,
            null,
            false,
            false,
            false,
            false,
            null,
            null,
            false,
            false,
            XMLSecurityKey::RSA_SHA256
        );
        $overrides['attributeReleasePolicy'] = null;
        $overrides['allowedIdpEntityIds'] = [];
        $overrides['allowed'] = true;
        $overrides['displayName'] = 'EbEntityId'; // DisplayName uses entityId as fallback
        $overrides['attributeAggregationRequired'] = false;


        // TODO: should the methods below not set trough EBIdPInfo?
        $overrides['supportUrlEn'] = null;
        $overrides['supportUrlNl'] = null;




        // EngineblockIdentityProviderInformation
        $overrides['nameNl'] = 'test-suite EngineBlock';
        $overrides['nameEn'] = 'test-suite EngineBlock';
        $overrides['displayNameNl'] = 'test-suite EngineBlock';
        $overrides['displayNameEn'] = 'test-suite EngineBlock';
        $overrides['descriptionNl'] = 'configuredDescription';
        $overrides['descriptionEn'] = 'configuredDescription';
        $overrides['logo'] = $logo;
        $overrides['organizationNl'] = $organization;
        $overrides['organizationEn'] = $organization;
        $overrides['contactPersons'] = $contactPersons;

        // EngineBlockServiceProvider
        $overrides['entityId'] = 'EbEntityId';
        $overrides['certificates'] = [$certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['requestedAttributes'] = $attributes;
        $overrides['assertionConsumerServices'] = [new IndexedService('proxiedAcsLocation', Constants::BINDING_HTTP_POST, 0)];
        $overrides['allowed'] = true;
        $overrides['allowAll'] = true;


        $this->runServiceProviderAssertions($adapter, $decorator, $overrides);

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $decorator);
    }


    public function test_stepup_properties()
    {
        $this->translator->expects($this->exactly(1))
            ->method('trans')
            ->with('suite_name')
            ->willReturn('test-suite');

        $this->configuration = new EngineBlockConfiguration(
            $this->translator,
            'configuredSupportUrl',
            'configuredSupportMail',
            'configuredDescription',
            'configuredLogoUrl',
            1209,
            1009
        );

        $certificateMock = $this->createMock(X509Certificate::class);
        $keyPairMock = $this->createMock(X509KeyPair::class);
        $keyPairMock->method('getCertificate')
            ->willReturn($certificateMock);
        $this->keyPairFactory->method('buildFromIdentifier')
            ->with('initial-key-id')
            ->willReturn($keyPairMock);

        $attributes = [
            new RequestedAttribute(2),
            new RequestedAttribute(3),
            new RequestedAttribute(1),
        ];
        $this->attributes->method('getRequestedAttributes')
            ->willReturn($attributes);


        $this->urlProvider->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                // EntityId
                ['metadata_stepup', false, null, null],
                // ACS: ServiceProvider::getAssertionConsumerService
                ['authentication_stepup_consume_assertion', false, null, null]
            ) ->willReturnOnConsecutiveCalls(
                // EntityId
                'StepupEntityId',
                // ACS
                'proxiedAcsLocation'
            );


        $this->factory = new ServiceProviderFactory($this->attributes, $this->keyPairFactory, $this->configuration, $this->urlProvider);

        $adapter = $this->createServiceProviderAdapter();
        $decorator = $this->factory->createStepupEntityFrom('initial-key-id');


        // the actual assertions
        $overrides = [];

        // default values
        $overrides['id'] = null;
        $overrides['displayNameNl'] = '';
        $overrides['displayNameEn'] = '';
        $overrides['keywordsNl'] = '';
        $overrides['keywordsEn'] = '';
        $overrides['workflowState'] = 'prodaccepted';
        $overrides['nameIdFormat'] = null;
        $overrides['singleLogoutService'] = null;
        $overrides['requestsMustBeSigned'] = false;
        $overrides['manipulation'] = null;
        $overrides['coins'] = Coins::createForServiceProvider(
            true,
            false,
            false,
            false,
            null,
            false,
            false,
            false,
            false,
            null,
            null,
            false,
            false,
            XMLSecurityKey::RSA_SHA256
        );
        $overrides['attributeReleasePolicy'] = null;
        $overrides['allowedIdpEntityIds'] = [];
        $overrides['allowed'] = false;
        $overrides['displayName'] = 'StepupEntityId'; // DisplayName uses entityId as fallback
        $overrides['attributeAggregationRequired'] = false;
        $overrides['requestedAttributes'] = null;


        // TODO: should the methods below not set trough EBIdPInfo?
        $overrides['supportUrlEn'] = null;
        $overrides['supportUrlNl'] = null;

        $overrides['nameNl'] = '';
        $overrides['nameEn'] = '';
        $overrides['descriptionNl'] = '';
        $overrides['descriptionEn'] = '';
        $overrides['logo'] = null;
        $overrides['organizationNl'] = '';
        $overrides['organizationEn'] = '';
        $overrides['contactPersons'] = [];

        // Stepup
        $overrides['entityId'] = 'StepupEntityId';
        $overrides['certificates'] = [$certificateMock];
        $overrides['supportedNameIdFormats'] = [];
        $overrides['assertionConsumerServices'] = [new IndexedService('proxiedAcsLocation', Constants::BINDING_HTTP_POST, 0)];
        $overrides['allowAll'] = false;


        $this->runServiceProviderAssertions($adapter, $decorator, $overrides);

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $decorator);
    }
}
