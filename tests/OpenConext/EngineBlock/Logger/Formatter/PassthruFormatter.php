<?php

namespace OpenConext\EngineBlock\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;

class PassthruFormatter implements FormatterInterface
{
    public function format(array $record)
    {
        return $record;
    }

    public function formatBatch(array $records)
    {
        return $records;
    }
}
