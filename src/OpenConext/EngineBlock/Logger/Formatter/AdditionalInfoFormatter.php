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

use EngineBlock_Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use OpenConext\EngineBlock\Logger\Message\AdditionalInfo;

final class AdditionalInfoFormatter implements FormatterInterface
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function format(LogRecord $record): mixed
    {
        return $this->formatter->format($this->addAdditionalInfoForEngineBlockExceptions($record));
    }

    public function formatBatch(array $records): mixed
    {
        foreach ($records as &$value) {
            $value = $this->addAdditionalInfoForEngineBlockExceptions($value);
        };

        return $this->formatter->formatBatch($records);
    }

    /**
     * @param LogRecord $record
     * @return LogRecord
     */
    private function addAdditionalInfoForEngineBlockExceptions(LogRecord $record): LogRecord
    {
        if (!isset($record['context']['exception'])) {
            return $record;
        }

        $exception = $record['context']['exception'];
        if (!$exception instanceof EngineBlock_Exception) {
            return $record;
        }

        $context = $record->context;
        $context['exception'] = AdditionalInfo::createFromException($exception)->toArray();

        return $record->with(context: $context);
    }
}
