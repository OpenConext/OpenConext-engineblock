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

namespace OpenConext\EngineBlock\Logger\Processor;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Request\RequestId;
use PHPUnit\Framework\TestCase;

class RequestIdProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Request')]
    #[\PHPUnit\Framework\Attributes\Group('Logger')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function request_id_is_added_to_the_record()
    {
        $requestIdValue = 'some_request_id';

        $requestIdGenerator = m::mock(\OpenConext\EngineBlock\Request\RequestIdGenerator::class);
        $requestIdGenerator->shouldReceive('generateRequestId')
            ->once()
            ->andReturn($requestIdValue);

        $requestId = new RequestId($requestIdGenerator);

        $requestIdProcessor = new RequestIdProcessor($requestId);
        $record = ['extra' => []];

        $processedRecord = $requestIdProcessor->processRecord($record);

        $this->assertEquals($requestIdValue, $processedRecord['extra']['request_id']);
    }
}
