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

class EngineblockIdentityProviderTest extends AbstractEntityTest
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
        $matcher = $this->exactly(2);

        $this->urlProvider->expects($matcher)
            ->method('getUrl')->willReturnCallback(function (...$parameters) use ($matcher) {
            if ($matcher->numberOfInvocations() === 1) {
                $this->assertSame('authentication_logout', $parameters[0]);
                $this->assertSame(false, $parameters[1]);
                $this->assertSame(null, $parameters[2]);
                $this->assertSame(null, $parameters[3]);
                return 'sloLocation';
            }
            if ($matcher->numberOfInvocations() === 2) {
                $this->assertSame('authentication_idp_sso', $parameters[0]);
                $this->assertSame(false, $parameters[1]);
                $this->assertSame('default', $parameters[2]);
                $this->assertSame(null, $parameters[3]);
                return 'ssoLocation';
            }
        });

        $decorator = new EngineBlockIdentityProvider($this->adapter, 'default', $this->keyPairMock, $this->urlProvider);

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        $overrides['certificates'] = [$this->certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['singleSignOnServices'] = [new Service('ssoLocation', Constants::BINDING_HTTP_REDIRECT)];
        $overrides['singleLogoutService'] = new Service('sloLocation', Constants::BINDING_HTTP_REDIRECT);

        $this->runIdentityProviderAssertions($this->adapter, $decorator, $overrides);
    }

    public function test_override_slo_service_if_child_slo_service_set()
    {
        $this->adapter = $this->createIdentityProviderAdapter();

        $this->urlProvider->expects($this->once())
            ->method('getUrl')
            ->with('authentication_logout', false, null, null)
            ->willReturn('sloLocation');

        $decorator = new EngineBlockIdentityProvider($this->adapter, 'default', $this->keyPairMock, $this->urlProvider);

        $this->assertEquals($decorator->getSingleLogoutService(), new Service('sloLocation', Constants::BINDING_HTTP_REDIRECT));
    }

    public function test_return_null_for_slo_service_if_child_has_no_slo_service_set()
    {
        $this->adapter = $this->createIdentityProviderAdapter(false, false, [
            'singleLogoutService' => null,
        ]);

        $decorator = new EngineBlockIdentityProvider($this->adapter, 'default', $this->keyPairMock, $this->urlProvider);

        $this->assertEquals($decorator->getSingleLogoutService(), null);
    }

    public function test_do_not_add_entity_id_hash_to_service_url_for_sso_service_if_eb_self()
    {
        $this->adapter = $this->createIdentityProviderAdapter();
        $matcher = $this->exactly(1);

        $this->urlProvider->expects($matcher)
            ->method('getUrl')
            ->willReturnCallback(
                function (...$parameters) use ($matcher) {
                    if ($matcher->numberOfInvocations() === 1) {
                        $this->assertSame('authentication_idp_sso', $parameters[0]);
                        $this->assertSame(false, $parameters[1]);
                        $this->assertSame('default', $parameters[2]);
                        $this->assertSame(null, $parameters[3]);
                        return 'ssoLocation';
                    }
                }  // we would expect the fourth paremeter to be null and not 'entity-id' becasue we are EB
            );

        $decorator = new EngineBlockIdentityProvider($this->adapter, 'default', $this->keyPairMock, $this->urlProvider);

        $this->assertEquals([new Service('ssoLocation', Constants::BINDING_HTTP_REDIRECT)], $decorator->getSingleSignOnServices());
    }
}
