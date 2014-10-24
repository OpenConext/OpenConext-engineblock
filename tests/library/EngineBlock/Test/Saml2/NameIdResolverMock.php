<?php

class EngineBlock_Test_Saml2_NameIdResolverMock extends EngineBlock_Saml2_NameIdResolver
{
    private $_serviceProviderUuids = array();
    private $_persistentIds = array();

    protected function _getUserDirectory()
    {
        $mock = new EngineBlock_Test_UserDirectoryMock();
        $mock->setUser(
            'urn:collab:person:example.edu:mock1',
            array(
                'collabpersonid' => 'urn:collab:person:example.edu:mock1',
                'collabpersonuuid' => '',
            )
        );
        return $mock;
    }

    protected function _fetchPersistentId($serviceProviderUuid, $userUuid)
    {
        return empty($this->_persistentIds[$serviceProviderUuid][$userUuid]) ?
            false :
            $this->_persistentIds[$serviceProviderUuid][$userUuid];
    }

    protected function _storePersistentId($persistentId, $serviceProviderUuid, $userUuid)
    {
        if (!isset($this->_persistentIds[$serviceProviderUuid])) {
            $this->_persistentIds[$serviceProviderUuid] = array();
        }
        $this->_persistentIds[$serviceProviderUuid][$userUuid] = $persistentId;
    }

    protected function _fetchServiceProviderUuid($spEntityId)
    {
        return empty($this->_serviceProviderUuids[$spEntityId]) ?
            false:
            $this->_serviceProviderUuids[$spEntityId];
    }

    protected function _storeServiceProviderUuid($spEntityId, $uuid)
    {
        $this->_serviceProviderUuids[$spEntityId] = $uuid;
    }
}
