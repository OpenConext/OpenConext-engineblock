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

use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

class IdentityProviderProxyTest extends AbstractEntityTest
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

    protected function setUp()
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

        $decorator = new IdentityProviderProxy($this->adapter, $this->keyPairMock, $this->urlProvider);

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        $overrides['certificates'] = [$this->certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['singleSignOnServices'] = [new Service('ssoLocation', Constants::BINDING_HTTP_REDIRECT)];
        $overrides['singleLogoutService'] = new Service('sloLocation', Constants::BINDING_HTTP_REDIRECT);
        $overrides['responseProcessingService'] = new Service('/authentication/idp/provide-consent', 'INTERNAL');

        $this->runIdentityProviderAssertions($this->adapter, $decorator, $overrides);
    }

    public function test_override_slo_service_if_child_slo_service_set()
    {
        $this->adapter = $this->createIdentityProviderAdapter();

        $this->urlProvider->expects($this->once())
            ->method('getUrl')
            ->with('authentication_logout', false, null, null)
            ->willReturn('sloLocation');

        $decorator = new IdentityProviderProxy($this->adapter, $this->keyPairMock, $this->urlProvider);

        $this->assertEquals($decorator->getSingleLogoutService(), new Service('sloLocation', Constants::BINDING_HTTP_REDIRECT));
    }

    public function test_return_null_for_slo_service_if_child_has_no_slo_service_set()
    {
        $this->adapter = $this->createIdentityProviderAdapter([
            'singleLogoutService' => null,
        ]);

        $decorator = new IdentityProviderProxy($this->adapter, $this->keyPairMock, $this->urlProvider);

        $this->assertEquals($decorator->getSingleLogoutService(), null);
    }

    public function test_do_not_add_entity_id_hash_to_service_url_for_sso_service_if_eb_self()
    {
        $this->adapter = $this->createIdentityProviderAdapter();

        $this->urlProvider->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                // SSO: IdentityProviderProxy::getSingleSignOnServices
                ['metadata_idp', false, null, null], // check if entity is EB
                ['authentication_idp_sso', false, null, null]  // we would expect the fourth paremeter to be null and not 'entity-id' becasue we are EB
            ) ->willReturnOnConsecutiveCalls(
            // SLO
                // SSO
                'entity-id', // The entity id should be the metadata url to test if EB becasue we are EB
                'ssoLocation'
            );


        $decorator = new IdentityProviderProxy($this->adapter, $this->keyPairMock, $this->urlProvider);

        $this->assertEquals([new Service('ssoLocation', Constants::BINDING_HTTP_REDIRECT)], $decorator->getSingleSignOnServices());
    }

    public function test_only_add_entity_id_hash_for_sso_service_url_if_not_eb_self()
    {
        $this->adapter = $this->createIdentityProviderAdapter([
            'singleSignOnServices' => null,
        ]);

        $this->urlProvider->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
            // SSO: IdentityProviderProxy::getSingleSignOnServices
                ['metadata_idp', false, null, null], // check if entity is EB
                ['authentication_idp_sso', false, null, 'entity-id']  // we would expect the fourth paremeter to be null and not 'entity-id' becasue we are EB
            ) ->willReturnOnConsecutiveCalls(
            // SLO
            // SSO
                'other-entity-id', // The entity id should be the metadata url to test if EB becasue we are EB
                'ssoLocation?entityIdHash'
            );


        $decorator = new IdentityProviderProxy($this->adapter, $this->keyPairMock, $this->urlProvider);

        $this->assertEquals([new Service('ssoLocation?entityIdHash', Constants::BINDING_HTTP_REDIRECT)], $decorator->getSingleSignOnServices());
    }
}
