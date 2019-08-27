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
use OpenConext\EngineBlock\Request\RequestId;
use PHPUnit_Framework_TestCase as TestCase;

class RequestIdProcessorTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Request
     * @group Logger
     */
    public function request_id_is_added_to_the_record()
    {
        $requestIdValue = 'some_request_id';

        $requestIdGenerator = m::mock('OpenConext\EngineBlock\Request\RequestIdGenerator');
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
