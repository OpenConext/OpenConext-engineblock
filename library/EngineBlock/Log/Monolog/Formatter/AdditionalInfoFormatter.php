<?php

use Monolog\Formatter\FormatterInterface;

/**
 * This formatter add additional information to the log record context based on the exception, if present. It then
 * passes the log record on to the decorated formatter.
 */
final class EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter implements FormatterInterface
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

    public function formatBatch(array $records)
    {
        foreach ($records as $key => $value) {
            $records[$key] = $this->addAdditionalInfo($value);
        }

        return $this->formatter->formatBatch($records);
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
                EngineBlock_Log_Message_AdditionalInfo::createFromException($record['context']['exception'])->toArray();
        }

        return $record;
    }
}
