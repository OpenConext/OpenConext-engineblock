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

/**
 *
 */ 
class EngineBlock_Group_Provider_Aggregator extends EngineBlock_Group_Provider_Abstract
{
    protected $_providers = array();

    public static function createFromConfigFor($userId)
    {
        $config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        $providers = $config->groupProviders;
        $providerConfigs = array();
        foreach ($providers as $provider) {
            if (!isset($config->$provider)) {
                throw new EngineBlock_Exception("Group Provider '$provider' mentioned, but no config found.");
            }
            $providerConfigs[] = $config->$provider;
        }
        $providerConfigs = new Zend_Config($providerConfigs);
        return self::createFromConfigs($providerConfigs, $userId);
    }

    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $providers = array();
        foreach ($config as $providerConfig) {
            $className = $providerConfig->className;

            if (!$className) {
                throw new EngineBlock_Exception("No classname specified in provider config");
            }
            if (!class_exists($className, true)) {
                throw new EngineBlock_Exception("Classname from Provider config '$className' does not exist!");
            }
            $provider = $className::createFromConfigs($providerConfig, $userId);

            if (!$provider->validatePreconditions()) {
                continue;
            }

            $providers[] = $provider;
        }
        return new self($providers);
    }

    public function __construct(array $providers)
    {
        $this->_providers = $providers;
    }

    public function setGroupStem($stemIdentifier)
    {
        parent::setGroupStem($stemIdentifier);

        foreach ($this->_providers as $provider) {
            $provider->setGroupStem($stemIdentifier);
        }

        return $this;
    }

    public function getGroups()
    {
        $groups = array();
        foreach ($this->_providers as $provider) {
            $groups = array_merge($groups, $provider->getGroups());
        }
        return $groups;
    }

    public function getMembers($groupIdentifier)
    {
        $members = array();
        foreach ($this->_providers as $provider) {
            $members = array_merge($members, $provider->getMembers($groupIdentifier));
        }
    }

    public function isMember($groupIdentifier)
    {
        foreach ($this->_providers as $provider) {
            if ($provider->isMember($groupIdentifier)) {
                return true;
            }
        }
        return false;
    }
}
