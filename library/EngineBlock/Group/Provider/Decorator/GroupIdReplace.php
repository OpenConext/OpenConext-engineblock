<?php

class EngineBlock_Group_Provider_Decorator_GroupIdReplace
    extends EngineBlock_Group_Provider_Decorator_Abstract
{
    /**
     * @var string
     */
    protected $_search;

    /**
     * @var string
     */
    protected $_replace;

    public static function createFromConfigsWithProvider(EngineBlock_Group_Provider_Interface $provider, Zend_Config $config)
    {
        if (!isset($config->search) || !isset($config->replace)) {
            throw new EngineBlock_Group_Provider_Exception(
                "Missing configuration for groupIdReplace decorator, please make sure .search and .replace are set"
            );
        }
        return new self($provider, $config->search, $config->replace);
    }

    public function __construct($provider, $search, $replace)
    {
        $this->_provider = $provider;
        $this->_search   = $search;
        $this->_replace  = $replace;
    }

    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier,$serviceProviderGroupAcls )
    {
        // If the group is not a decorated group, don't even bother looking up the members, can't be ours
        if ($this->_search && !preg_match($this->_search, $groupIdentifier)) {
            return array();
        }

        $groupIdentifier = preg_replace($this->_search, $this->_replace, $groupIdentifier);

        return parent::getMembers($groupIdentifier, $serviceProviderGroupAcls);
    }

    /**
     * Check whether the given group is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        // If the group is not a decorated group, don't even bother looking up the members, can't be ours
        // except the members group for stem VOs
        if ($groupIdentifier !== 'members' && $this->_search && !preg_match($this->_search, $groupIdentifier)) {
            return false;
        }

        $groupIdentifier = preg_replace($this->_search, $this->_replace, $groupIdentifier);

        return parent::isMember($groupIdentifier);
    }
}
