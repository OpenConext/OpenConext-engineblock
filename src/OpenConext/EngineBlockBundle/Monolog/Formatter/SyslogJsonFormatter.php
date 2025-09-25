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

namespace OpenConext\EngineBlockBundle\Monolog\Formatter;

use Monolog\Formatter\JsonFormatter;

class SyslogJsonFormatter extends JsonFormatter
{
    public function format(array $record): string
    {
        return parent::format($this->mapRecord($record));
    }

    public function formatBatch(array $records): string
    {
        return parent::formatBatch(
            array_map(
                function (array $record) {
                    return $this->mapRecord($record);
                },
                $records
            )
        );
    }

    private function mapRecord(array $record)
    {
        return [
            'channel' => $record['channel'],
            'level'   => $record['level_name'],
            'message' => $record['message'],
            'context' => $record['context'],
            'extra'   => $record['extra'],
        ];
    }
}
