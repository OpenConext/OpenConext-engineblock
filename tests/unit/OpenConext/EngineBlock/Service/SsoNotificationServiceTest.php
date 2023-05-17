<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

namespace OpenConext\EngineBlock\Service;

use EngineBlock_Corto_ProxyServer;
use EngineBlock_Saml2_AuthnRequestAnnotationDecorator;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use Phake;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Tests\Logger;

class SsoNotificationServiceTest extends TestCase
{

    private $idpEntityId = "testIdP";
    private $idpUrl = "https://testIdP.com";
    private $encryptionKey = "testEncryptionKey";
    private $encryptionKeySalt = "testSalt";
    private $encryptionMethod = "AES-256-CBC";
    private $iv = "encryptionTestIv";
    private $cookieValue;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Logger
     */
    private $loggerMock;

    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $proxyServerMock;

    /**
     * @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    private $requestMock;

    /**
     * @var SsoNotificationService
     */
    private $ssoNotificationService;

    public function setUp(): void
    {
        $this->loggerMock = Phake::mock(Logger::class);
        $this->proxyServerMock = Phake::mock(EngineBlock_Corto_ProxyServer::class);
        $this->requestMock = Phake::mock(EngineBlock_Saml2_AuthnRequestAnnotationDecorator::class);
        $this->ssoNotificationService = new SsoNotificationService(
            $this->encryptionKey,
            $this->encryptionKeySalt,
            $this->encryptionMethod,
            $this->loggerMock
        );
        $this->cookieValue = $this->getStandardCookieValue($this->idpEntityId);
        $this->request = new Request();

        Phake::when($this->proxyServerMock)
            ->getRepository()
            ->thenReturn(new InMemoryMetadataRepository(
                array(new IdentityProvider($this->idpEntityId)),
                array()
            ));
    }

    /**
     * @test
     * @group SsoNotification
     */
    public function test_get_sso_cookie()
    {
        $this->request->cookies->add([ 'ssonot' => $this->cookieValue ]);

        $response = $this->ssoNotificationService->getSsoCookie($this->request->cookies);
        $this->assertEquals($this->cookieValue, $response);

    }

    /**
     * @test
     * @group SsoNotification
     */
    public function test_handle_sso_notification()
    {
        $this->request->cookies->add([ 'ssonot' => $this->cookieValue ]);

        $entityId = $this->ssoNotificationService->handleSsoNotification($this->request->cookies, $this->proxyServerMock);

        $this->assertEquals($this->idpEntityId, $entityId);
    }

    /**
     * @test
     * @group SsoNotification
     */
    public function test_handle_unknown_idp()
    {
        $this->request->cookies->add([ 'ssonot' => $this->getStandardCookieValue($this->idpEntityId . "test") ]);

        $this->ssoNotificationService->handleSsoNotification($this->request->cookies, $this->proxyServerMock);

        Phake::verify($this->loggerMock)->warning(Phake::anyParameters());
    }

    /**
     * @test
     * @group SsoNotification
     */
    public function test_invalid_encryption_key()
    {
        $ssoNotificationService = new SsoNotificationService(
            $this->encryptionKey . "test",
            $this->encryptionKeySalt,
            $this->encryptionMethod,
            $this->loggerMock
        );
        $this->request->cookies->add([ 'ssonot' => $this->cookieValue ]);

        $ssoNotificationService->handleSsoNotification($this->request->cookies, $this->proxyServerMock);

        Phake::verify($this->loggerMock, Phake::times(2))->error(Phake::anyParameters());
        Phake::verify($this->loggerMock)->warning(Phake::anyParameters());
    }

    /**
     * @test
     * @group SsoNotification
     */
    public function test_invalid_json()
    {
        $data = "{\"url\":\"$this->idpUrl\"}";
        $this->request->cookies->add([ 'ssonot' => $this->encryptData($data) ]);

        $this->ssoNotificationService->handleSsoNotification($this->request->cookies, $this->proxyServerMock);

        Phake::verify($this->loggerMock)->warning(Phake::anyParameters());
    }

    private function getStandardCookieValue($entityId)
    {
        $data = "{\"entityId\":\"$entityId\", \"url\":\"$this->idpUrl\"}";
        return $this->encryptData($data);
    }

    private function encryptData($data)
    {
        $key = hash_pbkdf2('sha256', $this->encryptionKey, $this->encryptionKeySalt, 1000, 256, true);
        $encrypted = openssl_encrypt($data, $this->encryptionMethod, $key, OPENSSL_RAW_DATA, $this->iv);
        return base64_encode($this->iv . $encrypted);
    }

}
