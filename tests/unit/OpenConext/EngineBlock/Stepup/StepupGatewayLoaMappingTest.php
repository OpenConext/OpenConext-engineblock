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

namespace OpenConext\EngineBlock\Stepup;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\LoaRepository;
use OpenConext\EngineBlock\Stepup\StepupGatewayLoaMapping;
use PHPUnit\Framework\TestCase;

class StepupGatewayLoaMappingTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_should_be_successful_populated()
    {
        $mapping = [
            1 => [
                'engineblock' => 'ebLoa1',
                'gateway' => 'gatewayLoa1',
            ],
            2 => [
                'engineblock' => 'ebLoa2',
                'gateway' => 'gatewayLoa2',
            ],
        ];

        $ebLoa1 = Loa::create(1, 'ebLoa1');
        $ebLoa2 = Loa::create(2, 'ebLoa2');

        $loaRepository = m::mock(LoaRepository::class);
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa1')
            ->andReturn(Loa::create(1, 'gatewayLoa1'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa2')
            ->andReturn(Loa::create(2, 'gatewayLoa2'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa1')
            ->andReturn($ebLoa1);

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa2')
            ->andReturn($ebLoa2);

        $stepupLoaMapping = new StepupGatewayLoaMapping($mapping, 'gatewayLoa1', $loaRepository);

        $this->assertSame('gatewayLoa1', $stepupLoaMapping->transformToGatewayLoa($ebLoa1)->getIdentifier());
        $this->assertSame('gatewayLoa2', $stepupLoaMapping->transformToGatewayLoa($ebLoa2)->getIdentifier());
        $this->assertSame('gatewayLoa1', $stepupLoaMapping->getGatewayLoa1()->getIdentifier());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find the EngineBlock LoA in the configured stepup LoA mapping');

        $stepupLoaMapping->transformToGatewayLoa(Loa::create(2, 'loa2'));

    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_should_successful_map_back()
    {
        $mapping = [
            1 => [
                'engineblock' => 'ebLoa1',
                'gateway' => 'gatewayLoa1',
            ],
            2 => [
                'engineblock' => 'ebLoa2',
                'gateway' => 'gatewayLoa2',
            ],
            3 => [
                'engineblock' => 'ebLoa3',
                'gateway' => 'gatewayLoa3',
            ],
        ];

        $gwLoa2 = Loa::create(2, 'gatewayLoa2');
        $gwLoa3 = Loa::create(3, 'gatewayLoa3');

        $loaRepository = m::mock(LoaRepository::class);
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa1')
            ->andReturn(Loa::create(1, 'gatewayLoa1'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa2')
            ->andReturn($gwLoa2);

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa3')
            ->andReturn($gwLoa3);

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa1')
            ->andReturn(Loa::create(1, 'ebLoa1'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa2')
            ->andReturn(Loa::create(2, 'ebLoa2'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa3')
            ->andReturn(Loa::create(3, 'ebLoa3'));

        $stepupLoaMapping = new StepupGatewayLoaMapping($mapping, 'gatewayLoa1', $loaRepository);

        $this->assertSame('ebLoa2', $stepupLoaMapping->transformToEbLoa($gwLoa2)->getIdentifier());
        $this->assertSame('ebLoa3', $stepupLoaMapping->transformToEbLoa($gwLoa3)->getIdentifier());
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_should_return_an_exception_when_unable_to_map_back()
    {
        $mapping = [
            3 => [
                'engineblock' => 'ebLoa3',
                'gateway' => 'gatewayLoa3',
            ],
        ];

        $loaRepository = m::mock(LoaRepository::class);

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa1')
            ->andReturn(Loa::create(1, 'gatewayLoa1'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa3')
            ->andReturn(Loa::create(3, 'gatewayLoa3'));

        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa3')
            ->andReturn(Loa::create(3, 'ebLoa3'));

        $stepupLoaMapping = new StepupGatewayLoaMapping($mapping, 'gatewayLoa1', $loaRepository);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find the received stepup LoA in the configured EngineBlock LoA');

        $stepupLoaMapping->transformToEbLoa(Loa::create(2, 'loa2'));
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_mapping_must_be_valid_no_duplicates_allowed()
    {
        $mapping = [
            1 => [
                'engineblock' => 'ebLoa1',
                'gateway' => 'gatewayLoa1',
            ],
            2 => [
                'engineblock' => 'ebLoa1',
                'gateway' => 'gatewayLoa2',
            ]
        ];

        $loaRepository = m::mock(LoaRepository::class);
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa1')
            ->andReturn(Loa::create(1, 'gatewayLoa1'));
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa2')
            ->andReturn(Loa::create(2, 'gatewayLoa2'));
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa1')
            ->andReturn(Loa::create(1, 'ebLoa1'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Found a duplicate EngineBlock LoA identifier, this is not allowed.');
        new StepupGatewayLoaMapping($mapping, 'gatewayLoa1', $loaRepository);
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_mapping_must_be_valid_no_duplicates_in_gw_config_allowed()
    {
        $mapping = [
            1 => [
                'engineblock' => 'ebLoa1',
                'gateway' => 'gatewayLoa2',
            ],
            2 => [
                'engineblock' => 'ebLoa2',
                'gateway' => 'gatewayLoa2',
            ]
        ];

        $loaRepository = m::mock(LoaRepository::class);
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa1')
            ->andReturn(Loa::create(1, 'gatewayLoa1'));
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('gatewayLoa2')
            ->andReturn(Loa::create(2, 'gatewayLoa2'));
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa1')
            ->andReturn(Loa::create(1, 'ebLoa1'));
        $loaRepository
            ->shouldReceive('getByIdentifier')
            ->with('ebLoa2')
            ->andReturn(Loa::create(2, 'ebLoa2'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Found a duplicate Gateway LoA identifier, this is not allowed.');
        new StepupGatewayLoaMapping($mapping, 'gatewayLoa1', $loaRepository);
    }
}
