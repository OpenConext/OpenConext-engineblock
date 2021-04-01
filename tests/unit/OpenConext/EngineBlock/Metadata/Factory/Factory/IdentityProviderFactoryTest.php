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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;
use Symfony\Component\Translation\TranslatorInterface;

class IdentityProviderFactoryTest extends AbstractEntityTest
{
    use MockeryPHPUnitIntegration;

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

    public function setUp(): void
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
        $entity = $this->factory->createIdentityProviderEntityFromEntity($entity, 'default');

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }

    public function test_create_idp_entity_from_entity_properties()
    {
        $this->translator->expects($this->at(0))
            ->method('trans')
            ->with('suite_name')
            ->willReturn('test-suite');

        $this->translator->expects($this->at(1))
            ->method('trans')
            ->with('metadata_organization_name')
            ->willReturn('configuredOrganizationName');

        $this->translator->expects($this->at(2))
            ->method('trans')
            ->with('metadata_organization_displayname')
            ->willReturn('configuredOrganizationDisplayName');

        $this->translator->expects($this->at(3))
            ->method('trans')
            ->with('metadata_organization_url')
            ->willReturn('configuredOrganizationUrl');

        $this->configuration = new EngineBlockConfiguration(
            $this->translator,
            'configuredSupportMail',
            'configuredDescription',
            'example.org',
            '/configuredLogoUrl.gif',
            1209,
            1009
        );

        $this->factory = new IdentityProviderFactory($this->keyPairFactory, $this->configuration, $this->urlProvider);

        $values = $this->getIdentityProviderMockProperties();
        $entity = $this->getOrmEntityIdentityProviderMock($values);
        $adapter = new IdentityProviderEntity($entity);
        $decorator = $this->factory->createIdentityProviderEntityFromEntity($entity, 'default');

        // Logo we would expect
        $logo = new Logo('https://example.org/configuredLogoUrl.gif');
        $logo->width = 1209;
        $logo->height = 1009;

        // Organization we would expect
        $organization = new Organization('configuredOrganizationName', 'configuredOrganizationDisplayName', 'configuredOrganizationUrl');

        // contacts we would expect
        $contactPersons = [
            ContactPerson::from('support', 'configuredOrganizationName', 'Support', 'configuredSupportMail'),
            ContactPerson::from('technical', 'configuredOrganizationName', 'Support', 'configuredSupportMail'),
            ContactPerson::from('administrative', 'configuredOrganizationName', 'Support', 'configuredSupportMail'),
        ];

        $this->urlProvider->expects($this->exactly(1))
            ->method('getUrl')
            ->withConsecutive(
            // SSO: EngineBlockIdentityProvider::getSingleSignOnServices
                ['authentication_idp_sso', false, 'default', 'entity-id']
            ) ->willReturnOnConsecutiveCalls(
            // SSO
                'proxiedSsoLocation'
            );

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        // the actual assertions
        $overrides = [];

        // EngineblockIdentityProviderInformation
        $overrides['nameNl'] = 'name-nl';
        $overrides['nameEn'] = 'name-en';
        $overrides['namePt'] = 'name-pt';
        $overrides['displayNameNl'] = 'display-name-nl';
        $overrides['displayNameEn'] = 'display-name-en';
        $overrides['displayNamePt'] = 'display-name-pt';
        $overrides['descriptionNl'] = 'description-nl';
        $overrides['descriptionEn'] = 'description-en';
        $overrides['descriptionPt'] = 'description-pt';
        $overrides['contactPersons'] = $contactPersons;

        // EngineBlockIdentityProvider
        $overrides['certificates'] = [$this->certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['singleSignOnServices'] = [new Service('proxiedSsoLocation', Constants::BINDING_HTTP_REDIRECT)];
        $overrides['singleLogoutService'] = new Service(null, null);

        $this->runIdentityProviderAssertions($adapter, $decorator, $overrides);

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $decorator);
    }
}
