<?php

define('ENGINEBLOCK_SERVICEREGISTRY_GADGETBASEURL_FIELD', 'coin:gadgetbaseurl');

class EngineBlock_SocialData
{
    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory = NULL;

    /**
     * @var EngineBlock_Groups_Grouper
     */
    protected $_grouperClient = null;

    /**
     * @var EngineBlock_ServiceRegistry_Client
     */
    protected $_serviceRegistry = NULL;

    /**
     * @var EngineBlock_SocialData_FieldMapper
     */
    protected $_fieldMapper = NULL;

    /**
     * @var String
     */
    protected $_appId = NULL;
    
    /**
     * Construct an EngineBlock_SocialData instance for retrieving social data within
     * Engineblock
     * @param String $appId The id of the app on behalf of which we are retrieving data
     */
    public function __construct($appId)
    {
        $this->_appId = $appId;
    }
    
    public function getPerson($identifier, $socialAttributes = array())
    {
        $result = array();
                
        $ldapAttributes = $this->_getFieldMapper()->socialToLdapAttributes($socialAttributes);
        
        $persons = $this->_getUserDirectory()->findUsersByIdentifier($identifier, $ldapAttributes);
        if (count($persons)) {
            // ignore the hypothetical possibility that we get multiple results for now.
            $result = $this->_getFieldMapper()->ldapToSocialData($persons[0], $socialAttributes);
            
            // Make sure we only include attributes that we are allowed to share
            $result = $this->_enforceArp($result);
        }
          
        return $result;
    }

    public function getGroupsForPerson($identifier)
    {
        $grouperGroups = $this->_getGrouperClient()->getGroups($identifier);

        $openSocialGroups = array();
        foreach ($grouperGroups as $group) {
            $openSocialGroups[] = $this->_getFieldMapper()->grouperToSocialData($group);
        }
        return $openSocialGroups;
    }

    public function getGroupMembers($groupMemberUid, $groupId, $socialAttributes = array())
    {
        $groupMembers = $this->_getGrouperClient()->getMembers($groupMemberUid, $groupId);

        $people = array();
        foreach ($groupMembers as $groupMember) {
            $people[] = $this->getPerson($groupMember['id'], $socialAttributes);
        }
        return $people;
    }

    protected function _enforceArp($record)
    {
        // @todo not implemented
        return $record;

        // @todo: the below is a pseudocode implementation of an arp enforcer. Not tested.

        // Find out the SP Identifier based on the app id.
        // E.g. if the appid = 'weather.gadget.google.com' and in Janus there is a field
        // called coin:gadgetbaseurl which is set to .*\.gadget\.google\.com, then
        // the SP identifier of that entry will be returned by findIdentifiersBymetadata.
        $result = $this->_getServiceRegistry()->
                         findIdentifiersByMetadata(ENGINEBLOCK_SERVICEREGISTRY_GADGETBASEURL_FIELD,
                                                   $this->_appId);

        if (count($result)) {
            $spIdentifier = $result[0];
            $arp = $this->_getServiceRegistry()->getArp($spIdentifier);

            // @todo filter attributes based on arp.

            return $record;

        }

        return array(); // something went wrong, as a precaution we don't give back any data
    }

    /**
     * @return EngineBlock_UserDirectory
     */
    protected function _getUserDirectory()
    {
        if ($this->_userDirectory == NULL) {
            $this->_userDirectory = new EngineBlock_UserDirectory();
        }
        return $this->_userDirectory;
    }

    public function setUserDirectory($userDirectory)
    {
        $this->_userDirectory = $userDirectory;
    }

    /**
     * @return EngineBlock_Groups_Grouper Grouper REST client
     */
    protected function _getGrouperClient()
    {
        if (!isset($this->_grouperClient)) {
            $this->_grouperClient = new EngineBlock_Groups_Grouper();
        }
        return $this->_grouperClient;
    }
    
    /**
     * @return EngineBlock_ServiceRegistry_Client
     */
    protected function _getServiceRegistry()
    {
        if ($this->_serviceRegistry == NULL) {
            $this->_serviceRegistry = new EngineBlock_ServiceRegistry_Cached();
        }
        return $this->_serviceRegistry;
    }

    public function setServiceRegistry($serviceRegistry)
    {
        $this->_serviceRegistry = $serviceRegistry;
    }
    
    /**
     * @return EngineBlock_SocialData_FieldMapper mapper
     */
    protected function _getFieldMapper()
    {
        if ($this->_fieldMapper == NULL) {
            $this->_fieldMapper = new EngineBlock_SocialData_FieldMapper();
        }
        return $this->_fieldMapper;
    }

    public function setFieldMapper($mapper)
    {
        $this->_fieldMapper = $mapper;
    }
}
