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

namespace OpenConext\EngineBlock\Xml;

use OpenConext\EngineBlock\Metadata\X509\X509PrivateKey;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class Saml2BridgeTest extends TestCase
{
    public function testCreateSigningKeyReturnsXmlSecurityKey(): void
    {
        $keyPath = __DIR__ . '/../Metadata/X509/test.pem.key';
        $privateKey = new X509PrivateKey($keyPath);

        $bridge = new Saml2Bridge();
        $result = $bridge->createSigningKey($privateKey);

        $this->assertInstanceOf(XMLSecurityKey::class, $result);
    }

    public function testCreatedKeyCanSign(): void
    {
        $keyPath = __DIR__ . '/../Metadata/X509/test.pem.key';
        $privateKey = new X509PrivateKey($keyPath);

        $bridge = new Saml2Bridge();
        $xmlSecKey = $bridge->createSigningKey($privateKey);

        $signature = $xmlSecKey->signData('test');
        $this->assertNotEmpty($signature);
    }
}
