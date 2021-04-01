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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;
use Symfony\Component\Translation\TranslatorInterface;

class ProxiedIdentityProviderTest extends AbstractEntityTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $certificateMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $keyPairMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlProvider;
    /**
     * @var IdentityProviderEntity|null
     */
    private $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = null;
        $this->certificateMock = $this->createMock(X509Certificate::class);
        $this->keyPairMock = $this->createMock(X509KeyPair::class);
        $this->keyPairMock->method('getCertificate')
            ->willReturn($this->certificateMock);

        $this->urlProvider = $this->createMock(UrlProvider::class);
    }

    public function test_methods()
    {
        $this->adapter = $this->createIdentityProviderAdapter();

        $this->configureUrlProvider();

        $configuration = $this->createConfiguration();

        $decorator = new ProxiedIdentityProvider(
            $this->adapter,
            $configuration,
            null,
            $this->keyPairMock,
            $this->urlProvider
        );

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        // Expected contact persons
        $contactPersons = [
            ContactPerson::from('support', 'configuredOrganizationName', 'Support', 'configuredSupportMail'),
            ContactPerson::from('technical', 'configuredOrganizationName', 'Support', 'configuredSupportMail'),
            ContactPerson::from('administrative', 'configuredOrganizationName', 'Support', 'configuredSupportMail'),
        ];

        $overrides['certificates'] = [$this->certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['singleSignOnServices'] = [new Service('proxiedSsoLocation', Constants::BINDING_HTTP_REDIRECT)];
        $overrides['singleLogoutService'] = new Service(null, null); // Verify it matches the mocked SLO service
        $overrides['contactPersons'] = $contactPersons;

        $this->runIdentityProviderAssertions($this->adapter, $decorator, $overrides);
    }

    private function createConfiguration(): EngineBlockConfiguration
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->at(0))
            ->method('trans')
            ->with('suite_name')
            ->willReturn('test-suite');

        $translator->expects($this->at(1))
            ->method('trans')
            ->with('metadata_organization_name')
            ->willReturn('configuredOrganizationName');

        $translator->expects($this->at(2))
            ->method('trans')
            ->with('metadata_organization_displayname')
            ->willReturn('configuredOrganizationDisplayName');

        $translator->expects($this->at(3))
            ->method('trans')
            ->with('metadata_organization_url')
            ->willReturn('configuredOrganizationUrl');

        $configuration = new EngineBlockConfiguration(
            $translator,
            'configuredSupportMail',
            'configuredDescription',
            'configuredLogoUrl',
            'logopath',
            1209,
            1009
        );

        return $configuration;
    }

    private function configureUrlProvider(): void
    {
        $this->urlProvider->expects($this->exactly(1))
            ->method('getUrl')
            ->withConsecutive(
            // SSO: EngineBlockIdentityProvider::getSingleSignOnServices
                ['authentication_idp_sso', false, null, 'entity-id']
            )->willReturnOnConsecutiveCalls(
            // SSO
                'proxiedSsoLocation'
            );
    }
}
