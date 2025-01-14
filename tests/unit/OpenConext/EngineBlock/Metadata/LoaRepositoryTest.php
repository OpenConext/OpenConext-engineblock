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

namespace OpenConext\EngineBlock\Metadata;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LoaNotFoundException;
use PHPUnit\Framework\TestCase;
use function substr;

class LoaRepositoryTest extends TestCase
{
    public function test_build_from_valid_config()
    {
        $repository = new LoaRepository($this->getValidConfigAsArray());
        // No exceptions are raised when creating the repository with a valid config
        $this->expectNotToPerformAssertions();
    }

    public function test_build_from_valid_config_empty()
    {
        $repository = new LoaRepository([]);
        // No exceptions are raised when creating the repository with a valid config
        $this->expectNotToPerformAssertions();
    }

    public function test_it_can_find_by_loa_identifier()
    {
        $repository = new LoaRepository($this->getValidConfigAsArray());

        $existingLoa = 'http://dev.openconext.local/assurance/loa1';
        $loa = $repository->getByIdentifier($existingLoa);

        $this->assertInstanceOf(Loa::class, $loa);
    }

    public function test_it_throws_an_exception_when_loa_not_found()
    {
        $repository = new LoaRepository($this->getValidConfigAsArray());
        $nonExistingLoa = 'foobar';

        $this->expectException(LoaNotFoundException::class);
        $this->expectExceptionMessage('Unable to find LoA with identifier "foobar"');
        $repository->getByIdentifier($nonExistingLoa);

    }

    public function test_the_getter_only_returns_eb_loas()
    {
        $repository = new LoaRepository($this->getValidConfigAsArray());
        $loas = $repository->getStepUpLoas();
        foreach ($loas as $loa) {
            $this->assertFalse(strpos($loa->getIdentifier(), 'gateway'));
        }
    }

    /**
     * @dataProvider provideInvalidConfig
     */
    public function test_it_raises_exceptions_when_constructed_with_invalid_configuration(
        $config,
        $expectedExceptionMessage
    ) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        new LoaRepository($config);
    }

    private function getValidConfigAsArray()
    {
        return [
            10 => ["engineblock" => "http://dev.openconext.local/assurance/loa1", "gateway" => "https://gateway.tld/authentication/loa1"],
            15 => ["engineblock" => "http://dev.openconext.local/assurance/loa1_5", "gateway" => "https://gateway.tld/authentication/loa1_5"],
            20 => ["engineblock" => "http://dev.openconext.local/assurance/loa2", "gateway" => "https://gateway.tld/authentication/loa2"],
            30 => ["engineblock" => "http://dev.openconext.local/assurance/loa3", "gateway" => "https://gateway.tld/authentication/loa3"]
        ];
    }

    public function provideInvalidConfig()
    {
        return [
            [[10 => ['engineBlock' => 'loa1', 'gateway' => 'loa1']], 'Both the engineblock and gateway keys must be present in every LoA mapping.'],
            [[10 => ['gateway' => 'loa1']], 'Both the engineblock and gateway keys must be present in every LoA mapping.'],
            [[10 => []], 'Both the engineblock and gateway keys must be present in every LoA mapping.'],
            [[20 => ['engineblock' => null, 'gateway' => 'loa1']], 'The EngineBlock LoA must be a string value'],
            [[20 => ['engineblock' => 3, 'gateway' => 'loa1']], 'The EngineBlock LoA must be a string value'],
            [[20 => ['engineblock' => false, 'gateway' => 'loa1']], 'The EngineBlock LoA must be a string value'],
            [[30 => ['engineblock' => 'loa1', 'gateway' => null]], 'The Gateway LoA must be a string value'],
            [[30 => ['engineblock' => 'loa1', 'gateway' => 4]], 'The Gateway LoA must be a string value'],
            [[30 => ['engineblock' => 'loa1', 'gateway' => true]], 'The Gateway LoA must be a string value'],
        ];
    }
}
