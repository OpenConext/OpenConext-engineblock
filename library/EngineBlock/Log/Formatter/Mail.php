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

class EngineBlock_Log_Formatter_Mail
{
    const REPLACE_WITH = '**SECRET**';

    protected $_filterValues = array();
    protected $_applicationConfiguration;

    public function __construct($filterValues)
    {
        $this->_filterValues = isset($filterValues) ? $filterValues : array();
        $this->_applicationConfiguration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
    }

    public function format($event)
    {
        foreach ($this->_filterValues as $configName) {
            $configValue = $this->_getConfigValue($this->_applicationConfiguration, $configName);
            if (is_null($configValue)) {
                continue;
            }

            if (is_array($configValue)) {
                foreach ($configValue as $configValueElement) {
                    $event['message'] = str_replace($configValueElement, self::REPLACE_WITH, $event['message']);
                    if ($event['details']) {
                        $event['details'] = str_replace($configValueElement, self::REPLACE_WITH, $event['details']);
                    }
                }
            }
            else {
                $event['message'] = str_replace($configValue, self::REPLACE_WITH, $event['message']);
                if ($event['details']) {
                    $event['details'] = str_replace($configValue, self::REPLACE_WITH, $event['details']);
                }
            }
        }

        $view = new Zend_View();
        foreach ($event as $key => $val) {
            $view->__set($key, $val);
        }

        return $view;
    }

    protected function _getConfigValue($config, $configName)
    {
        $firstDot = strpos($configName, '.');
        if ($firstDot) {
            $firstPart = substr($configName, 0, $firstDot);
            if (isset($config->$firstPart)) {
                return $this->_getConfigValue($config->$firstPart, substr($configName, $firstDot + 1));
            }
        }
        else {
            if (isset($config->$configName)) {
                if (is_object($config->$configName)) {
                    return $config->$configName->toArray();
                }
                else {
                    return $config->$configName;
                }
            }
        }
        return null;
    }
}