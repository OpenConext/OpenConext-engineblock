<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Memcache_SettingsException extends EngineBlock_Exception
{
    public function __construct($message, $severity = self::CODE_ALERT, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}

class EngineBlock_Memcache_ConnectionFactory {
    const DEFAULT_PORT                      = 11211;
    const DEFAULT_PERSISTENCE               = true;
    const DEFAULT_WEIGHT                    = 1;
    const DEFAULT_TIMEOUT_IN_SECONDS        = 1;
    const DEFAULT_RETRY_INTERVAL_IN_SECONDS = 15;

    protected $_memcache;

    public function create()
    {
        if (isset($this->_memcache)) {
            return $this->_memcache;
        }

        $configuration = $this->_getConfiguration();
        if (!isset($configuration->memcache)) {
            throw new EngineBlock_Memcache_SettingsException("No memcache settings");
        }
        if (!isset($configuration->memcache->servers)) {
            throw new EngineBlock_Memcache_SettingsException("No memcache servers defined");
        }
        $memcache = $this->_getMemcacheClient();
        foreach ($configuration->memcache->servers as $serverName) {
            if (!isset($configuration->memcache->$serverName)) {
                $serverConfiguration = new stdClass();
            }
            else {
                $serverConfiguration = $configuration->memcache->$serverName;
            }
            $serverArguments = $this->_getServerArgumentsForConfiguration($serverName, $serverConfiguration);
            call_user_func_array(array($memcache, 'addServer'), $serverArguments);
        }
        $this->_memcache = $memcache;
        return $this->_memcache;
    }

    /**
     *
     * Memcache::addServer (
     *      string $host
     *          [, int $port = 11211
     *              [, bool $persistent
     *                  [, int $weight
     *                      [, int $timeout
     *                          [, int $retry_interval
     * )
     *
     * @param string $serverName
     * @param stdClass $serverConfiguration
     * @return string[]
     */
    protected function _getServerArgumentsForConfiguration($serverName, stdClass $serverConfiguration)
    {
        $arguments = array();
        // HOST
        if (isset($serverConfiguration->host)) {
            $arguments[] = $serverConfiguration->host;
        }
        else {
            $arguments[] = $serverName;
        }
        // PORT
        if (isset($serverConfiguration->port)) {
            $arguments[] = $serverConfiguration->port;
        }
        else {
            $arguments[] = self::DEFAULT_PORT;
        }
        // PERSISTENCE
        if (isset($serverConfiguration->persistent)) {
            $arguments[] = $serverConfiguration->persistent;
        }
        else {
            $arguments[] = self::DEFAULT_PERSISTENCE;
        }
        // WEIGHT
        if (isset($serverConfiguration->weight)) {
            $arguments[] = $serverConfiguration->weight;
        }
        else {
            $arguments[] = self::DEFAULT_WEIGHT;
        }
        // TIMEOUT
        if (isset($serverConfiguration->timeout)) {
            $arguments[] = $serverConfiguration->timeout;
        }
        else {
            $arguments[] = self::DEFAULT_TIMEOUT_IN_SECONDS;
        }
        // RETRY INTERVAL
        if (isset($serverConfiguration->retry_interval)) {
            $arguments[] = $serverConfiguration->retry_interval;
        }
        else {
            $arguments[] = self::DEFAULT_RETRY_INTERVAL_IN_SECONDS;
        }
        return $arguments;
    }

    protected function _getConfiguration()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
    }

    protected function _getMemcacheClient()
    {
        if (!extension_loaded('memcache')) {
            throw new EngineBlock_Memcache_SettingsException("Memcache extension not installed");
        }

        return new Memcache();
    }
}
