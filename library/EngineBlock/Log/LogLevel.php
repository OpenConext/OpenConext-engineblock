<?php

use Psr\Log\LogLevel as PsrLogLevel;

final class EngineBlock_Log_LogLevel
{
    /**
     * Returns whether the given level is a valid PSR-3 level.
     *
     * @param mixed $level Eg. "debug"
     * @return bool
     */
    public static function isValid($level)
    {
        return in_array(
            $level,
            array(
                PsrLogLevel::DEBUG,
                PsrLogLevel::INFO,
                PsrLogLevel::NOTICE,
                PsrLogLevel::WARNING,
                PsrLogLevel::ERROR,
                PsrLogLevel::CRITICAL,
                PsrLogLevel::ALERT,
                PsrLogLevel::EMERGENCY,
            ),
            true
        );
    }
}
