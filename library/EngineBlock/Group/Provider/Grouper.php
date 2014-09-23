<?php

class EngineBlock_Group_Provider_Grouper extends EngineBlock_Group_Provider_Abstract
{
    protected $_name;

    /**
     * @var Grouper_Client_Rest
     */
    protected $_grouperClient;

    /**
     * Factory method to create and configure a group provider from it's given configuration
     *
     * @static
     * @abstract
     * @param Zend_Config $config
     * @param string $userId
     * @return EngineBlock_Group_Provider_Grouper
     */
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $grouperClient = Grouper_Client_Rest::createFromConfig($config);
        $provider = new self($config->id, $config->name, $grouperClient);

        $provider->setUserId($userId);

        $provider->configurePreconditions($config);
        $provider->configureGroupFilters($config);
        $provider->configureGroupMemberFilters($config);
        $decoratedProvider = $provider->configureDecoratorChain($config);

        return $decoratedProvider;
    }

    public function __construct($id, $name, Grouper_Client_Interface $grouperClient)
    {
        $this->_id   = $id;
        $this->_name = $name;
        $this->_grouperClient = $grouperClient;
    }

    public function setUserId($userId)
    {
        parent::setUserId($userId);

        $this->_grouperClient->setSubjectId($userId);
        return $this;
    }

    public function getGroups($serviceProviderGroupAcls)
    {
        try {
            $grouperGroups = $this->_grouperClient->getGroupsWithPrivileges();
        }
        catch (Grouper_Client_Exception_SubjectNotFound $e) {
            // Ignore Subject not found exceptions
            EngineBlock_ApplicationSingleton::getLog()->warn("User '{$this->_userId}' does not exist in Grouper Group Provider '{$this->_id}'");
            return array();
        }

        $groups = array();
        foreach ($grouperGroups as $group) {
            $groups[] = $this->_mapGrouperGroupToEngineBlockGroup($group);
        }
        return $groups;
    }

    public function getGroupsByStem($stem, $serviceProviderGroupAcls)
    {
        try {
            $grouperGroups = $this->_grouperClient->getGroupsWithPrivileges($stem);
        }
        catch (Grouper_Client_Exception_SubjectNotFound $e) {
            // Ignore Subject not found exceptions
            EngineBlock_ApplicationSingleton::getLog()->warn("User '{$this->_userId}' does not exist in Grouper Group Provider '{$this->_id}'");
            return array();
        }

        $groups = array();
        foreach ($grouperGroups as $group) {
            $groups[] = $this->_mapGrouperGroupToEngineBlockGroup($group);
        }
        return $groups;
    }

    public function getMembers($groupIdentifier, $serviceProviderGroupAcls)
    {
        $members = array();
        $stemmedGroupIdentifier = $this->_getStemmedGroupId($groupIdentifier);
        $subjects = $this->_grouperClient->getMembersWithPrivileges($stemmedGroupIdentifier);
        foreach ($subjects as $subject) {
                $members[] = $this->_mapGrouperSubjectToEngineBlockGroupMember($subject, $stemmedGroupIdentifier);
        }
        return $members;
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        return $this->_grouperClient->hasMember($this->_getStemmedGroupId($groupIdentifier));
    }

    public function isMemberInStem()
    {
        $groups = $this->_grouperClient->getGroups($this->_stem);
        return !empty($groups);
    }

    protected function _mapGrouperGroupToEngineBlockGroup(Grouper_Model_Group $grouperGroup)
    {
        $engineBlockGroup = new EngineBlock_Group_Model_Group();
        $engineBlockGroup->id           = $grouperGroup->name;
        $engineBlockGroup->title        = $grouperGroup->displayExtension;
        $engineBlockGroup->description  = $grouperGroup->description;
        $engineBlockGroup->forUserId    = $this->_userId;
        $engineBlockGroup->userRole     = $this->_determineVootMembershipRoleByPrivileges($grouperGroup->privileges);

        foreach ($this->_groupFilters as $groupFilter) {
            $engineBlockGroup = $groupFilter->filter($engineBlockGroup);
        }

        return $engineBlockGroup;
    }

    protected function _mapGrouperSubjectToEngineBlockGroupMember(Grouper_Model_Subject $grouperSubject, $groupId)
    {
        $engineBlockMember = new EngineBlock_Group_Model_GroupMember();
        $engineBlockMember->id          = $grouperSubject->id;
        $engineBlockMember->displayName = $grouperSubject->name;
        $engineBlockMember->forGroup    = $groupId;
        $engineBlockMember->forUser     = $this->_userId;
        $engineBlockMember->userRole    = $this->_determineVootMembershipRoleByPrivileges($grouperSubject->privileges);

        foreach ($this->_memberFilters as $memberFilter) {
            $engineBlockMember = $memberFilter->filter($engineBlockMember);
        }

        return $engineBlockMember;
    }

    protected function _determineVootMembershipRoleByPrivileges(array $privileges)
    {
        if (in_array('admin', $privileges)) {
            return 'admin';
        }
        if (in_array('update', $privileges)) {
            return 'manager';
        }
        if (in_array('read', $privileges)) {
            return 'member';
        }

        throw new EngineBlock_Group_Provider_Exception("Unable to determine member role in group by looking at the privileges");
    }

    /**
     * Get the details of a groupMember
     * @param $userId the unique groupMember identifier
     * @return the Person
     */
    public function getGroupMemberDetails($subjectId = null)
    {
        //this can never happen as we retrieve groups from Grouper and therefore MemberDetails are
        //provided by the userDirectory (e.g. we always return false from isGroupProviderFor)
        throw new EngineBlock_Exception("Grouper does not provide GroupMemberDetails");
    }

    /**
     * Is this GroupProvider able to return details for the given userId based on the configured memberFilter
     * @param $userId the unique Person identifier
     * @return boolean true is the userId is a partial matched with this GroupProviders urn
     */
    public function isGroupProviderForUser()
    {
        return false;
    }
}
