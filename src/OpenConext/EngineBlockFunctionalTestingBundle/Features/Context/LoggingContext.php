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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
use OpenConext\EngineBlockFunctionalTestingBundle\Log\TestLogHandler;
use PHPUnit\Framework\Assert;

/**
 * Behat context for asserting on structured log output.
 *
 * Injects the in-memory TestLogHandler so scenarios can verify that
 * log records carry the expected structured fields (e.g. correlation_id).
 *
 * Does not extend AbstractSubContext because it performs no browser interactions
 * and has no need for MinkContext.
 */
class LoggingContext implements Context
{
    public function __construct(private readonly TestLogHandler $logHandler)
    {
    }

    /**
     * @BeforeScenario
     */
    public function resetLogHandler(): void
    {
        $this->logHandler->reset();
    }

    /**
     * @Then each log record should contain a :field field
     */
    public function eachLogRecordShouldContainField(string $field): void
    {
        $records = $this->logHandler->getRecords();

        Assert::assertNotEmpty($records, 'No log records were captured during this scenario.');

        foreach ($records as $index => $record) {
            Assert::assertArrayHasKey(
                $field,
                $record->extra,
                sprintf(
                    'Log record #%d (channel=%s, message="%s") is missing extra field "%s".',
                    $index,
                    $record->channel,
                    $record->message,
                    $field,
                ),
            );

            Assert::assertNotNull(
                $record->extra[$field],
                sprintf(
                    'Log record #%d (channel=%s, message="%s") has a null value for extra field "%s".',
                    $index,
                    $record->channel,
                    $record->message,
                    $field,
                ),
            );
        }
    }
}
