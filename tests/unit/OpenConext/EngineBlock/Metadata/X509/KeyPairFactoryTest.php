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

namespace OpenConext\EngineBlock\Metadata\X509;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlockBundle\Exception\UnknownKeyIdException;
use PHPUnit\Framework\TestCase;

class KeyPairFactoryTest extends TestCase
{
    private $factory;

    public function setUp(): void
    {
        $this->factory = new KeyPairFactory([
            'default' => [
                'publicFile' => 'file://' . __DIR__ . '/test.pem.crt',
                'privateFile' => 'file://' . __DIR__ . '/test.pem.key',
            ],
            'rollover' => [
                'publicFile' => 'file://' . __DIR__ . '/test2.pem.crt',
                'privateFile' => 'file://' . __DIR__ . '/test.pem.key',
            ]
        ]);
    }

    public function test_builds_key_pair_from_default_config()
    {
        $defaultKeyPair = $this->factory->buildFromIdentifier('default');
        $this->assertEquals(file_get_contents('file://' . __DIR__ . '/test.pem.crt'), $defaultKeyPair->getCertificate()->toPem());
        $this->assertEquals('file://' . __DIR__ . '/test.pem.key', $defaultKeyPair->getPrivateKey()->getFilePath());
    }

    public function test_builds_key_pair_from_specified_identifier()
    {
        $defaultKeyPair = $this->factory->buildFromIdentifier('rollover');
        $this->assertEquals(file_get_contents('file://' . __DIR__ . '/test2.pem.crt'), $defaultKeyPair->getCertificate()->toPem());
        $this->assertEquals('file://' . __DIR__ . '/test.pem.key', $defaultKeyPair->getPrivateKey()->getFilePath());
    }

    public function test_it_raises_exception_when_requesting_empty_key_pair_identifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected non-empty string for "identifier", "" given');
        $this->factory->buildFromIdentifier('');
    }

    public function test_it_raises_exception_when_requesting_invalid_key_pair()
    {
        $this->expectException(UnknownKeyIdException::class);
        $this->expectExceptionMessage("Unknown key id 'ahnk-morpork'");
        $this->factory->buildFromIdentifier('ahnk-morpork');
    }
}
