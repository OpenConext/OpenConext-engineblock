<?php

namespace OpenConext\EngineBlock\Logger\Formatter;

use EngineBlock_Exception;
use Monolog\Formatter\FormatterInterface;
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

    public function format(array $record)
    {
        return $this->formatter->format($this->addAdditionalInfoForEngineBlockExceptions($record));
    }

    public function formatBatch(array $records)
    {
        foreach ($records as &$value) {
            $value = $this->addAdditionalInfoForEngineBlockExceptions($value);
        };

        return $this->formatter->formatBatch($records);
    }

    /**
     * @param array $record
     * @return array
     */
    private function addAdditionalInfoForEngineBlockExceptions(array $record)
    {
        if (!isset($record['context']['exception'])) {
            return $record;
        }

        $exception = $record['context']['exception'];
        if (!$exception instanceof EngineBlock_Exception) {
            return $record;
        }

        $record['context']['exception'] = AdditionalInfo::createFromException($exception)->toArray();

        return $record;
    }
}
