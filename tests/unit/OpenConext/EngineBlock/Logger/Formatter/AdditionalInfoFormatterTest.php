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

namespace OpenConext\EngineBlock\Logger\Formatter;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use EngineBlock_Exception;
use Exception;
use PHPUnit\Framework\TestCase;

class AdditionalInfoFormatterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function additional_info_is_added_for_an_engineblock_exception()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_EMERGENCY);

        $formatter = new AdditionalInfoFormatter(new PassthruFormatter());
        $formatted = $formatter->format(['context' => ['exception' => $exception]]);

        $this->assertTrue(
            is_array($formatted['context']['exception']),
            'EngineBlock Exception representation should be converted to array'
        );
        $this->assertEquals(
            'EMERG',
            $formatted['context']['exception']['severity'],
            'Engineblock Exception code should be mapped.'
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function additional_info_is_added_for_engineblock_exception_when_batch_formatting()
    {
        $exception = new EngineBlock_Exception('message');

        $formatter = new AdditionalInfoFormatter(new PassthruFormatter());
        $formatted = $formatter->formatBatch([['context' => ['exception' => $exception]]]);

        $this->assertTrue(
            is_array($formatted[0]['context']['exception']),
            'EngineBlock Exception representation should be converted to array'
        );
        $this->assertEquals(
            'ERROR',
            $formatted[0]['context']['exception']['severity'],
            'Engineblock Exception code should be mapped.'
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function additional_info_is_not_added_for_non_engineblock_exceptions()
    {
        $exception = new Exception('message');

        $formatter = new AdditionalInfoFormatter(new PassthruFormatter());
        $formatted = $formatter->format(['context' => ['exception' => $exception]]);

        $this->assertEquals($exception, $formatted['context']['exception']);
    }
}
