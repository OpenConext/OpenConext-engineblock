<?php

namespace OpenConext\EngineBlockBundle\Monolog\Formatter;

use Monolog\Formatter\JsonFormatter;

class SyslogJsonFormatter extends JsonFormatter
{
    public function format(array $record)
    {
        return parent::format($this->mapRecord($record));
    }

    public function formatBatch(array $records)
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
