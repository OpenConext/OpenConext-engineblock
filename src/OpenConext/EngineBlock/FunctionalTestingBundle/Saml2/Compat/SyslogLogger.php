<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Saml2\Compat;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class SyslogLogger
 * @package OpenConext\EngineBlock\FunctionalTestingBundle\Saml2\Compat
 */
class SyslogLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $level = $this->logLevelToSyslogLevel($level);
        syslog($level, $message . empty($context) ? '' : ' ' . json_encode($context));
    }

    /**
     * @param $level
     * @return int
     */
    protected function logLevelToSyslogLevel($level)
    {
        switch ($level) {
            case LogLevel::ALERT:
                return LOG_ALERT;
            case LogLevel::CRITICAL:
                return LOG_CRIT;
            case LogLevel::DEBUG:
                return LOG_DEBUG;
            case LogLevel::EMERGENCY:
                return LOG_EMERG;
            case LogLevel::ERROR:
                return LOG_ERR;
            case LogLevel::INFO:
                return LOG_INFO;
            case LogLevel::NOTICE:
                return LOG_NOTICE;
        }
        return LOG_ERR;
    }
}
