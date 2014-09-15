<?php

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
        $configReader = new EngineBlock_Group_Provider_ProviderConfig();
        $config = $configReader->createFromDatabaseFor();
        return self::createFromConfigs($config, $userId);
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
                EngineBlock_ApplicationSingleton::getLog()->err(
                    "Group Provider '$providerConfigKey' mentioned, but no config found."
                );
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
            try {
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
            catch (Exception $e) {
                self::_logErrorMessage($providerConfig->id, $e);
            }
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

    public function getGroups($serviceProviderGroupAcls)
    {
        $groups = array();
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                $acl = $serviceProviderGroupAcls[$provider->getId()];
                if ($acl && $acl['allow_groups']) {
                    $providerGroups = $provider->getGroups($serviceProviderGroupAcls);
                    $groups = array_merge($groups, $providerGroups);
                }
            }
            catch (Exception $e) {
                self::_logErrorMessage($provider->getId(), $e);
            }
        }
        return $groups;
    }

    public function getGroupsByStem($stem, $serviceProviderGroupAcls)
    {
        $groups = array();
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                $acl = $serviceProviderGroupAcls[$provider->getId()];
                if ($acl && $acl['allow_groups']) {
                    $providerGroups = $provider->getGroupsByStem($stem, $serviceProviderGroupAcls);
                    $groups = array_merge($groups, $providerGroups);
                }
            }
            catch (Exception $e) {
                self::_logErrorMessage($provider->getId(), $e);
            }
        }
        return $groups;
    }

    public function getMembers($groupIdentifier, $serviceProviderGroupAcls)
    {
        $members = array();
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                $acl = $serviceProviderGroupAcls[$provider->getId()];
                if ($acl && $acl['allow_members']) {
                    $providerMembers = $provider->getMembers($groupIdentifier, $serviceProviderGroupAcls);
                    $members = array_merge($members, $providerMembers);
                }
            }
            catch (Exception $e) {
                self::_logErrorMessage($provider->getId(), $e);
            }
        }
        return $members;
    }

    public function isMember($groupIdentifier)
    {
        // Loop through all providers
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                // And when we find a provider that knows the groupIdentifier and has the current user
                // as a member of this group, we return true
                if ($provider->isMember($groupIdentifier)) {
                    return true;
                }
            }
            catch (Exception $e) {
                self::_logErrorMessage($provider->getId(), $e);
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
                self::_logErrorMessage($provider->getId(), $e);
            }
        }
        return false;
    }

    /**
     * Get the details of a groupMember
     * @param $userId the unique groupMember identifier
     * @return the Person
     */
    public function getGroupMemberDetails($subjectId = null)
    {
        // Loop through all providers
        foreach ($this->_providers as $provider) {
            /**
             * @var EngineBlock_Group_Provider_Interface $provider
             */
            try {
                // And when we find a provider that is able to retrieve the groupDetails
                // we use this one
                if ($provider->isGroupProviderForUser()) {
                    return $provider->getGroupMemberDetails($subjectId);
                }
            }
            catch (Exception $e) {
                self::_logErrorMessage($provider->getId(), $e);
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
                $this->_logErrorMessage($provider, $e);
            }
        }
        return false;
    }

    protected static function _logErrorMessage($providerId, Exception $e)
    {
        $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()->setDetails($e->getTraceAsString());
        EngineBlock_ApplicationSingleton::getLog()->err(
            "Unable to use provider $providerId, received Exception: " . $e->getMessage(),
            $additionalInfo
        );
        EngineBlock_ApplicationSingleton::getLog()->info($e->getTraceAsString());
    }
}
