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
 * Aggregate groups from multiple providers
 */ 
class EngineBlock_Group_Provider_Aggregator extends EngineBlock_Group_Provider_Abstract
{
    /**
     * All known and usable group providers
     *
     * @var array
     */
    protected $_providers = array();

    /**
     * Known but unusable group providers (based on preconditions)
     * 
     * @var array
     */
    protected $_invalidProviders = array();

    /**
     * Create an aggregate of Group Providers from database configuration
     *
     * @static
     * @param $userId
     * @return void
     */
    public static function createFromDatabaseFor($userId)
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        $db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
        $groupProviderRows = $db->query(
            'SELECT *
            FROM group_provider'
        )->fetchAll(PDO::FETCH_ASSOC);
        $groupProviders = array();
        foreach ($groupProviderRows as $groupProviderRow) {
            $groupProvider = array(
                'id'        => $groupProviderRow['identifier'],
                'name'      => $groupProviderRow['name'],
                'className' => $groupProviderRow['classname'],
            );

            // Retrieve options
            $optionRows = $db->query(
                "SELECT `name`, `value`
                FROM group_provider_option
                WHERE group_provider_id = {$groupProviderRow['id']}"
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($optionRows as $optionRow) {
                $groupProviderOptionPointer = &$groupProvider;
                $optionNameParts = explode('.', $optionRow['name']);
                $lastOptionNamePart = null;
                while ($optionNamePart = array_shift($optionNameParts)) {
                    if (!isset($groupProviderOptionPointer[$optionNamePart]) && !empty($optionNameParts)) {
                        $groupProviderOptionPointer[$optionNamePart] = array();
                    }
                    $groupProviderOptionPointer = &$groupProviderOptionPointer[$optionNamePart];
                }
                $groupProviderOptionPointer = $optionRow['value'];
            }

            // decorators
            $decoratorAndOptionsRows = $db->query(
                "SELECT gpd.id        AS id,
                        gpd.classname AS className,
                        gpdo.name     AS option_name,
                        gpdo.value    AS option_value
                FROM group_provider_decorator gpd
                LEFT JOIN group_provider_decorator_option gpdo ON gpd.id = gpdo.group_provider_decorator_id
                WHERE gpd.group_provider_id = {$groupProviderRow['id']}"
            );
            if (!empty($decoratorAndOptionsRows)) {
                $groupProvider['decorators'] = array();
                foreach ($decoratorAndOptionsRows as $decoratorOptionsRow) {
                    if (!isset($groupProvider['decorators'][$decoratorOptionsRow['id']])) {
                        $groupProvider['decorators'][$decoratorOptionsRow['id']] = array();
                    }
                    $groupProvider['decorators'][$decoratorOptionsRow['id']]['className'] = $decoratorOptionsRow['className'];
                    if (isset($decoratorOptionsRow['option_name']) && $decoratorOptionsRow['option_name']) {
                        $groupProvider['decorators'][$decoratorOptionsRow['id']][$decoratorOptionsRow['option_name']] = $decoratorOptionsRow['option_value'];
                    }
                }
            }

            // filters
            $filterAndOptionsRows = $db->query(
                "SELECT gpf.id        AS id,
                        gpf.type      AS type,
                        gpf.classname AS className,
                        gpfo.name     AS option_name,
                        gpfo.value    AS option_value
                FROM group_provider_filter gpf
                LEFT JOIN group_provider_filter_option gpfo ON gpf.id = gpfo.group_provider_filter_id
                WHERE gpf.group_provider_id = {$groupProviderRow['id']}"
            );
            foreach ($filterAndOptionsRows as $filterOptionsRow) {
                if (!isset($groupProvider[$filterOptionsRow['type'] . 'Filters'])) {
                    $groupProvider[$filterOptionsRow['type'] . 'Filters'] = array();
                }
                $filters = &$groupProvider[$filterOptionsRow['type'] . 'Filters'];
                if (!isset($filters[$filterOptionsRow['id']])) {
                    $filters[$filterOptionsRow['id']] = array();
                }
                $filters[$filterOptionsRow['id']]['className'] = $filterOptionsRow['className'];
                if (isset($filterOptionsRow['option_name']) && $filterOptionsRow['option_name']) {
                    $filters[$filterOptionsRow['id']][$filterOptionsRow['option_name']] = $filterOptionsRow['option_value'];
                }
            }

            // preconditions
            $preconditionAndOptionsRows = $db->query(
                "SELECT gpp.id        AS id,
                        gpp.classname AS className,
                        gppo.name     AS option_name,
                        gppo.value    AS option_value
                FROM group_provider_precondition gpp
                LEFT JOIN group_provider_precondition_option gppo ON gpp.id = gppo.group_provider_precondition_id
                WHERE gpp.group_provider_id = {$groupProviderRow['id']}"
            );
            if (!empty($preconditionAndOptionsRows)) {
                $groupProvider['preconditions'] = array();
                foreach ($preconditionAndOptionsRows as $preconditionOptionsRow) {
                    if (!isset($groupProvider['preconditions'][$preconditionOptionsRow['id']])) {
                        $groupProvider['preconditions'][$preconditionOptionsRow['id']] = array();
                    }
                    $groupProvider['preconditions'][$preconditionOptionsRow['id']]['className'] = $preconditionOptionsRow['className'];
                    if (isset($preconditionOptionsRow['option_name']) && $preconditionOptionsRow['option_name']) {
                        $groupProvider['preconditions'][$preconditionOptionsRow['id']][$preconditionOptionsRow['option_name']] = $preconditionOptionsRow['option_value'];
                    }
                }
            }

            $groupProviders[] = $groupProvider;
        }
        return self::createFromConfigs(new Zend_Config($groupProviders), $userId);
    }

