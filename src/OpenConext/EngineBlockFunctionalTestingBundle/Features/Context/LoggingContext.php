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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use RuntimeException;

class LoggingContext implements Context
{
    public function __construct(private readonly string $logFile)
    {
    }

    /**
     * @BeforeScenario
     */
    public function resetLogHandler(): void
    {
        file_put_contents($this->logFile, '');
    }

    /**
     * @Then the following log messages should have a correlation_id:
     */
    public function theFollowingLogMessagesShouldHaveACorrelationId(TableNode $table): void
    {
        $records = $this->readRecords();
        $allCorrelationIds = [];

        foreach ($table->getColumnsHash() as $row) {
            $pattern = $row['message'];
            $isRegex = preg_match('/^\/.*\/$/', $pattern) === 1;

            $matched = array_filter(
                $records,
                static fn(array $r) => $isRegex
                    ? preg_match($pattern, $r['message'] ?? '') === 1
                    : ($r['message'] ?? '') === $pattern,
            );

            if (empty($matched)) {
                throw new RuntimeException(sprintf(
                    'No log record matched message %s "%s".',
                    $isRegex ? 'pattern' : 'string',
                    $pattern,
                ));
            }

            foreach ($matched as $record) {
                $correlationId = $record['extra']['correlation_id'] ?? null;

                if ($correlationId === null) {
                    throw new RuntimeException(sprintf(
                        'Log record matching "%s" (channel=%s) has a null correlation_id.',
                        $pattern,
                        $record['channel'] ?? '?',
                    ));
                }

                $allCorrelationIds[] = $correlationId;
            }
        }

        $distinct = array_unique($allCorrelationIds);

        if (count($distinct) > 1) {
            throw new RuntimeException(sprintf(
                'Expected a single correlation_id across all matched log records, but found %d distinct values: %s.',
                count($distinct),
                implode(', ', $distinct),
            ));
        }
    }

    /**
     * @Then I dump the log records
     */
    public function iDumpTheLogRecords(): void
    {
        $records = $this->readRecords();
        $rows = [];
        foreach ($records as $record) {
            $message = $record['message'] ?? '';
            if (mb_strlen($message) > 100) {
                $message = mb_substr($message, 0, 97) . '...';
            }
            $rows[] = sprintf(
                '| %-12s | %-9s | %-100s | %s |',
                $record['channel'] ?? '',
                $record['level_name'] ?? '',
                str_replace('|', '\\|', $message),
                $record['extra']['correlation_id'] ?? 'null',
            );
        }
        echo "\n" . implode("\n", $rows) . "\n";
    }

    /**
     * Reads all records from the log file, decodes each JSON line, and returns only
     * records not belonging to the event channel (Symfony kernel internals).
     *
     * @return array<int, array<string, mixed>>
     */
    private function readRecords(): array
    {
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return [];
        }

        $records = [];

        foreach ($lines as $index => $line) {
            $record = json_decode($line, true);

            if (!is_array($record)) {
                throw new RuntimeException(sprintf('Log record #%d could not be decoded as JSON.', $index));
            }

            if (($record['channel'] ?? '') === 'event') {
                continue;
            }

            $records[] = $record;
        }

        return $records;
    }
}
