<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Tests;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlockBundle\Sfo\SfoEndpoint;
use PHPUnit_Framework_TestCase as TestCase;

class SfoEndpointTest extends TestCase
{
    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_endpoint_object_should_be_successful_populated()
    {
        $endpoint = new SfoEndpoint('entity-id', 'https://sso-location', '/etc/openconext/engineblock.crt');

        $this->assertSame('entity-id', $endpoint->getEntityId());
        $this->assertSame('https://sso-location', $endpoint->getSsoLocation());
        $this->assertSame('/etc/openconext/engineblock.crt', $endpoint->getKeyFile());
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_endpoint_object_entityId_should_be_a_string()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EntityId should be a string');

        $endpoint = new SfoEndpoint(null, 'https://sso-location', 'tests/resources/key/engineblock.key');
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_endpoint_object_ssoLocation_should_be_a_string()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SSO location should be a string');

        $endpoint = new SfoEndpoint('entity-id', null, 'tests/resources/key/engineblock.key');
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_endpoint_object_keyFile_should_be_a_string()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('KeyFile should be a string');

        $endpoint = new SfoEndpoint('entity-id', 'https://sso-location', null);
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_endpoint_object_keyFile_should_be_a_file()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Keyfile should be a valid file');

        $endpoint = new SfoEndpoint('entity-id', 'https://sso-location', 'tests/resources/key/non-existent-file.key');
    }
}
