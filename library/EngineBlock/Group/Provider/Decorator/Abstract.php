<?php

abstract class EngineBlock_Group_Provider_Decorator_Abstract
    implements EngineBlock_Group_Provider_Decorator_Interface, EngineBlock_Group_Provider_Interface
{
    /**
     * @var EngineBlock_Group_Provider_Interface
     */
    protected $_provider;

    /**
     * {@inheritdoc}
     */
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        throw new EngineBlock_Exception("createFromConfigs not possible for decorator, need access to provider");
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
        return $this->_provider->setUserId($userId);
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @return array A list of groups
     */
    public function getGroups($serviceProviderGroupAcls)
    {
        return $this->_provider->getGroups($serviceProviderGroupAcls);
    }

    /**
     * Get the details of a groupMember
     * @abstract
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

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @return array A list of groups
     */
    public function getGroupsByStem($stem, $serviceProviderGroupAcls)
    {
        return $this->_provider->getGroupsByStem($stem, $serviceProviderGroupAcls);
    }
    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier, $serviceProviderGroupAcls)
    {
        return $this->_provider->getMembers($groupIdentifier, $serviceProviderGroupAcls);
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        return $this->_provider->isMember($groupIdentifier);
    }

    public function isMemberInStem()
    {
        return $this->_provider->isMemberInStem();
    }

    /**
     * Get the ID of this provider (plain alphanumeric string for use in configs, URLs and such)
     *
     * @abstract
     * @return string
     */
    public function getId()
    {
        return $this->_provider->getId();
    }

    /**
     * Get the display name for this group provider
     *
     * @abstract
     * @return string
     */
    public function getDisplayName()
    {
        return $this->_provider->getDisplayName();
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
     * Add a precondition for use of this provider.
     *
     * @abstract
     * @param string $className
     * @param array $options
     * @return EngineBlock_Group_Provider_Interface
     */
    public function addPrecondition($className, $options = null)
    {
        return $this->_provider->addPrecondition($className, $options);
    }

    /**
     * Get the preconditions for use of this provider.
     *
     * @abstract
     * @return array
     */
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
        $this->_provider->removePreconditionByClassName($className);
    }

    public function addGroupFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        return $this->_provider->addGroupFilter($filter);
    }

    public function getGroupFilters()
    {
        return $this->_provider->getGroupFilters();
    }

    public function addMemberFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        return $this->_provider->addMemberFilter($filter);
    }

    public function getMemberFilters()
    {
        return $this->_provider->getMemberFilters();
    }

    /**
     * @abstract
     * @return string
     */
    public function getGroupStem()
    {
        return $this->_provider->getGroupStem();
    }

    /**
     * Some group provider implementations are able to host more than one set
     * of groups. In many implementations this is called a 'stem', and by
     * setting the stem we can choose which set of groups to use. While we use
     * the term 'stem' here, other implementations are free to implement the
     * filtering as they see fit. Implementations that don't support multiple
     * sets of groups, they can simply ignore this call
     * @param String $stemIdentifier
     * @return EngineBlock_Group_Provider_Interface
     */
    public function setGroupStem($stemIdentifier)
    {
        return $this->_provider->setGroupStem($stemIdentifier);
    }

    /**
     * Proxy calls that are not in the interface (like the OAuth / OpenSocial specific stuff)
     * to the provider;
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_provider, $name), $arguments);
    }
}