    /**
     * Create an aggregate of group providers from the application configuration
     *
     * @static
     * @param $userId
     * @return EngineBlock_Group_Provider_Aggregator
     */
    public static function createFromConfigFor($userId)
    {
        $config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        $providers = $config->groupProviders;
        $providerConfigs = array();
        foreach ($providers as $providerConfigKey) {
            if (!isset($config->$providerConfigKey)) {
                eblog()->error("Group Provider '$providerConfigKey' mentioned, but no config found.");
                continue;
            }

            $providerConfig = $config->$providerConfigKey->toArray();
            $providerConfig['id'] = $providerConfigKey;
            $providerConfigs[] = $providerConfig;
        }
        $providerConfigs = new Zend_Config($providerConfigs);
        return static::createFromConfigs($providerConfigs, $userId);
    }

    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $providers = array();
        $invalidProviders = array();
        foreach ($config as $providerConfig) {
            $className = $providerConfig->className;

            if (!$className) {
                throw new EngineBlock_Exception("No classname specified in provider config");
            }
            if (!class_exists($className, true)) {
                throw new EngineBlock_Exception("Classname from Provider config '$className' does not exist or cannot be autoloaded!");
            }
            $provider = $className::createFromConfigs($providerConfig, $userId);

            if (!$provider->validatePreconditions()) {
                $invalidProviders[] = $provider;
                continue;
            }

            $providers[] = $provider;
        }
        $aggregator = new static($providers);
        $aggregator->_invalidProviders = $invalidProviders;
        return $aggregator;
    }

    public function __construct(array $providers)
    {
        $this->_providers = $providers;
    }

    public function getDisplayName()
    {
        $names = array();
        foreach ($this->_providers as $provider) {
            $names[] = $provider->getDisplayName();
        }
        return 'Multiple (' . implode(', ', $names) . ')';
    }

    public function getProviders()
    {
        return $this->_providers;
    }

    public function getInvalidProviders()
    {
        return $this->_invalidProviders;
    }

    public function setGroupStem($stemIdentifier)
    {
        parent::setGroupStem($stemIdentifier);

        // Propagate the groupStem onto all providers
        foreach ($this->_providers as $provider) {
            $provider->setGroupStem($stemIdentifier);
        }

        return $this;
    }

    public function getGroups()
    {
        $groups = array();
        foreach ($this->_providers as $provider) {
            try {
                $providerGroups = $provider->getGroups();
                $groups = array_merge($groups, $providerGroups);
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        return $groups;
    }

    public function getGroupsByStem($stem)
    {
        $groups = array();
        foreach ($this->_providers as $provider) {
            try {
                $providerGroups = $provider->getGroupsByStem($stem);
                $groups = array_merge($groups, $providerGroups);
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        return $groups;
    }

    public function getMembers($groupIdentifier)
    {
        $members = array();
        foreach ($this->_providers as $provider) {
            try {
                $providerMembers = $provider->getMembers($groupIdentifier);
                $members = array_merge($members, $providerMembers);
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        return $members;
    }

    public function isMember($groupIdentifier)
    {
        // Loop through all providers
        foreach ($this->_providers as $provider) {
            try {
                // And when we find a provider that knows the groupIdentifier and has the current user
                // as a member of this group, we return true
                if ($provider->isMember($groupIdentifier)) {
                    return true;
                }
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        // If none of the known providers knows the groupIdentifier or has the current user as a member
        // then he's not a member of this group.
        return false;
    }

    public function isMemberInStem()
    {
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                if ($provider->isMemberInStem()) {
                    return true;
                }
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        return false;
    }

    /**
     * Get the details of a groupMember
     * @param $userId the unique groupMember identifier
     * @return the Person
     */
    public function getGroupMemberDetails()
    {
        // Loop through all providers
        foreach ($this->_providers as $provider) {
            try {
                // And when we find a provider that is able to retrieve the groupDetails
                // we use this one
                if ($provider->isGroupProviderForUser()) {
                    return $provider->getGroupMemberDetails();
                }
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        // If none of the known providers can handle this userId we return null
        return NULL;
    }

     /**
     * Is this GroupProvider able to return details for the given userId based on the configured memberFilter
     * @abstract
     * @param $userId the unique Person identifier
     * @return boolean true is the userId is a partial matched with this GroupProviders urn
     */
    public function isGroupProviderForUser() {
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                if ($provider->isGroupProviderForUser()) {
                    return true;
                }
            }
            catch (Exception $e) {
                $providerId = $provider->getId();
                ebLog()->err("Unable to use provider $providerId, received Exception: " . $e->getMessage());
                ebLog()->debug($e->getTraceAsString());
            }
        }
        return false;
    }
}
