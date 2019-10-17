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

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

class ServiceProviderProxyTest extends AbstractServiceProviderDecoratorTest
{

    public function test_methods()
    {
        $adapter = $this->createServiceProviderAdapter();

        $certificateMock = $this->createMock(X509Certificate::class);
        $keyPairMock = $this->createMock(X509KeyPair::class);
        $keyPairMock->method('getCertificate')
            ->willReturn($certificateMock);

        $attributes = [
            new RequestedAttribute(2),
            new RequestedAttribute(3),
            new RequestedAttribute(1),
        ];
        $attributesMock = $this->createMock(AttributesMetadata::class);
        $attributesMock->method('getRequestedAttributes')
            ->willReturn($attributes);

        $consentServiceMock = $this->createMock(Service::class);

        $decorator = new ServiceProviderProxy($adapter, $keyPairMock, $attributesMock, $consentServiceMock);

        $assertions = $this->getServiceProviderAssertions($adapter, $decorator);

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        $assertions['certificates'] = [[$certificateMock], $decorator->getCertificates()];
        $assertions['supportedNameIdFormats'] = [$supportedNameIdFormats, $decorator->getSupportedNameIdFormats()];
        $assertions['requestedAttributes'] = [$attributes, $decorator->getRequestedAttributes()];
        $assertions['responseProcessingService'] = [$consentServiceMock, $decorator->getResponseProcessingService()];

        $this->runServiceProviderAssertions($assertions);
    }
}