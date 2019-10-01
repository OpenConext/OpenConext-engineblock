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

namespace OpenConext\EngineBlock\Metadata\X509;

use PHPUnit\Framework\TestCase;

/**
 * Class PrivateKeyTest
 * @package OpenConext\EngineBlock\Metadata\X509
 */
class X509PrivateKeyTest extends TestCase
{
    public function testSigning()
    {
        $data = 'test';

        $filePath = __DIR__ . '/test.pem.key';
        $privateKey = new X509PrivateKey($filePath);
        $signature = $privateKey->sign($data);

        $publicKey = new X509Certificate(openssl_pkey_get_public('file://' . __DIR__ . '/test.pem.crt'));

        $this->assertEquals(1, openssl_verify($data, $signature, $publicKey->toResource()));
        $this->assertEquals($filePath, $privateKey->getFilePath());
    }

    public function testXmlSecurityKey()
    {
        $data = 'test';

        $filePath = __DIR__ . '/test.pem.key';
        $privateKey = new X509PrivateKey($filePath);
        $xmlSecurityKey = $privateKey->toXmlSecurityKey();

        $signature = $xmlSecurityKey->signData($data);

        $publicKey = new X509Certificate(openssl_pkey_get_public('file://' . __DIR__ . '/test.pem.crt'));

        $this->assertEquals(1, openssl_verify($data, $signature, $publicKey->toResource(), OPENSSL_ALGO_SHA1));
    }
}
