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
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

class IdentityProviderProxyTest extends AbstractEntityTest
{
    public function test_methods()
    {
        $adapter = $this->createIdentityProviderAdapter();

        $certificateMock = $this->createMock(X509Certificate::class);
        $keyPairMock = $this->createMock(X509KeyPair::class);
        $keyPairMock->method('getCertificate')
            ->willReturn($certificateMock);

        $urlProvider = $this->createMock(UrlProvider::class);

        $urlProvider->expects($this->exactly(5))
            ->method('getUrl')
            ->withConsecutive(
                ['authentication_idp_sso', false, null, null], // check if entity is EB (SLO)
                ['authentication_logout', false, null, null],
                ['authentication_idp_sso', false, null, null], // check if entity is EB (RPS)
                ['authentication_idp_sso', false, null, null], // check if entity is EB (SSO)
                ['authentication_idp_sso', false, null, null]
            ) ->willReturnOnConsecutiveCalls(
                'ssoLocation',
                'sloLocation',
                'ssoLocation',
                'ssoLocation',
                'ssoLocation'
            );

        $decorator = new IdentityProviderProxy($adapter, $keyPairMock, $urlProvider);

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        $overrides['certificates'] = [$certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['singleSignOnServices'] = [new Service('ssoLocation', Constants::BINDING_HTTP_REDIRECT)];
        $overrides['singleLogoutService'] = new Service('sloLocation', Constants::BINDING_HTTP_REDIRECT);
        $overrides['responseProcessingService'] = new Service('/authentication/idp/provide-consent', 'INTERNAL');

        $this->runIdentityProviderAssertions($adapter, $decorator, $overrides);
    }
}
