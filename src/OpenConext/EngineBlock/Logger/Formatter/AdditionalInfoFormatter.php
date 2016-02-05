<?php

namespace OpenConext\EngineBlock\Logger\Formatter;

use EngineBlock_Exception;
use Monolog\Formatter\FormatterInterface;
use OpenConext\EngineBlock\Logger\Message\AdditionalInfo;

/**
 * This formatter add additional information to the log record context based on the exception, if present. It then
 * passes the log record on to the decorated formatter.
 */
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

    public function format(array $record)
    {
        return $this->formatter->format($this->addAdditionalInfo($record));
    }

    /**
     * @param array $record
     * @return array
     */
    private function addAdditionalInfo(array $record)
    {
        $hasEngineBlockException =
            isset($record['context']['exception']) && $record['context']['exception'] instanceof EngineBlock_Exception;

        if ($hasEngineBlockException) {
            $record['context']['exception'] =
                AdditionalInfo::createFromException($record['context']['exception'])->toArray();
        }

        return $record;
    }

    public function formatBatch(array $records)
    {
        foreach ($records as $key => $value) {
            $records[$key] = $this->addAdditionalInfo($value);
        }

        return $this->formatter->formatBatch($records);
    }
}
