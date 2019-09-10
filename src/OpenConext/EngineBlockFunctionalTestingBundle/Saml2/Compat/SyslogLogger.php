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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class SyslogLogger
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat
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
    public function log($level, $message, array $context = [])
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
