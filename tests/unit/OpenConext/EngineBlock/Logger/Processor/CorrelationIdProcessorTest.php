<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use OpenConext\EngineBlock\Request\CurrentCorrelationId;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CorrelationIdProcessorTest extends TestCase
{
    #[Group('EngineBlock')]
    #[Group('Logger')]
    #[Test]
    public function correlation_id_is_added_to_the_record(): void
    {
        $correlationId = new CurrentCorrelationId();
        $correlationId->correlationId = 'test-correlation-id';

        $processor = new CorrelationIdProcessor($correlationId);
        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Debug,
            message: 'test message',
            context: [],
            extra: [],
        );

        $processedRecord = ($processor)($record);

        $this->assertSame('test-correlation-id', $processedRecord->extra['correlation_id']);
    }

    #[Group('EngineBlock')]
    #[Group('Logger')]
    #[Test]
    public function correlation_id_is_null_when_not_set(): void
    {
        $correlationId = new CurrentCorrelationId();

        $processor = new CorrelationIdProcessor($correlationId);
        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Debug,
            message: 'test message',
            context: [],
            extra: [],
        );

        $processedRecord = ($processor)($record);

        $this->assertNull($processedRecord->extra['correlation_id']);
    }
}
