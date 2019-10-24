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

namespace OpenConext\EngineBlock\Metadata\Factory\Factory;

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\IdentityProviderProxy;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use Symfony\Component\Translation\TranslatorInterface;

class IdentityProviderFactoryTest extends AbstractEntityTest
{
    /**
     * @var IdentityProviderFactory
     */
    private $factory;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|KeyPairFactory
     */
    private $keyPairFactory;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EngineBlockConfiguration
     */
    private $configuration;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UrlProvider
     */
    private $urlProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    private $translator;

    public function setup()
    {
        $this->keyPairFactory = $this->createMock(KeyPairFactory::class);
        $this->configuration = $this->createMock(EngineBlockConfiguration::class);
        $this->urlProvider = $this->createMock(UrlProvider::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->factory = new IdentityProviderFactory($this->keyPairFactory, $this->configuration, $this->urlProvider);
    }

    public function test_create_entity_from()
    {
        $entity = $this->factory->createEngineBlockEntityFrom(
            'entityID',
            'ssoLocation',
            'default'
        );

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }

    public function test_create_proxy_from_entity()
    {
        $entity = new IdentityProvider('entityId');
        $entity = $this->factory->createEngineBlockEntityFromEntity($entity, 'default');

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }


    public function test_create_engineblock_entity_from_entity_properties()
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

        $this->factory = new IdentityProviderFactory($this->keyPairFactory, $this->configuration, $this->urlProvider);

        $values = $this->getIdentityProviderMockProperties();
        $entity = $this->getOrmEntityIdentityProviderMock($values);
        $adapter = new IdentityProviderEntity($entity);
        $decorator = $this->factory->createEngineBlockEntityFromEntity($entity, 'default');


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


        $this->urlProvider->expects($this->exactly(3))
            ->method('getUrl')
            ->withConsecutive(
            // SLO: IdentityProviderProxy::getSingleLogoutService
                ['authentication_logout', false, null, null],
                // SSO: IdentityProviderProxy::getSingleSignOnServices
                ['metadata_idp', false, null, null], // check if entity is EB
                ['authentication_idp_sso', false, null, 'entity-id']
            ) ->willReturnOnConsecutiveCalls(
            // SLO
                'sloLocation',
                // SSO
                'entityId',
                'ssoLocation'
            );

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];


        // the actual assertions
        $overrides = [];

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

        // IdentityProviderProxy
        $overrides['certificates'] = [$this->certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['singleSignOnServices'] = [new Service('ssoLocation', Constants::BINDING_HTTP_REDIRECT)];
        $overrides['singleLogoutService'] = new Service('sloLocation', Constants::BINDING_HTTP_REDIRECT);
        $overrides['responseProcessingService'] = new Service('/authentication/idp/provide-consent', 'INTERNAL');


        $this->runIdentityProviderAssertions($adapter, $decorator, $overrides);

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $decorator);
    }
}
