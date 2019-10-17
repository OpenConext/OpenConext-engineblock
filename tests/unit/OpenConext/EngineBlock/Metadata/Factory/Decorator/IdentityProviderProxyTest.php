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

use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

class IdentityProviderProxyTest extends AbstractIdentityProviderDecoratorTest
{
    public function test_methods()
    {
        $adapter = $this->createIdentityProviderAdapter();

        $certificateMock = $this->createMock(X509Certificate::class);
        $keyPairMock = $this->createMock(X509KeyPair::class);
        $keyPairMock->method('getCertificate')
            ->willReturn($certificateMock);

        $decorator = new IdentityProviderProxy($adapter, $keyPairMock);

        $assertions = $this->getIdentityProviderAssertions($adapter, $decorator);

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        $assertions['certificates'] = [[$certificateMock], $decorator->getCertificates()];
        $assertions['supportedNameIdFormats'] = [$supportedNameIdFormats, $decorator->getSupportedNameIdFormats()];

        $this->runIdentityProviderAssertions($assertions);
    }
}