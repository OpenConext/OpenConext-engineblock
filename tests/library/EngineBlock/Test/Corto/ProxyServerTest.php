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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use PHPUnit\Framework\TestCase;
use SAML2\AuthnRequest;
use Surfnet\SamlBundle\Signing\KeyPair;
use Twig\Environment;

/**
 * Note: this Test only tests setting of NameIDFormat, add other tests if required
 */
class EngineBlock_Test_Corto_ProxyServerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testDefaultNameIDPolicy()
    {
        $proxyServer = $this->factoryProxyServer();

        $originalRequest = $this->factoryOriginalRequest();
        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        /** @var AuthnRequest $enhancedRequest */
        $enhancedRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $originalRequest,
            $identityProvider,
            $proxyServer
        );

        $nameIdPolicy = $enhancedRequest->getNameIdPolicy();
        $this->assertSame(['AllowCreate' => true], $nameIdPolicy);
    }

    public function testNameIDFormatIsSetFromRemoteMetaData()
    {
        $proxyServer = $this->factoryProxyServer();
        $originalRequest = $this->factoryOriginalRequest();

        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        $identityProvider->nameIdFormat = 'fooFormat';

        /** @var AuthnRequest $enhancedRequest */
        $enhancedRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $originalRequest,
            $identityProvider,
            $proxyServer
        );

        $nameIdPolicy = $enhancedRequest->getNameIdPolicy();
        $this->assertEquals($nameIdPolicy['Format'], 'fooFormat');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideKeyExpectations')]
    public function testGetSigningCertificates(?string $keyOverride, bool $force, string $expectedResult)
    {
        $keys = [
            'default' => $this->buildKeyPair('default', 'default'),
            'rollover' => $this->buildKeyPair('rollover', 'rollover'),
        ];
        $proxyServer = $this->factoryProxyServer();
        $proxyServer->setKeyPairs($keys);

        if ($keyOverride) {
            $proxyServer->setKeyId($keyOverride);
        }

        $keyPair = $proxyServer->getSigningCertificates($force);
        $this->assertEquals($keyPair->getPrivateKey(), $keys[$expectedResult]->getPrivateKey());
        $this->assertEquals($keyPair->getCertificate(), $keys[$expectedResult]->getCertificate());
    }

    private function buildKeyPair(string $key, string $cert): EngineBlock_X509_KeyPair
    {
        return new EngineBlock_X509_KeyPair(
            new EngineBlock_X509_Certificate(openssl_x509_read(file_get_contents(__DIR__."/fixture/{$key}.pem.crt"))),
            new EngineBlock_X509_PrivateKey(__DIR__."/fixture/{$cert}.pem.key")
        );
    }

    public static function provideKeyExpectations()
    {
        return [
            'force default signing key' => [null, true, 'default'],
            'require specific key is overridden by forcing the default' => ['rollover', true, 'default'],
            'require specific key' => ['rollover', false, 'rollover'],
            'requiring nothing yield the default' => [null, false, 'default'],
        ];
    }

    /**
     * @return array
     */
    private function factoryOriginalRequest()
    {
        $originalRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new AuthnRequest());

        return $originalRequest;
    }

    private function factoryProxyServer()
    {
        $twig = Mockery::mock(Environment::class);
        $proxyServer = new EngineBlock_Corto_ProxyServer($twig);

        $proxyServer->setRepository(new InMemoryMetadataRepository(
            array(new IdentityProvider('testIdp')),
            array()
        ));

        return $proxyServer;
    }
}
