<?php

/**
 * Copyright 2024 SURFnet B.V.
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

use Mockery as m;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EngineBlock_Arp_NameIdSubstituteResolverTest extends TestCase
{
    private $resolver;
    private $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loggerMock = m::mock(LoggerInterface::class);
        $this->resolver = new EngineBlock_Arp_NameIdSubstituteResolver($this->loggerMock);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testFindNameIdSubstituteWithValidSubstitute()
    {
        $arp = m::mock(AttributeReleasePolicy::class);
        $arp->shouldReceive('findNameIdSubstitute')->andReturn('email');

        $responseAttributes = ['email' => ['test@example.com']];

        $this->loggerMock->shouldReceive('notice')
            ->with('Found a NameId substitute ("use_as_nameid", email will be used as NameID)')
            ->once();

        $result = $this->resolver->findNameIdSubstitute($arp, $responseAttributes);

        $this->assertEquals('test@example.com', $result);
    }

    public function testFindNameIdSubstituteWithInvalidSubstitute()
    {
        $arp = m::mock(AttributeReleasePolicy::class);

        $arp->shouldReceive('findNameIdSubstitute')->andReturn('non_existent_attribute');

        $responseAttributes = ['email' => ['test@example.com']];

        $result = $this->resolver->findNameIdSubstitute($arp, $responseAttributes);

        $this->assertNull($result);
    }
}
