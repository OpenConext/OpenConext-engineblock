<?php

/**
 * Non-persistent request based in memory cache so we aren't requesting a users groups
 * over and over during a single request to EngineBlock.
 */
class EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy implements EngineBlock_Group_Provider_Interface
{
    /**
     * @var EngineBlock_Group_Provider_Aggregator
     */
    protected $_provider;

    protected $_groupCache = array();

    protected $_memberCache = array();

    protected $_isMemberCache = array();

    protected $_isMemberInStemCache;

    public static function createFromDatabaseFor($userId)
    {
        return new self(EngineBlock_Group_Provider_Aggregator::createFromDatabaseFor($userId));
    }

    public static function createFromConfigFor($userId)
    {
        return new self(EngineBlock_Group_Provider_Aggregator::createFromConfigFor($userId));
    }

    /**
     * Factory method to create and configure a group provider from it's given configuration
     *
     * @static
     * @abstract
     * @param Zend_Config $config Configuration for this provider in it's specific format
     * @param string      $userId UserId to provide group information for (required)
     * @return EngineBlock_Group_Provider_Interface
     */
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        return new self(EngineBlock_Group_Provider_Aggregator::createFromConfigs($config, $userId));
    }

    public function __construct(EngineBlock_Group_Provider_Interface $provider)
    {
        $this->_provider = $provider;
    }

    public function getId()
    {
        return $this->_provider->getId();
    }

    public function getDisplayName()
    {
        return $this->_provider->getDisplayName();
    }

    public function getProviders()
    {
        return $this->_provider->getProviders();
    }

    public function getInvalidProviders()
    {
        return $this->_provider->getInvalidProviders();
    }

    /**
     * Set the ID of the User to provide group information for
     *
     * @abstract
     * @param string $userId
     * @return EngineBlock_Group_Provider_Interface
     */
    public function setUserId($userId)
    {
        $this->_clearCache();

        $this->_provider->setUserId($userId);
        return $this;
    }

    /**
     * Get the ID of the User that group information will be provided for
     *
     * @abstract
     * @return string
     */
    public function getUserId()
    {
        return $this->_provider->getUserId();
    }

    /**
     * @param string $className
     * @param Zend_Config|null $options
     * @return EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy
     */
    public function addPrecondition($className, $options = null)
    {
        $this->_clearCache();

        $this->_provider->addPrecondition($className, $options);
        return $this;
    }

    public function getPreconditions()
    {
        return $this->_provider->getPreconditions();
    }

    /**
     * Validate that this providers meets the preconditions, if this returns false, then you SHOULD not
     * use this provider.
     *
     * Mainly used for checking that a Provider is applicable for a given userid.
     *
     * @abstract
     * @return bool
     */
    public function validatePreconditions()
    {
        return $this->_provider->validatePreconditions();
    }

    public function removePreconditionByClassName($className)
    {
        return $this->_provider->removePreconditionByClassName($className);
    }

    /**
     * @param EngineBlock_Group_Provider_Filter_Interface $filter
     * @return EngineBlock_Group_Provider_Interface
     */
    public function addGroupFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        $this->_clearCache();

        $this->_provider->addGroupFilter($filter);
        return $this;
    }

    public function getGroupFilters()
    {
        return $this->_provider->getGroupFilters();
    }

    public function addMemberFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        $this->_clearCache();

        $this->_provider->addMemberFilter($filter);
        return $this;
    }

    public function getMemberFilters()
    {
        return $this->_provider->getMemberFilters();
    }

    public function getGroupStem()
    {
        return $this->_provider->getGroupStem();
    }

    public function setGroupStem($stemIdentifier)
    {
        $this->_clearCache();

        $this->_provider->setGroupStem($stemIdentifier);
        return $this;
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @return array A list of groups
     */
    public function getGroups($serviceProviderGroupAcl)
    {
        if (!empty($this->_groupCache)) {
            return $this->_groupCache;
        }

        $this->_groupCache = $this->_provider->getGroups($serviceProviderGroupAcl);
        return $this->_groupCache;
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @param $stem The name of the stem where the groups belong to
     * @return array A list of groups
     */
    public function getGroupsByStem($stem, $serviceProviderGroupAcl)
    {
        if (!empty($this->_groupCache)) {
            return $this->_groupCache;
        }

        $this->_groupCache = $this->_provider->getGroupsByStem($stem, $serviceProviderGroupAcl);
        return $this->_groupCache;
    }

    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier, $serviceProviderGroupAcl)
    {
        if (isset($this->_memberCache[$groupIdentifier]) && !empty($this->_memberCache[$groupIdentifier])) {
            return $this->_memberCache[$groupIdentifier];
        }
        $this->_memberCache[$groupIdentifier] = $this->_provider->getMembers($groupIdentifier, $serviceProviderGroupAcl);
        return $this->_memberCache[$groupIdentifier];
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        if (isset($this->_isMemberCache[$groupIdentifier])) {
            return $this->_isMemberCache[$groupIdentifier];
        }

        $this->_isMemberCache[$groupIdentifier] = $this->_provider->isMember($groupIdentifier);
        return $this->_isMemberCache[$groupIdentifier];
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMemberInStem()
    {
        if (isset($this->_isMemberInStemCache) && $this->_isMemberInStemCache !== NULL) {
            return $this->_isMemberInStemCache;
        }

        $this->_isMemberInStemCache = $this->_provider->isMemberInStem();
        return $this->_isMemberInStemCache;
    }

    protected function _clearCache()
    {
        $this->_groupCache = array();
        $this->_memberCache = array();
        $this->_isMemberCache = array();
        unset($this->_isMemberInStemCache);
        return $this;
    }


    /**
     * Get the details of a groupMember
     * @return the Person
     */
    public function getGroupMemberDetails($subjectId = null)
    {
        return $this->_provider->getGroupMemberDetails($subjectId);
    }

    /**
     * Is this GroupProvider able to return details for the given userId based on the configured memberFilter
     * @abstract
     * @return boolean true is the userId is a partial matched with this GroupProviders urn
     */
    public function isGroupProviderForUser()
    {
        return $this->_provider->isGroupProviderForUser();
    }
}
