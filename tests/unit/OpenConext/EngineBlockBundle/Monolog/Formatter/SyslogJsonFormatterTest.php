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

declare(strict_types=1);

namespace OpenConext\EngineBlockBundle\Monolog\Formatter;

use DateTimeImmutable;
use EngineBlock_Exception;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SyslogJsonFormatterTest extends TestCase
{
    private SyslogJsonFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new SyslogJsonFormatter();
    }

    #[Test]
    public function it_normalizes_an_exception_in_the_context_to_its_class_message_code_and_file(): void
    {
        $exception = new EngineBlock_Exception('Something went wrong');

        $record = $this->recordWithContext(['exception' => $exception]);

        $decoded = json_decode($this->formatter->format($record), true);

        self::assertSame(EngineBlock_Exception::class, $decoded['context']['exception']['class']);
        self::assertSame('Something went wrong', $decoded['context']['exception']['message']);
        self::assertArrayHasKey('code', $decoded['context']['exception']);
        self::assertArrayHasKey('file', $decoded['context']['exception']);
        self::assertArrayNotHasKey('sessionId', $decoded['context']['exception']);
        self::assertArrayNotHasKey('trace', $decoded['context']['exception']);
    }

    #[Test]
    public function it_normalizes_previous_exceptions_to_their_string_representation(): void
    {
        $exception = new RuntimeException('Outer', 0, new RuntimeException('Inner'));

        $record = $this->recordWithContext(['previous_exceptions' => [(string) $exception->getPrevious()]]);

        $decoded = json_decode($this->formatter->format($record), true);

        self::assertStringContainsString('Inner', $decoded['context']['previous_exceptions'][0]);
    }

    #[Test]
    public function it_leaves_scalar_and_array_context_values_unchanged(): void
    {
        $record = $this->recordWithContext(['route' => 'api_connections', 'route_parameters' => ['_format' => 'json']]);

        $decoded = json_decode($this->formatter->format($record), true);

        self::assertSame('api_connections', $decoded['context']['route']);
        self::assertSame(['_format' => 'json'], $decoded['context']['route_parameters']);
    }

    private function recordWithContext(array $context): LogRecord
    {
        return new LogRecord(
            new DateTimeImmutable(),
            'app',
            Level::Error,
            'An error was caught',
            $context,
        );
    }
}
