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
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

class EngineBlockServiceProviderTest extends AbstractEntityTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UrlProvider
     */
    private $urlProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlProvider = $this->createMock(UrlProvider::class);
    }


    public function test_methods()
    {
        $adapter = $this->createServiceProviderAdapter();

        $this->urlProvider->expects($this->exactly(1))
            ->method('getUrl')
            ->withConsecutive(
                // ACS: EngineBlockServiceProvider::getAssertionConsumerServices
                ['authentication_sp_consume_assertion', false, null, null]
            ) ->willReturnOnConsecutiveCalls(
                // ACS
                'acsLocation'
            );

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

        $decorator = new EngineBlockServiceProvider($adapter, $keyPairMock, $attributesMock, $this->urlProvider);

        $supportedNameIdFormats = [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];

        $overrides['certificates'] = [$certificateMock];
        $overrides['supportedNameIdFormats'] = $supportedNameIdFormats;
        $overrides['requestedAttributes'] = $attributes;
        $overrides['assertionConsumerServices'] = [new IndexedService('acsLocation', Constants::BINDING_HTTP_POST, 0)];

        $this->runServiceProviderAssertions($adapter, $decorator, $overrides);
    }
}
